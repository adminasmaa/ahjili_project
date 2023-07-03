<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\UserResource;
use App\Http\Resources\PostResource;
use App\Models\User;
use App\Models\MediaStory;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use Maize\Markable\Models\Bookmark;

use App\Models\Ban;
use App\Models\FollowRequest;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }

    public function getUserProfile(Request $request)
    {
        $user = $this->user;

        $success['user'] =   new UserResource($user);
        return $this->sendResponse($success, 'User profile retrieved successfully.');
    }

    public function updateProfile(Request $request)
    {
        $user=$this->user;

        $validator = Validator::make($request->all(), [
            'full_name'     => 'required',
            'username'      => 'sometimes|alpha_dash|unique:users,username,'.$user->id,
            'email'         => 'sometimes|string|unique:users,email,'.$user->id,
            'dob'           => 'required',
            'gender'        => 'required',
            'phone_number'  => 'sometimes|unique:users,phone_number,'.$user->id,
        ], [
            'full_name.required' => 'The :attribute field can not be blank value.',
            'email.required' => 'The :attribute field can not be blank value.',
            'username.unique' => 'The :attribute already exist',
            'email.unique' => 'The :attribute already exist',
            'dob.required' => 'The :attribute field can not be blank value.',
            'gender.required' => 'The :attribute field can not be blank value.',
            'phone_number.required' => 'The :attribute field can not be blank value.',
            'phone_number.unique' => 'The :attribute already exist',
            'username.alpha_dash' => 'Special characters or spaces not allowed in :attribute',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $input=$request->all();
        $input = $request->except(['profile_image']);
        $uploadFolder = 'usersProfile/'.$user->id;
        if (!is_null($request->profile_image)) {
            Storage::disk('public')->deleteDirectory($uploadFolder);
            checkDirectory($uploadFolder);
            $image = $request->file('profile_image');
            $image_uploaded_path = $image->store($uploadFolder, 'public');
            $image_url=$image_uploaded_path;
            $input['profile_image']=$image_url;
        }
        $user->update($input);
        $success['user'] =   new UserResource($user);
        return $this->sendResponse($success, 'User profile update successfully.');
    }

    public function updateProfileImage(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make(
            $request->all(),
            [
           'profile_image' => 'required|image:jpeg,png,jpg|max:5120'
        ],
            [
                'profile_image.required' => 'The :attribute field is required.',
                'profile_image.image' => 'The :attribute must be jpeg,png,jpg.',
                'profile_image.max' => 'The :attribute max size must be 5MB.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $uploadFolder = 'usersProfile/'.$user->id;
        if (!is_null($request->profile_image)) {
            Storage::disk('public')->deleteDirectory($uploadFolder);
            checkDirectory($uploadFolder);
            $image = $request->file('profile_image');
            $image_uploaded_path = $image->store($uploadFolder, 'public');
            $image_url=$image_uploaded_path;
            $profile_image  =$image_url;
            $user->update(['profile_image' => $profile_image]);
        }

        $success['user'] =   new UserResource($user);
        return $this->sendResponse($success, 'User profile picture successfully.');
    }

    public function updateAccountType(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make(
            $request->all(),
            [
            'account_type' => 'required|in:public,private',
        ],
            [
                'account_type.required' => 'The :attribute field is required.',
                'account_type.in' => 'The :attribute value must be public,private.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user->update(['account_type' => $request->account_type]);
        return $this->sendResponse([], 'Account type changed successfully.');
    }


    public function addToBookmark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
        ], [
            'post_id.required' => 'The :attribute field is required.',
            'post_id.exists' => 'The :attribute is not exist.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->getMessages());
        }
        $id=$request->post_id;
        $post = Post::findOrFail($id);
        $user = $this->user;
        $bookmark=Bookmark::add($post, $user);
        return $this->sendResponse([], 'Bookmark successfully');
    }

    public function removeBookmark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:markable_bookmarks,markable_id',
        ], [
            'post_id.required' => 'The :attribute field is required.',
            'post_id.exists' => 'The :attribute is not exist.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->getMessages());
        }
        $id=$request->post_id;
        $post = Post::findOrFail($id);
        $user = $this->user;
        $bookmark=Bookmark::remove($post, $user);
        return $this->sendResponse([], 'Bookmark remove successfully');
    }

    public function bookmarkList(Request $request)
    {
        $posts=Post::whereHasBookmark(
            auth()->user(),
        )->paginate(2);

        PostResource::collection($posts);
        return $this->sendResponse($posts, 'Bookmark list retrieved successfully.');
    }

    public function getUsers(Request $request)
    {
        $users = User::query();
        if ($request->search!='') {
            $users->where('username', 'like', '%'.$request->search.'%');
        }
        $users=$users->get();

        $this->transformgetUsers($users);

        return $this->sendResponse($users, 'All Users List');
    }

    /**
     * getAllUserBanned
     *
     * @return void
     */
    public function getAllUserBanned()
    {
        // make a paginate:
        $paginate = request('count_page') ? request('count_page') : Ban::PAGINATE;

        $bans_ids = Ban::query()->where('user_id', $this->user->id)->pluck('ban_user_id');

        $users=User::whereIn('id', $bans_ids)->paginate($paginate);
        UserResource::collection($users);
        $success =   $users;
        // return response
        return $this->sendResponse($success, 'All Users banned List .');
    }

    /**
     * banUser => function a make ban user by user
     *
     * @param  User $user
     * @return void
     */
    public function banUser(User $user)
    {
        // get user a make ban
        $user_make_ban = $this->user;
        // check if same user
        if ($user_make_ban->id == $user->id) {
            return $this->sendResponse([], 'It is not possible to ban your account');
        }
        // make a ban user
        $ban = Ban::query()->where('user_id', $user_make_ban->id)
        ->where('ban_user_id', $user->id)
        ->first();

        // checked ban
        if ($ban) {
            return $this->sendResponse([], "this user {$user->username} already banned");
        }
        // make a ban user
        $ban_created = Ban::create([
            'ban_user_id' => $user->id,
            'ban_username' => $user->username,
            'ban_full_name' => $user->full_name,
            'profile_image' => $user->profile_image,
            'user_id' => $user_make_ban->id,
        ]);
        // check if added ban:
        if (!$ban_created) {
            return $this->sendResponse([], 'There is a problem with ban');
        }

        if ($user->account_type == User::PUBLIC) {
            // check if is following:
            if ($user_make_ban->isFollowing($user)) {
                $user_make_ban->toggleFollow($user);
            }
            // check if is following:
            if ($user->isFollowing($user_make_ban)) {
                $user->toggleFollow($user_make_ban);
            }
        } else {
            // ---------------------------- case user sended request  ----------------------------------
            // get request for checked
            $checked_user_requested = FollowRequest::query()->where('user_follow_id', $user->id)
                                                ->where('user_id', $user_make_ban->id)
                                                ->where('is_accepted', false)
                                                ->where('has_request_follow', true)->first();
            // checked is exists request or not
            if ($checked_user_requested) {
                // canceled request:
                $checked_user_requested->has_request_follow = false;
                $checked_user_requested->save();
            }
            // ---------------------------- case user accepted  ----------------------------------
            // get request for checked
            $checked_user_accepted = FollowRequest::query()->where('user_follow_id', $user->id)
                                                ->where('user_id', $user_make_ban->id)
                                                ->where('is_accepted', true)
                                                ->where('has_request_follow', false)->first();
            // checked is exists request and accep or not
            if ($checked_user_accepted) {
                // canceled accepted:
                $checked_user_accepted->is_accepted = false;
                $checked_user_accepted->accepted_at = null;
                $checked_user_accepted->save();

                // check if is following:
                if ($user_make_ban->isFollowing($user)) {
                    $user_make_ban->toggleFollow($user);
                }
                // check if is following:
                if ($user->isFollowing($user_make_ban)) {
                    $user->toggleFollow($user_make_ban);
                }
            }
            // ---------------------------- case user make ban sended request  ------------------------------
            // get request for checked
            $checked_user_requested = FollowRequest::query()->where('user_follow_id', $user_make_ban->id)
                                        ->where('user_id', $user->id)
                                        ->where('is_accepted', false)
                                        ->where('has_request_follow', true)->first();
            // checked is exists request or not
            if ($checked_user_requested) {
                // canceled request:
                $checked_user_requested->has_request_follow = false;
                $checked_user_requested->save();
            }

            // get request for checked accepted
            $checked_user_accepted = FollowRequest::query()->where('user_follow_id', $user_make_ban->id)
                                                ->where('user_id', $user->id)
                                                ->where('is_accepted', true)
                                                ->where('has_request_follow', false)->first();
            // checked is exists request or not
            if ($checked_user_accepted) {
                // canceled accepted:
                $checked_user_accepted->is_accepted = false;
                $checked_user_accepted->accepted_at = null;
                $checked_user_accepted->save();

                // check if is following:
                if ($user_make_ban->isFollowing($user)) {
                    $user_make_ban->toggleFollow($user);
                }
                // check if is following:
                if ($user->isFollowing($user_make_ban)) {
                    $user->toggleFollow($user_make_ban);
                }
            }
        }
        // return response
        return $this->sendResponse([], "Ban user {$user->username} successfully.");
    }


    /**
     * deleteLogicUser
     *
     * @return void
     */
    public function deleteLogicUser(Request $request)
    {
        // get a user
        $user = User::find($this->user->id);
        // deleted user
        $deleted = $user->delete();
        // check a deleted
        if (!$deleted) {
            return $this->sendError('This operation is failure,try again');
        }
        $request->user()->currentAccessToken()->delete();
        // return response
        return $this->sendResponse([], "User {$user->username} deleted a successfully.");
    }

    /**
     * unbanUser => function a make ban user by user
     *
     * @param  User $user
     * @return void
     */
    public function unbanUser(User $user)
    {
        // get user a make unban
        $user_make_unban = $this->user;
        // make a ban user
        $ban = Ban::query()->where('user_id', $user_make_unban->id)
                           ->where('ban_user_id', $user->id)
                           ->first();
        // checked found ban
        if (!$ban) {
            return $this->sendResponse([], 'This a ban not found');
        }
        // unban user
        $unban = $ban->delete();

        // check if added ban:
        if (!$unban) {
            return $this->sendResponse([], 'There is a problem with the unban');
        }
        // return response
        return $this->sendResponse([], "Unban user {$user->username} successfully.");
    }

    /**
     * get_all_notifications
     *
     * @return void
     */
    public function getAllNotifications(Request $request)
    {
        // make a paginate:
        $paginate = request('count_page') ? request('count_page') : 10;
        if ($request->has('type')) {
            // get all notification
            $notifications = Notification::query()
                                            ->where('notifiable_id', $this->user->id)
                                            ->where('data->type', $request->type)
                                            ->latest()
                                            ->paginate($paginate);
        } else {
            // get all notification
            $notifications = Notification::query()
                                                ->where('notifiable_id', $this->user->id)
                                                ->latest()
                                                ->paginate($paginate);
        }

        // make in transform:
        $this->transformgetAllNotifications($notifications);
        $success =  $notifications;
        // return response
        return $this->sendResponse($success, 'All Notifications List');
    }

    /**
     * get_unread_notifications
     *
     * @param  mixed $request
     * @return void
     */
    public function getUnreadNotifications(Request $request)
    {
        // make a paginate:
        $paginate = request('count_page') ? request('count_page') : 10;
        if ($request->has('type')) {
            // get all notification
            $notifications = Notification::query()
                                            ->where('notifiable_id', $this->user->id)
                                            ->where('data->type', $request->type)
                                            ->whereNull('read_at')
                                            ->latest()
                                            ->paginate($paginate);
        } else {
            // get all notification
            $notifications = Notification::query()
                                                ->where('notifiable_id', $this->user->id)
                                                ->whereNull('read_at')
                                                ->latest()
                                                ->paginate($paginate);
        }

        // make in transform:
        $this->transformgetAllNotifications($notifications);
        $success =  $notifications;
        // return response
        return $this->sendResponse($success, 'All Unreaded Notifications List');
    }

    /**
     * get_unread_notifications
     *
     * @param  mixed $request
     * @return void
     */
    public function getReadNotifications(Request $request)
    {
        // make a paginate:
        $paginate = request('count_page') ? request('count_page') : 10;
        if ($request->has('type')) {
            // get all notification
            $notifications = Notification::query()
                                            ->where('notifiable_id', $this->user->id)
                                            ->where('data->type', $request->type)
                                            ->whereNotNull('read_at')
                                            ->latest()
                                            ->paginate($paginate);
        } else {
            // get all notification
            $notifications = Notification::query()
                                                ->where('notifiable_id', $this->user->id)
                                                ->whereNotNull('read_at')
                                                ->latest()
                                                ->paginate($paginate);
        }

        // make in transform:
        $this->transformgetAllNotifications($notifications);
        $success =  $notifications;
        // return response
        return $this->sendResponse($success, 'All Readed Notifications List');
    }


    /**
     * markReadNotification
     *
     * @return void
     */
    public function markReadNotification()
    {
        // mark all notifications read
        $this->user->unreadNotifications->markAsRead();

        // return response
        return $this->sendResponse([], 'Mark Notifications Readed');
    }




    //tested


    public function oldgetUserProfile(Request $request)
    {
        $user = Auth::user();
        return $this->oldsendUserProfile($user, 'User Profile', false);
    }


    public function uploadnewStory(Request $request)
    {
        $path = request()->file('profile_picture')->store('ahjili_app/images', 's3');
        $bucket_path = Storage::disk('s3')->url($path);
        dd($bucket_path);
    }

    public function saveUserStory(Request $request)
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

        if ($request->vedio_file!= '') {
            $validator = Validator::make($request->all(), [
                'vedio_file' => 'required|file',
                'vedio_file.*' => 'mimetypes:video/mp4',
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->getMessages());
            }
        }

        ini_set('memory_limit', '44M');
        $vedio_base_location = 'ahjili_app/stories/vedios';
        $image_id = generateImageId();
        $imageResponse=false;

        if ($request->hasFile('vedio_file')) {
            $documentPath = $request->file('vedio_file')->store($vedio_base_location, 's3');
            $bucket_path = Storage::disk('s3')->url($documentPath);
            $file = $request->file('vedio_file');
            $name = $file->getClientOriginalName();
            //store image file into directory and db
            $save = new MediaStory();
            $save->title = $name ?? $image_id;
            $save->user_id = auth()->id();
            $save->story_media_id = $image_id;
            $save->path = $bucket_path;
            $save->type = 'video';
            $save->save();
            $imageResponse=true;
        }

        $files = $request->file('images');
        $image_base_location = 'ahjili_app/stories/images';
        if ($request->hasFile('images')) {
            foreach ($files as $file) {
                $documentPath = $file->store($image_base_location, 's3');
                $bucket_path = Storage::disk('s3')->url($documentPath);
                $name = $file->getClientOriginalName();
                //store image file into directory and db
                $save = new MediaStory();
                $save->title = $name ?? $image_id;
                $save->user_id = auth()->id();
                $save->story_media_id = $image_id;
                $save->path = $bucket_path;
                $save->type = 'image';
                $save->save();
                $imageResponse=true;
            }
        }

        if ($imageResponse == true) {
            //save story
            $story = Story::create([
                'user_id' => auth()->id(),
                'media'   => $image_id,
                'description'    => null,
                'type'    => 'vedio',
                'status'  => 'active',
            ]);

            return $this->sendResponse('success', ['message'=>['Story created successfully']]);
        } else {
            return $this->sendError('failed', ['message' => ['Something went wrong']]);
        }
    }
}
