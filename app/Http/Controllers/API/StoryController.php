<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Ban;
use App\Models\MediaStory;
use Illuminate\Http\Request;
use App\Models\Story;
use App\Models\StorySeen;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StoryController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }
    public function store(Request $request)
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
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'audio' => 'required|file',
                'audio.*' => 'mimes:audio/mpeg,mpga,mp3,wav',
            ]);
        }


        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->getMessages());
        }

        if ($request['type'] == 'image') {
            $imageResponse =  storyImages($request);
        } elseif ($request['type'] == 'video') {
            $imageResponse =  storyVideo($request);
        } else {
            $imageResponse =  storyAudio($request);
        }
        if ($imageResponse['file_uploaded'] == true) {
            $story = Story::create([
                'user_id' => auth()->id(),
                'media'   => $imageResponse['image_id'],
                'description'    => $request['description'],
                'type'    => $request['type'],
                'status'  => $request['status']
            ]);
            return $this->sendResponse('success', ['message'=>['Story created successfully']]);
        } else {
            return $this->sendError('failed', ['message' => ['Something went wrong']]);
        }
    }

    public function newStoryStore(Request $request)
    {
        if ($request->images!= '') {
            $validator = Validator::make($request->all(), [
                'images' => 'required|array|max:5',
                'images.*' => 'mimes:jpeg,jpg,png,gif',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->getMessages());
            }
        }
        if ($request->video!= '') {
            $validator = Validator::make($request->all(), [
                'video' => 'required|file',
                'video.*' => 'mimetypes:video/mp4',
            ]);


            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->getMessages());
            }
        }

        $imageResponse['file_uploaded']=false;
        if (request()->has('images')) {
            $imageResponse =  storyS3Images($request);
            //$path = request()->file('profile_picture')->store('ahjili_app/images', 's3');
            //$bucket_path = Storage::disk('s3')->url($path);
        }
        if (request()->has('video')) {
            $imageResponse =  storyS3Video($request);
        }

        if ($imageResponse['file_uploaded'] == true) {
            $story = Story::create([
                'user_id' => auth()->id(),
                'media'   => $imageResponse['image_id'],
                'description'    => null,
                'type'    => $request['type'],
                'status'  => 'active',
            ]);
            return $this->sendResponse('success', ['message'=>['Story created successfully']]);
        } else {
            return $this->sendError('failed', ['message' => ['Something went wrong']]);
        }
    }

    public function lisiting(Request $request)
    {
        $stories = Story::with('getStoryMedia')->get();
        if (count($stories) > 0) {
            return $this->sendStoryListing($stories, 'All user stories');
        } else {
            return $this->sendResponse('failed', ['message' => ['No story found']]);
        }
    }

    public function newlisiting()
    {
        // get users blocked:
        $users_blocked = Ban::query()->where('user_id', $this->user->id)->pluck('ban_user_id');
        $users_has_blocked = Ban::query()->where('ban_user_id', $this->user->id)->pluck('user_id');
        // get users following:
        $users_followings = $this->user->followings()->with('followable')->pluck('followable_id')->push($this->user->id);

        $stories = MediaStory::query()
                            ->whereIn('user_id', $users_followings)
                            ->whereNotIn('user_id', $users_blocked)
                            ->whereNotIn('user_id', $users_has_blocked)
                            ->groupBy('user_id')
                            ->get();

        if (count($stories) > 0) {
            return $this->sendUserStoryListing($stories, 'All user stories');
        } else {
            return $this->sendResponse('failed', ['message' => ['No story found']]);
        }
    }

    public function story_seen(Request $request)
    {
        StorySeen::create([
            'story_id' => $request['story_id'],
            'user_id'  => $request['user_id']
        ]);

        return $this->sendResponse('success', 'Story seen');
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'post_id' => 'required|exists:stories,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->getMessages());
        }

        $id = auth()->id();
        $post_id=$request->post_id;
        $story = Story::findorFail($post_id);
        $storymedia=$story->getStoryMedia;

        ini_set('memory_limit', '44M');

        if (count($storymedia)>0) {
            foreach ($storymedia as $single) {
                $opath= explode('https://ahjili.s3.eu-central-1.amazonaws.com/', $single->path);
                $path= $opath[1]?? "";
                if (Storage::disk('s3')->exists($path)) {
                    Storage::disk('s3')->delete($path);
                }
                $single->delete();
            }
        }

        if ($story) {
            $story->delete();
            return $this->sendResponse('success', ['message'=>['Story deleted successfully']]);
        } else {
            return $this->sendError('failed', ['message' => ['Something went wrong']]);
        }
    }

    public function show_stories()
    {
        // get users blocked:
        $users_blocked = Ban::query()->where('user_id', $this->user->id)->pluck('ban_user_id');
        $users_has_blocked = Ban::query()->where('ban_user_id', $this->user->id)->pluck('user_id');
        // get users following:
        $users_followings = $this->user->followings()->with('followable')->pluck('followable_id')->push($this->user->id);
        // get all stories
        $stories = MediaStory::query()
                            ->where('user_id', '!=', $this->user->id)
                            ->whereIn('user_id', $users_followings)
                            ->whereNotIn('user_id', $users_blocked)
                            ->whereNotIn('user_id', $users_has_blocked)
                            ->groupBy('user_id')
                            ->get();
        // get store of user
        $store_of_user = MediaStory::query()->where('user_id', $this->user->id)->groupBy('user_id')->get();

        // transform all stories all users
        $transtorm_stories = $stories->transform(function ($story) {
            $images_array = MediaStory::where('user_id', $story->user_id)->get();
            $images = [];
            if (count($images_array)) {
                foreach ($images_array as $image) {
                    if ($image['type'] == MediaStory::VIDEO) {
                        if (get_path_storage($image['path'])) {
                            $file=$image['path'] ?? "";
                        } else {
                            $file = '';
                        }
                        $images[] = [
                            'file_id' => $image['id'],
                            'story_file_group_id' => $image['story_media_id'],
                            'file_type' => $image['type'],
                            'file' =>  $file,
                            'created_at' => $image['created_at']->diffForHumans()
                        ];
                    } else {
                        $images[] = [
                            'file_id' => $image['id'],
                            'story_file_group_id' => $image['story_media_id'],
                            'file_type' => $image['type'],
                            'file' =>  $image['path'] ?? "",
                            'created_at' => $image['created_at']->diffForHumans()
                        ];
                    }
                }
            }

            $user = User::where('id', $story['user_id'])->first();
            $storyCount = StorySeen::where('user_id', Auth::user()->id)->where('story_id', $story['id'])->count();

            $image = isset($user->userProfile->profile_image) ? get_path_storage($user->userProfile->profile_image) : url('/')."/images/ahjili.png";

            $data = [
                'post_id'      => $story['id'],
                'user_id'      => $story['user_id'],
                'user_name'    => $user['username'],
                'profile_image' => $image ?? "",
                'description'  => $story['description'] ?? null,
                'seen'         => $storyCount,
                'media'        => $images,
                'created_at'        => $story['created_at']->diffForHumans(),

            ];

            return $data;
        });

        // transform story of user
        $transtorm_story = $store_of_user->transform(function ($story) {
            $images_array = MediaStory::where('user_id', $story->user_id)->get();
            $images = [];
            if (count($images_array)) {
                foreach ($images_array as $image) {
                    if ($image['type'] == MediaStory::VIDEO) {
                        if (get_path_storage($image['path'])) {
                            $file=$image['path'] ?? "";
                        } else {
                            $file = '';
                        }
                        $images[] = [
                            'file_id' => $image['id'],
                            'story_file_group_id' => $image['story_media_id'],
                            'file_type' => $image['type'],
                            'file' =>  $file,
                            'created_at' => $image['created_at']->diffForHumans()
                        ];
                    } else {
                        $images[] = [
                            'file_id' => $image['id'],
                            'story_file_group_id' => $image['story_media_id'],
                            'file_type' => $image['type'],
                            'file' =>  $image['path'] ?? "",
                            'created_at' => $image['created_at']->diffForHumans()
                        ];
                    }
                }
            }

            $user = User::where('id', $story['user_id'])->first();
            $storyCount = StorySeen::where('user_id', Auth::user()->id)->where('story_id', $story['id'])->count();

            $image = isset($user->userProfile->profile_image) ? get_path_storage($user->userProfile->profile_image) : url('/')."/images/ahjili.png";

            $data = [
                'post_id'      => $story['id'],
                'user_id'      => $story['user_id'],
                'user_name'    => $user['username'],
                'profile_image' => $image ?? "",
                'description'  => $story['description'] ?? null,
                'seen'         => $storyCount,
                'media'        => $images,
                'created_at'        => $story['created_at']->diffForHumans(),

            ];

            return $data;
        });


        // return response
        return response()->json([
            "success" => true,
            "my_story" => $transtorm_story,
            "other_stories" => $transtorm_stories
        ]);
    }
}
