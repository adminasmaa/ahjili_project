<?php

namespace App\Http\Controllers\API;

use App\Enums\AccountType;
use App\Enums\PostType;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserPrivateResource;
use App\Http\Resources\UserResource;
use App\Models\Ban;
use App\Models\ReportMessage;
use App\Models\ReportAbusePost;
use App\Notifications\PostLiked;
use App\Notifications\PostTagNotification;
use Illuminate\Support\Facades\Auth;
use Notification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class PostController extends BaseController
{
    /**
     * __construct
     *
     * @param  mixed $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        // make a validation:
        $validator = Validator::make(
            $request->all(),
            [
            'type' => 'required|in:image,video,audio,text,gif',
            'title' => 'required_if:type,==,text',
            'images' => 'required_if:type,==,image|array|max:5',
            'images.*' => 'mimes:jpeg,jpg,png,gif',
            'video' => 'required_if:type,==,video|mimes:mp4,ogx,oga,ogv,ogg,webm',
            'video_thumbnail' => 'required_if:type,==,video|mimes:jpeg,jpg,png,gif',
            'audio' => 'required_if:type,==,audio|mimes:audio/mpeg,mpga,mp3,wav,aac',
            'gif' => 'required_if:type,==,gif',
            "mention_users"  => "sometimes|array",
            "mention_users.*"  => "sometimes|distinct|exists:users,id",
        ],
            [
                'type.required' => 'The :attribute field is required.',
                'type.in' => 'The :attribute image,video,audio,text,gif.',
                'title.required_if' => 'The :attribute field is required when type is text.',
                'images.required_if' => 'The :attribute field is required when type is image.',
                'images.*.mimes' => 'The :attribute must be a file of type: :values.',
                'images.max' => 'The :attribute must not have more than :max items.',
                'video.required_if' => 'The :attribute field is required when type is vedio.',
                'video.mimes' => 'The :attribute must be a file of type: :values.',
                'video_thumbnail.required_if' => 'The :attribute field is required when type is vedio.',
                'video_thumbnail.mimes' => 'The :attribute must be a file of type: :values.',
                'audio.required_if' => 'The :attribute field is required when type is audio.',
                'audio.mimes' => 'The :attribute must be a file of type: :values.',
                'gif.required_if' => 'The :attribute field is required when type is gif.',
                'gif.mimes' => 'The :attribute must be a file of type: :values.',
                'mention_users.array' => 'The :attribute must be a array values.',
                'mention_users.*.distinct' =>  'The :attribute must be a unique values.',
                'mention_users.*.exists' => 'The :attribute is not exists.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        ini_set('memory_limit', '44M');
        // proccessed case of type post
        switch($request->type) {
            case Post::IMAGE:
                $imageResponse =  postS3Images($request);
                break;
            case Post::VIDEO:
                $imageResponse =  postS3Video($request);
                break;
            case Post::AUDIO:
                $imageResponse =  postS3Audio($request);
                break;
            case Post::GIF:
                $imageResponse = ['path'=>array($request->gif),'thumb_path'=> array()];
                break;
            default:
                $imageResponse = ['path'=>array(),'thumb_path'=> array()];
                break;
        }

        // create a new post
        $post=new Post();
        $post->user_id =$this->user->id;
        $post->title =$request->title;
        $post->post_type =$request->type ?? Post::TEXT;
        $post->latitude =$request->latitude;
        $post->longitude =$request->longitude;
        $post->save();

        // check if imageResponse not a vide
        if (count($imageResponse['path'])>0) {
            //for saving post content
            foreach ($imageResponse['path'] as $key => $singlepath) {
                if (count($imageResponse['thumb_path'])>0) {
                    $path_thumbnail=$imageResponse['thumb_path'][$key];
                } else {
                    $path_thumbnail = null;
                }
                $postimage=new PostImage();
                $postimage->post_id=$post->id;
                $postimage->path=$singlepath;
                $postimage->path_thumbnail=$path_thumbnail ?? null;
                $postimage->save();
            }
            //end
        }

        //adding multiple tags
        if ($request->tags!='') {
            $tags=$request->tags;
            $post->attachTags($tags);
        }
        // put mentions:
        $mention_users=$request->mention_users;
        //adding mention users
        if ($mention_users!='') {
            if (array_sum($mention_users)>0) {
                foreach ($mention_users as $key => $userid) {
                    $receiver=User::find($userid);
                    $this->sendPostTagNotification($receiver, $post->id);
                }
                $post->update(['mention_users' => implode(',', $mention_users)]);
            }
        }
        //end

        return $this->sendResponse([], 'Post created successfully.');
    }


    /**
     * update
     *
     * @param  mixed $request
     * @return void
     */
    public function update(Request $request)
    {
        // make a validation
        $validator = Validator::make(
            $request->all(),
            [
            'id' => 'required|exists:posts,id',
            'type' => 'required|in:image,video,audio,text,gif',
            'title' => 'required_if:type,==,text',
            "mention_users"  => "sometimes|array",
            "mention_users.*"  => "sometimes|distinct|exists:users,id",
        ],
            [
                'id.required' => 'The :attribute field is required.',
                'id.exists' => 'The :attribute is not exists.',
                'type.required' => 'The :attribute field is required.',
                'type.in' => 'The :attribute image,video,audio,text,gif.',
                'title.required_if' => 'The :attribute field is required when type is text.',
                'mention_users.array' => 'The :attribute must be a array values.',
                'mention_users.*.distinct' =>  'The :attribute must be a unique values.',
                'mention_users.*.exists' => 'The :attribute is not exists.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // update post
        $id=$request->id;
        $post=Post::find($id);
        $post->title =$request->title;
        $post->latitude =$request->latitude;
        $post->longitude =$request->longitude;
        $post->save();

        //adding multiple tags
        if ($request->tags!='') {
            $tags=$request->tags;
            $post->syncTags($tags);
        }

        $mention_users=$request->mention_users;
        //adding mention users
        if ($mention_users!='') {
            if (array_sum($mention_users)>0) {
                foreach ($mention_users as $key => $userid) {
                    $receiver=User::find($userid);
                    // send notification
                    $this->sendPostTagNotification($receiver, $post->id);
                }
                // update mention users
                $post->update(['mention_users' => implode(',', $mention_users)]);
            }
        }
        //end

        // return response
        return $this->sendResponse([], 'Post updated successfully.');
    }

    /**
     * anonymousStore
     *
     * @param  mixed $request
     * @return void
     */
    public function anonymousStore(Request $request)
    {
        // make a validation
        $validator = Validator::make(
            $request->all(),
            [
            'title' => 'required'
        ],
            [
                'title.required' => 'The :attribute field is required.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // create a post anonymous
        $user_id=$this->user->id;
        $title=$request->title;
        $post =  Post::create([
                'user_id' => $user_id,
                'title'    => $title,
                'post_type'     => 'text',
                'anonymous_status' => true,
            ]);
        //adding multiple tags
        if ($request->tags!='') {
            $tags=$request->tags;
            $post->attachTags($tags);
        }
        // retuen response
        return $this->sendResponse([], 'Anonymous created successfully.');
    }

    /**
     * delete
     *
     * @param  mixed $request
     * @return void
     */
    public function delete(Request $request)
    {
        // make a validation
        $validator = Validator::make(
            $request->all(),
            [
            'id' => 'required|exists:posts,id',
        ],
            [
                'id.required' => 'The :attribute field is required.',
                'id.exists' => 'The :attribute is not exists.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // delete a post
        $id=$request->id;
        $post = Post::findorFail($id);
        $postmedia=$post->getPostImages;

        ini_set('memory_limit', '44M');

        if (count($postmedia)>0) {
            foreach ($postmedia as $single) {
                $opath= explode('https://ahjili.s3.eu-central-1.amazonaws.com/', $single->path);
                $thumb_path=explode('https://ahjili.s3.eu-central-1.amazonaws.com/', $single->path_thumbnail);
                $path= $opath[1]?? "";
                $thumb_path= $thumb_path[1]?? "";
                if ($path) {
                    if (Storage::disk('s3')->exists($path)) {
                        Storage::disk('s3')->delete($path);
                    }
                }
                if ($thumb_path) {
                    if (Storage::disk('s3')->exists($thumb_path)) {
                        Storage::disk('s3')->delete($thumb_path);
                    }
                }
                $single->delete();
            }
        }
        $post->delete();

        // return response
        return $this->sendResponse([], 'Post deleted successfully.');
    }

    public function getUserPosts(Request $request)
    {
        // make a validation
        $validator = Validator::make(
            $request->all(),
            [
            'id' => 'required|exists:users,id',
        ],
            [
                'id.required' => 'The :attribute field is required.',
                'id.exists' => 'The :attribute is not exists.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }


        $id=$request->id;
        $user=User::find($id);

        // check founded user
        if (!$user) {
            return $this->sendError('Not found this user.');
        }

        // get user banned if founded
        $user_banned = Ban::query()
                    ->where('user_id', $id)
                    ->Where('ban_user_id', $this->user->id)
                    ->first();
        // checked
        if ($user_banned) {
            // send response for banned user
            if (Gate::allows('check-user-banned', $user_banned)) {
                return $this->sendError('unauthoized cause banned .');
            }
        }

        $images_gifs=allUserPosts($id, [Post::IMAGE,Post::GIF]);
        $vedio=allUserPosts($id, [Post::VIDEO]);
        $text=allUserPosts($id, [Post::TEXT]);
        $audio=allUserPosts($id, [Post::AUDIO]);

        PostResource::collection($images_gifs);
        PostResource::collection($vedio);
        PostResource::collection($text);
        PostResource::collection($audio);
        $success['user'] = $user->account_type == User::PUBLIC ? new UserResource($user) : new UserPrivateResource($user);
        $success['posts'] = $images_gifs;
        $success['vedio'] = $vedio;
        $success['text'] =  $text;
        $success['audio'] = $audio;
        return $this->sendResponse($success, 'User all posts retrieved successfully.');
    }

    public function likePost(Request $request)
    {
        $id = $this->user->id;
        $user = User::find($id);
        $post = Post::find($request->id);
        $owner_post = User::find($post->user_id);
        if ($user->hasLiked($post)) {
            $user->toggleLike($post);
            // return response
            return $this->sendResponse([], 'Post unlike successfully.');
        } else {
            $user->toggleLike($post);
            //send notification
            $owner_post->notify(new PostLiked($owner_post, $user, $post));
            // return response
            return $this->sendResponse([], 'Post Like successfully.');
        }
    }

    /**
     * getAllUsersPost
     *
     * @return void
     */
    public function getAllUsersPost()
    {
        // get users following:
        $users_followings = $this->user->followings()->with('followable')->pluck('followable_id')->push($this->user->id);
        // get report posts
        $posts_repost = ReportAbusePost::query()->pluck('post_id');
        // get posts for users following
        $posts = Post::query()
                        ->active()
                        ->disactiveanonymous()
                        ->whereNotIn('id', $posts_repost)
                        ->with(['getPostImages','comments','likers'])
                        ->whereHas('user', function ($q) use ($users_followings) {
                            $q->whereIn('id', $users_followings);
                        })
                        ->orderBy('created_at', 'DESC')
                        ->paginate(Post::PAGINATE);

        PostResource::collection($posts);
        $success=$posts;
        return $this->sendResponse($success, 'All posts retrieved successfully.');
    }

    public function getAllUsersPostVedios()
    {
        // get users blocked:
        $users_blocked = Ban::query()->where('user_id', $this->user->id)->pluck('ban_user_id');
        // get report posts
        $posts_repost = ReportAbusePost::query()->pluck('post_id');
        // get posts
        $posts = Post::query()
                            ->active()
                            ->disactiveanonymous()
                            ->where('post_type', Post::VIDEO)
                            ->whereNotIn('id', $posts_repost)
                            ->with(['getPostImages','comments','likes'])
                            ->whereHas('user', function ($q) use ($users_blocked) {
                                $q->whereNotIn('id', $users_blocked)
                                  ->where('account_type', User::PUBLIC);
                            })
                            ->withCount('likes')
                            ->orderBy('likes_count', 'DESC')
                            ->paginate(15);
        // make a resources
        PostResource::collection($posts);
        $success=$posts;
        // return responses
        return $this->sendResponse($success, 'All Vedio posts retrieved successfully.');
    }

    public function getAllUsersPostImages()
    {
        // get users blocked:
        $users_blocked = Ban::query()->where('user_id', $this->user->id)->pluck('ban_user_id');
        // get report posts
        $posts_repost = ReportAbusePost::query()->pluck('post_id');

        $posts = Post::query()
                        ->active()
                        ->disactiveanonymous()
                        ->whereIn('post_type', [Post::IMAGE,Post::GIF])
                        ->whereNotIn('id', $posts_repost)
                        ->with(['getPostImages','comments','likes'])
                        ->whereHas('user', function ($q) use ($users_blocked) {
                            $q->whereNotIn('id', $users_blocked)
                              ->where('account_type', User::PUBLIC);
                        })
                        ->withCount('likes')
                        ->orderBy('likes_count', 'DESC')
                        ->paginate(15);
        PostResource::collection($posts);
        $success=$posts;
        return $this->sendResponse($success, 'All Images & Gifs posts retrieved successfully.');
    }


    /**
     * getAllUsersAnonymousPost
     *
     * @return void
     */
    public function getAllUsersAnonymousPost()
    {
        // get users blocked:
        $users_blocked = Ban::query()->where('user_id', $this->user->id)->pluck('ban_user_id');
        // get report posts
        $posts_repost = ReportAbusePost::query()->pluck('post_id');
        // get posts anonymous
        $posts = Post::query()
                        ->active()
                        ->activeanonymous()
                        ->whereNotIn('id', $posts_repost)
                        ->with(['getPostImages','comments','likers'])
                        ->whereHas('user', function ($q) use ($users_blocked) {
                            $q->whereNotIn('id', $users_blocked);
                        })
                        ->orderBy('created_at', 'DESC')
                        ->paginate(10);
        PostResource::collection($posts);
        $success=$posts;
        return $this->sendResponse($success, 'All anonymous posts retrieved successfully.');
    }

    public function reportPostList()
    {
        $reportposts =ReportAbusePost::query()->where('user_id', $this->user->id)->get();
        return $this->sendResponse($reportposts, 'All Saved Posts List retrieved successfully');
    }

    public function reportMessageList()
    {
        $reportmessages =ReportMessage::get();
        return $this->sendResponse($reportmessages, 'All Saved Messages List retrieved successfully');
    }

    public function postAbuseReport(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
            'post_id' => 'required|exists:posts,id',
            'reason' => 'required|string',
        ],
            [
                'post_id.required' => 'The :attribute field is required.',
                'post_id.exists' => 'The :attribute is not exists.',
                'reason.required' => 'The :attribute field is required.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input=$request->all();
        $id = auth()->id();
        $post_id=$request->post_id;
        $input['user_id']=$id;
        $row=ReportAbusePost::create($input);

        return $this->sendResponse([], 'Post abuse report successfully.');
    }

    public function sendPostTagNotification($receiver, $post_id)
    {
        $message='mentioned you in a post';
        $details =[
            'post_id'=> $post_id,
            'post_by'=> Auth::User()->username,
            'post_by_id'=> Auth::User()->id,
            'message'=> $message,
        ];

        Notification::send($receiver, new PostTagNotification($details, $receiver->fcmtoken));
        return 1;
    }

    //above code tested

    public function oldstores(Request $request)
    {
        if ($request['type'] == 'image') {
            $validator = Validator::make($request->all(), [
                'images' => 'required|array|max:5',
                'images.*' => 'mimes:jpeg,jpg,png,gif',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->getMessages());
            }
        } elseif ($request['type'] == 'video') {
            $validator = Validator::make($request->all(), [
                'video' => 'required|file',
                'video.*' => 'mimetypes:video/mp4',
                'video_thumbnail' => 'required|mimes:jpeg,jpg,png,gif',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->getMessages());
            }
        } elseif ($request['type'] == 'audio') {
            $validator = Validator::make($request->all(), [
                'audio' => 'required|file',
                'audio.*' => 'mimes:audio/mpeg,mpga,mp3,wav',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->getMessages());
            }
        } elseif ($request['type'] == 'text') {
            $validator = Validator::make($request->all(), [
                'body' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->getMessages());
            }
        } elseif ($request['type'] == 'gif') {
            $validator = Validator::make($request->all(), [
                'gif' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->getMessages());
            }
        }

        ini_set('memory_limit', '44M');

        if ($request['type'] == 'image') {
            $imageResponse =  postS3Images($request);
        } elseif ($request['type'] == 'video') {
            $imageResponse =  postS3Video($request);
        } elseif ($request['type'] == 'audio') {
            $imageResponse =  postS3Audio($request);
        } else {
            $imageResponse['file_uploaded'] = false;
        }
        $mention_users=$request->mention_users;
        if ($imageResponse['file_uploaded'] == true) {
            $tags = tagsFromString($request['body']);
            $postTags = $tags;
            $post = Post::create([
                'user_id' => auth()->id(),
                'post_image_id' => $imageResponse['image_id'],
                'body'    => $request['body'],
                'type'     => $request['type'],
                'post_tags'     =>  serialize($postTags),
                'status'  => $request['status']
            ]);
            //adding multiple tags
            if ($request->tags!='') {
                $tags=$request->tags;
                $post->attachTags($tags);
            }
            //adding mention users
            if ($mention_users!='') {
                if (array_sum($mention_users)>0) {
                    foreach ($mention_users as $key => $userid) {
                        $receiver=User::find($userid);
                        $this->sendPostTagNotification($receiver, $post->id);
                    }
                    $post->update(['mention_users' => implode(',', $mention_users)]);
                }
            }
            //end
            return $this->sendResponse('success', ['message'=>['Post created successfully']]);
        } elseif ($request['type'] == 'text') {
            $tags = tagsFromString($request['body']);
            //dd($tags);
            $postTags = $tags;
            $post =  Post::create([
                    'user_id' => auth()->id(),
                    'post_image_id' => '',
                    'body'    => $request['body'],
                    'type'     => $request['type'],
                    'post_tags'     =>  serialize($postTags),
                    'status'  => $request['status']
                ]);
            //adding multiple tags
            if ($request->tags!='') {
                $tags=$request->tags;
                $post->attachTags($tags);
            }
            //adding mention users
            if ($mention_users!='') {
                if (array_sum($mention_users)>0) {
                    foreach ($mention_users as $key => $userid) {
                        $receiver=User::find($userid);
                        $this->sendPostTagNotification($receiver, $post->id);
                    }
                    $post->update(['mention_users' => implode(',', $mention_users)]);
                }
            }
            //end
            return $this->sendResponse('success', ['message'=>['Post created successfully']]);
        } elseif ($request['type'] == 'gif') {
            $tags = tagsFromString($request['body']);
            $postTags = $tags;
            $post = Post::create([
                'user_id' => auth()->id(),
                'post_image_id' => $request['gif'],
                'body'    => $request['body'],
                'type'     => $request['type'],
                'post_tags'     =>  serialize($postTags),
                'status'  => $request['status']
            ]);
            //adding multiple tags
            if ($request->tags!='') {
                $tags=$request->tags;
                $post->attachTags($tags);
            }
            //adding mention users
            if ($mention_users!='') {
                if (array_sum($mention_users)>0) {
                    foreach ($mention_users as $key => $userid) {
                        $receiver=User::find($userid);
                        $this->sendPostTagNotification($receiver, $post->id);
                    }
                    $post->update(['mention_users' => implode(',', $mention_users)]);
                }
            }
            //end
            return $this->sendResponse('success', ['message'=>['Post created successfully']]);
        } else {
            return $this->sendError('failed', ['message' => ['Something went wrong']]);
        }
    }

    public function oldstore(Request $request)
    {
        if ($request['type'] == 'image') {
            $validator = Validator::make($request->all(), [
                'images' => 'required|array|max:5',
                'images.*' => 'mimes:jpeg,jpg,png,gif',
            ]);
        } elseif ($request['type'] == 'video') {
            $validator = Validator::make($request->all(), [
                'video' => 'required|file',
                'video.*' => 'mimetypes:video/mp4',
                'video_thumbnail' => 'required|mimes:jpeg,jpg,png,gif',
            ]);
        } elseif ($request['type'] == 'audio') {
            $validator = Validator::make($request->all(), [
                'audio' => 'required|file',
                'audio.*' => 'mimes:audio/mpeg,mpga,mp3,wav',
            ]);
        } elseif ($request['type'] == 'text') {
            $validator = Validator::make($request->all(), [
                'body' => 'required'
            ]);
        } elseif ($request['type'] == 'gif') {
            $validator = Validator::make($request->all(), [
                'gif' => 'required',
            ]);
        }

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->getMessages());
        }
        if ($request['type'] == 'image') {
            $imageResponse =  postImages($request);
        } elseif ($request['type'] == 'video') {
            $imageResponse =  postVideoo($request);
        } elseif ($request['type'] == 'audio') {
            $imageResponse =  postAudio($request);
        } else {
            $imageResponse['file_uploaded'] = false;
        }
        if ($imageResponse['file_uploaded'] == true) {
            $tags = tagsFromString($request['body']);
            $postTags = $tags;
            $post = Post::create([
                'user_id' => auth()->id(),
                'post_image_id' => $imageResponse['image_id'],
                'body'    => $request['body'],
                'type'     => $request['type'],
                'post_tags'     =>  serialize($postTags),
                'status'  => $request['status']
            ]);
            $post->attachTags($tags);
            return $this->sendResponse('success', ['message'=>['Post created successfully']]);
        } elseif ($request['type'] == 'text') {
            $tags = tagsFromString($request['body']);
            //dd($tags);
            $postTags = $tags;
            $post =  Post::create([
                    'user_id' => auth()->id(),
                    'post_image_id' => '',
                    'body'    => $request['body'],
                    'type'     => $request['type'],
                    'post_tags'     =>  serialize($postTags),
                    'status'  => $request['status']
                ]);
            //adding multiple tags
            $post->attachTags($tags);
            return $this->sendResponse('success', ['message'=>['Post created successfully']]);
        } elseif ($request['type'] == 'gif') {
            $tags = tagsFromString($request['body']);
            $postTags = $tags;
            $post = Post::create([
                'user_id' => auth()->id(),
                'post_image_id' => $request['gif'],
                'body'    => $request['body'],
                'type'     => $request['type'],
                'post_tags'     =>  serialize($postTags),
                'status'  => $request['status']
            ]);
            $post->attachTags($tags);
            return $this->sendResponse('success', ['message'=>['Post created successfully']]);
        } else {
            return $this->sendError('failed', ['message' => ['Something went wrong']]);
        }
    }

    public function taggedPost($tag)
    {
        $posts = getTaggedPost($tag);
        return $this->sendUserAllPosts($posts, 'Users all posts');
    }
}
