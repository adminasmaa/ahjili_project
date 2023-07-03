<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller as Controller;
use App\Models\User;
use App\Models\StorySeen;
use App\Models\MediaStory;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        // put messages errors in data
        $response['data'] = !empty($errorMessages) ? $errorMessages : [];
        // return response
        return response()->json(
            $response,
        );
    }

    public function transformgetUsers(&$users)
    {
        $users->transform(function ($result) {
            $data = [
                'id' => $result['id'],
                'full_name' => $result['full_name'],
                'username' => $result['username'],
                'profile_image' => get_profile_image($result->profile_image)
            ];
            return $data;
        });
    }

    /**
     * transformgetAllNotifications
     *
     * @param  mixed $notifications
     * @return void
     */
    public function transformgetAllNotifications(&$notifications)
    {
        $notifications->transform(function ($result) {
            $data = json_decode($result['data'], true);
            $data['created_at'] =  $result['created_at']->format('d-m-Y H:i:s');
            return $data;
        });
    }

    /**
     * transformgetUsersBanned
     *
     * @param  mixed $bans
     * @return void
     */
    public function transformgetUsersBanned(&$bans)
    {
        $bans->transform(function ($result) {
            $data = [
                'id' => $result['id'],
                'ban_full_name' => $result['ban_full_name'],
                'ban_username' => $result['ban_username'],
                'user_id' => $result['user_id'],
                'ban_user_id' => $result['ban_user_id'],
                'profile_image' => get_profile_image($result->profile_image)
            ];
            return $data;
        });
    }

    public function transformAllTags(&$tags)
    {
        $tags->transform(function ($result) {
            $data = [
                'id' => $result['id'],
                'tag' => $result['slug']
            ];
            return $data;
        });
    }

    //tested


    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function oldsendUserProfile($result, $message, $type)
    {
        //  dd($result->followers->count());
        $followed = false;
        if ($type == true) {
            $followed = $result->isFollowedBy(Auth::user());
        }

        $image = isset($result->userProfile->profile_image) ? get_path_storage($result->userProfile->profile_image) : '';

        $result = [
            'id'            => $result->id,
            'full_name'     => $result->full_name,
            'username'      => $result->username,
            'country_code'  => $result->country_code,
            'phone_number'  => $result->phone_number,
            'email'         => $result->email,
            'dob'           => $result->dob,
            'gender'        => $result->gender,
            'total_post'    => getUserPostsCount($result->id),
            'feeds'         => 0,
            'followers_count' => $result->followers->count(),
            'following'     => $result->followings->count(),
            'reels_count'   => 0,
            'subscription_count'  => 0,
            'favorites_count' => 0,
            'has_followed' => $followed,
            'boosted_post_count' => 0,
            'terms'         => $result->terms,
            'profile_image' => $image,
            'website'       => $result->userProfile->website ?? ' ',
            'description'   => $result->userProfile->description ?? ' ',
            'active_status' => $result->active_status ?? ''
        ];
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }

    public function sendUserProfile($result, $message, $type)
    {
        $image_gif_posts = getUserPostsCustom($result->id, [Post::IMAGE,Post::GIF]);
        $reels_posts = getUserPostsCustom($result->id, [Post::VIDEO]);
        $text_posts = getUserPostsCustom($result->id, [Post::TEXT]);
        $audio_posts = getUserPostsCustom($result->id, [Post::AUDIO]);
        $this->transformAllUsersPosts($image_gif_posts);
        $this->transformAllUsersPosts($reels_posts);
        $this->transformAllUsersPosts($text_posts);
        $this->transformAllUsersPosts($audio_posts);

        $image_gif_posts_count=$result->posts->whereIn('type', [Post::IMAGE,Post::GIF])->count();
        $reels_posts_count=$result->posts->where('type', Post::VIDEO)->count();
        $text_posts_count=$result->posts->where('type', Post::TEXT)->count();
        $audio_posts_count=$result->posts->where('type', Post::AUDIO)->count();

        $followed = false;
        if ($type == true) {
            $followed = $result->isFollowedBy(Auth::user());
        }

        $image = isset($result->userProfile->profile_image) ? get_path_storage($result->userProfile->profile_image) : url('/')."/images/ahjili.png";

        $result = [
            'id'            => $result->id,
            'full_name'     => $result->full_name,
            'username'      => $result->username,
            'country_code'  => $result->country_code,
            'phone_number'  => $result->phone_number,
            'email'         => $result->email,
            'dob'           => $result->dob,
            'gender'        => $result->gender,
            'total_post'    => getUserPostsCount($result->id),
            'feeds'         => 0,
            'followers_count' => $result->followers->count(),
            'following'     => $result->followings->count(),
            'reels_count'   => $reels_posts_count ?? 0,
            'image_gif_posts_count'=> $image_gif_posts_count ?? 0,
            'text_posts_count'=> $text_posts_count ?? 0,
            'audio_posts_count'=> $audio_posts_count ?? 0,
            'subscription_count'  => 0,
            'favorites_count' => 0,
            'has_followed' => $followed,
            'boosted_post_count' => 0,
            'terms'         => $result->terms,
            'profile_image' => $image,
            'website'       => $result->userProfile->website ?? ' ',
            'description'   => $result->userProfile->description ?? ' ',
            'active_status' => $result->active_status ?? ''
        ];
        $response = [
            'success' => true,
            'user'    => $result,
            'image_gif_posts' => $image_gif_posts,
            'reels_posts' => $reels_posts,
            'text_posts' => $text_posts,
            'audio_posts' => $audio_posts,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendUserPosts($results, $message)
    {
        $data = [];
        $this->trasnsformUserPosts($results);
        $response = [
            'success' => true,
            'data'    => $results,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendUserAllPosts($results, $message)
    {
        $this->transformAllUsersPosts($results);
        $response = [
            'success' => true,
            'profile_iamge' => getUserProfileImage(Auth::user()->id),
            'data'    => $results,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }

    public function sendPostComments($results, $message)
    {
        $this->transformComments($results);

        $response = [
            'success' => true,
            'data'    => $results,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }

    public function transformComments(&$comments)
    {
        $comments->transform(function ($result) {
            $replies = [];

            if (count($result['replies']) > 0) {
                foreach ($result['replies'] as $reply) {
                    $user = User::where('id', $reply['user_id'])->first();
                    if (!empty($reply)) {
                        $replies[] = [
                            'comment_id' => $reply['id'],
                            'comment'  => $reply['body'],
                            'user_id' => $reply['user_id'],
                            'user_fullname' => $user['full_name'],
                            'user_name' =>  $user['username'],
                            'user_profile_image' => getUserProfileImage($reply['user_id']),
                            'created_at'            => $reply['created_at']->diffForHumans()
                        ];
                    }
                }
            } else {
                $replies[] = [
                    'comment_id' => '',
                    'comment'  => '',
                    'user_id' => '',
                    'user_fullname' => '',
                    'user_name' =>  '',
                    'user_profile_image' => '',
                    'created_at' => ''
                ];
            }

            $data = [
                'comment_id' => $result['id'],
                'comment'  => $result['body'],
                'user_id' => $result['user_id'],
                'user_fullname' => $result['user']['full_name'],
                'user_name' =>  $result['user']['username'],
                'user_profile_image' => getUserProfileImage($result['user_id']),
                'replies_count' => count($result['replies']),
                'replies'   =>  $replies,
                'created_at'  => $result['created_at']->diffForHumans()
            ];

            return $data;
        });
    }

    public function trasnsformUserPosts(&$posts)
    {
        $posts->transform(function ($result) {
            $images = [];
            if (count($result['getPostImages']) > 0) {
                foreach ($result['getPostImages'] as $image) {
                    if ($image['type'] == "video") {
                        if (get_path_storage($image['path'])) {
                            $file = str_replace('storage/', '', get_path_storage($image['path']));
                        } else {
                            $file = '';
                        }
                        $images[] = [
                            'file_id' => $image['id'],
                            'post_file_group_id' => $image['post_image_id'],
                            'file_type' => $image['type'],
                            'file' =>  $file,
                        ];
                    } else {
                        $images[] = [
                            'file_id' => $image['id'],
                            'post_file_group_id' => $image['post_image_id'],
                            'file_type' => $image['type'],
                            'file' =>  get_path_storage($image['path']),
                        ];
                    }
                }
            }
            $likers = $result->likers()->get();
            $likedUser = [];
            if (count($likers) > 0) {
                foreach ($likers as $user) {
                    $likedUser [] = array('username' => $user->username, 'user_id' => $user->id );
                }
            }
            $gif = '';
            if ($result['type'] == Post::GIF) {
                $gif = $result['post_image_id'];
            }
            $data = [
                'post_id'               => $result['id'],
                'post_body'             => $result['body'],
                'post_user_id'          => $result['user_id'],
                'is_liked_by_user'      => $result->isLikedBy(Auth::user()),
                'user_name'             => getUserName($result['user_id']),
                'profile_image'         => getUserProfileImage($result['user_id']),
                'post_file_group_id'    => $result['post_image_id'],
                'liked_users'           => $likedUser,
                'pos_likes_count'       => $result->likers()->count(),
                'post_files'            => $images,
                'gif_url'               => $gif,
                'post_status'           => $result['status'],
                'type'                  => $result['type'],
                'created_at'            => $result['created_at']->diffForHumans()
            ];

            return $data;
        });
    }

    public function transformAllUsersPosts(&$posts)
    {
        $posts->transform(function ($result) {
            $images = [];
            if (count($result['getPostImages']) > 0) {
                foreach ($result['getPostImages'] as $image) {
                    if ($image['type'] == "video") {
                        if (get_path_storage($image['path'])) {
                            //$file = str_replace('storage/', '', Storage::disk('public')->url($image['path']));
                            $file=$image['path'];
                            if (is_null($image['path_thumbnail'])) {
                                $file_thumb= "";
                            } else {
                                //$file_thumb = str_replace('storage/', '', Storage::disk('public')->url($image['path_thumbnail']));
                                $file_thumb=$image['path_thumbnail'];
                            }
                        } else {
                            $file = '';
                        }
                        $images[] = [
                            'file_id' => $image['id'],
                            'post_file_group_id' => $image['post_image_id'],
                            'file_type' => $file_thumb ?? "",
                            'file' =>  $file ?? "",
                        ];
                    } else {
                        $images[] = [
                            'file_id' => $image['id'],
                            'post_file_group_id' => $image['post_image_id'],
                            'file_type' => $image['type'],
                            'file' =>  $image['path'] ?? "",
                        ];
                    }
                }
            }
            $post_user = User::where('id', $result['user_id'])->with('UserProfile')->first();
            $likers = $result->likers()->get();
            $likedUser = [];
            if (count($likers) > 0) {
                foreach ($likers as $user) {
                    $likedUser [] = array('username' => $user->username, 'user_id' => $user->id );
                }
            }
            $gif = '';
            if ($result['type'] == Post::GIF) {
                $gif = $result['post_image_id'];
            }
            $data = [
                'post_id'               => $result['id'],
                'post_body'             => $result['body'],
                'post_user_id'          => $result['user_id'],
                'is_liked_by_user'      => $result->isLikedBy(Auth::user()),
                'user_name'             => $post_user->username ?? '',
                'profile_image'         => getUserProfileImage($result['user_id']),
                'comments_count'        => count($result['comments']),
                'post_file_group_id'    => $result['post_image_id'],
                'liked_users'           => $likedUser,
                'gif_url'               => $gif,
                'is_user_post'          => ($result['user_id'] == Auth::user()->id) ? true : false,
                'pos_likes_count'       => $result->likers()->count(),
                'post_files'            => $images,
                'post_status'           => $result['status'],
                'type'                  => $result['type'],
                'anonymous'             => $result['anonymous'],
                'created_at'            => $result['created_at']->diffForHumans()
            ];

            return $data;
        });
    }

    public function sendStoryListing(&$stories)
    {
        $data = [];
        $this->transformStories($stories);
        $response = [
            'success' => true,
            'data'    => $stories
        ];

        return response()->json($response, 200);
    }

    public function transformStories(&$stories)
    {
        $stories->transform(function ($story) {
            //dd($story);
            $images_array = $story['getStoryMedia'];
            $images = [];
            if (count($images_array)) {
                foreach ($images_array as $image) {
                    if ($image['type'] == MediaStory::VIDEO) {
                        $file=get_path_storage($image['path']) ? $image['path'] : '';
                        $images[] = [
                            'file_id' => $image['id'],
                            'story_file_group_id' => $image['story_media_id'],
                            'file_type' => $image['type'],
                            'file' =>  $file,
                        ];
                    } else {
                        $images[] = [
                            'file_id' => $image['id'],
                            'story_file_group_id' => $image['story_media_id'],
                            'file_type' => $image['type'],
                            'file' =>  $image['path'] ?? "",
                        ];
                    }
                }
            }
            $user = User::where('id', $story['user_id'])->first();
            $storyCount = StorySeen::where('user_id', Auth::user()->id)->where('story_id', $story['id'])->count();
            $data = [
                'post_id'      => $story['id'],
                'user_id'      => $story['user_id'],
                'user_name'    => $user['username'],
                'description'  => $story['description'] ?? null,
                'seen'         => $storyCount,
                'media'        => $images
            ];

            return $data;
        });
    }

    public function sendUserStoryListing(&$stories)
    {
        $data = [];
        $this->transformUserStories($stories);
        $response = [
            'success' => true,
            'data'    => $stories
        ];

        return response()->json($response, 200);
    }

    public function transformUserStories(&$stories)
    {
        $stories->transform(function ($story) {
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
                            'created_at' => $image['created_at']
                        ];
                    } else {
                        $images[] = [
                            'file_id' => $image['id'],
                            'story_file_group_id' => $image['story_media_id'],
                            'file_type' => $image['type'],
                            'file' =>  $image['path'] ?? "",
                            'created_at' => $image['created_at']
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
    }

    public function transformEditPost(&$post)
    {
        $post->transform(function ($result) {
            $images = [];
            if (count($result['getPostImages']) > 0) {
                foreach ($result['getPostImages'] as $image) {
                    if ($image['type'] == Post::VIDEO) {
                        $file = get_path_storage($image['path']) ? str_replace('storage/', '', get_path_storage($image['path'])) : '';
                        $images[] = [
                            'file_id' => $image['id'],
                            'post_file_group_id' => $image['post_image_id'],
                            'file_type' => $image['type'],
                            'file' =>  $file,
                        ];
                    } else {
                        $images[] = [
                            'file_id' => $image['id'],
                            'post_file_group_id' => $image['post_image_id'],
                            'file_type' => $image['type'],
                            'file' =>  get_path_storage($image['path']),
                        ];
                    }
                }
            }
            $post_user = User::where('id', $result['user_id'])->with('UserProfile')->first();
            $likers = $result->likers()->get();
            $likedUser = [];
            if (count($likers) > 0) {
                foreach ($likers as $user) {
                    $likedUser [] = array('username' => $user->username, 'user_id' => $user->id );
                }
            }
            $gif = '';
            if ($result['type'] == Post::GIF) {
                $gif = $result['post_image_id'];
            }
            $data = [
                'post_id'               => $result['id'],
                'post_body'             => $result['body'],
                'post_user_id'          => $result['user_id'],
                'user_name'             => $post_user->username ?? '',
                'profile_image'         => getUserProfileImage($result['user_id']),
                'post_file_group_id'    => $result['post_image_id'],
                'gif_url'               => $gif,
                'is_user_post'          => ($result['user_id'] == Auth::user()->id) ? true : false,
                'post_files'            => $images,
                'post_status'           => $result['status'],
                'type'                  => $result['type'],
                'created_at'            => $result['created_at']->diffForHumans()
            ];

            return $data;
        });
    }
}
