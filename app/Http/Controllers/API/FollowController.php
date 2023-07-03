<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Models\Ban;
use App\Models\FollowRequest;
use App\Models\Notification;
use App\Notifications\AcceptFollowed;
use App\Notifications\CancelFollowed;
use App\Notifications\RequestFollowed;
use App\Notifications\UserFollowed;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class FollowController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }

    public function followUnfollow(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
            'id' => 'required|exists:users,id',
        ],
            [  'id.required' => 'The :attribute field can not be blank value.',
               'id.exists' => 'The :attribute is not exist.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // get id user for follow
        $id=$request->id;

        // get user banned if founded
        $user_banned = Ban::query()
                    ->where('ban_user_id', $id)
                    ->orWhere('ban_user_id', $this->user->id)
                    ->where('user_id', $this->user->id)
                    ->orWhere('user_id', $id)
                    ->first();

        // checked
        if ($user_banned && $id != $this->user->id) {
            // send response for banned user
            if (Gate::allows('check-user-banned', $user_banned)) {
                return $this->sendError('unauthoized cause banned .');
            }
        }

        $user1 = User::find($this->user->id);
        $user2 = User::find($id);


        if ($user1->isFollowing($user2)) {
            $user1->toggleFollow($user2);
            // return response
            $success['status']=["User unfollowed"];
            return $this->sendResponse($success, 'User unfollowed successfully.');
        } else {
            if ($user2->account_type == 'private') {
                // get request for checked
                $checked_requesed = FollowRequest::query()->where('user_follow_id', $user1->id)
                                                    ->where('user_id', $user2->id)
                                                    ->where('is_accepted', false)
                                                    ->where('has_request_follow', true)->first();
                // checked is exists request or not
                if ($checked_requesed) {
                    // canceled request:
                    $checked_requesed->has_request_follow = false;
                    $checked_requesed->save();
                    // send notification:
                    $user2->notify(new CancelFollowed($user1, $user2->fcmtoken));
                    // send response:
                    $success['status']=["User request follow"];
                    return $this->sendResponse($success, "User canceled request follow successfully.");
                }
                // get request follow if exists
                $follow_request = FollowRequest::query()->where('user_follow_id', $user1->id)
                                                        ->where('user_id', $user2->id)
                                                        ->where('is_accepted', false)
                                                        ->where('has_request_follow', false)->first();
                // check exists
                if ($follow_request) {
                    $follow_request->has_request_follow = true;
                    $follow_request->save();
                } else {
                    // create a new request
                    FollowRequest::create([
                        'user_follow_id' => $user1->id,
                        'user_id' => $user2->id,
                    ]);
                }
                // send notification:
                $user2->notify(new RequestFollowed($user1, $user2->fcmtoken));
                // send response
                $success['status']=["User request follow"];
                return $this->sendResponse($success, "User sended request follow to {$user2->username} successfully.");
            } else {
                $user1->toggleFollow($user2);
                // send notification
                $user2->notify(new UserFollowed($user1, $user2->fcmtoken));
                // return response
                $success['status']=["User followed"];
                return $this->sendResponse($success, 'User followed successfully.');
            }
        }
    }

    /**
     * acceptedFollow
     *
     * @param  mixed $request
     * @return void
     */
    public function acceptedFollow(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
            'id' => 'required|exists:users,id',
        ],
            [  'id.required' => 'The :attribute field can not be blank value.',
               'id.exists' => 'The :attribute is not exist.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // get id user for follow
        $id=$request->id;

        // get user banned if founded
        $user_banned = Ban::query()
                    ->where('ban_user_id', $id)
                    ->orWhere('ban_user_id', $this->user->id)
                    ->where('user_id', $this->user->id)
                    ->orWhere('user_id', $id)
                    ->first();

        // checked
        if ($user_banned && $id != $this->user->id) {
            // send response for banned user
            if (Gate::allows('check-user-banned', $user_banned)) {
                return $this->sendError('unauthoized cause banned .');
            }
        }

        $user1 = User::find($this->user->id);
        $user2 = User::find($id);

        // checked account_type
        if ($user1->account_type != 'private') {
            return $this->sendError('this account not private, lose your info');
        }
        // get request follow
        $checked_requesed = FollowRequest::query()->where('user_follow_id', $user2->id)
                                        ->where('user_id', $user1->id)
                                        ->where('is_accepted', 1)
                                        ->where('has_request_follow', 0)->exists();

        // checked is exists accepted or not
        if ($checked_requesed) {
            $success['status']=["User request follow"];
            return $this->sendResponse($success, "User Already accepted request {$user2->username}.");
        }

        // get request follow
        $follow_request = FollowRequest::query()->where('user_follow_id', $user2->id)
                            ->where('user_id', $user1->id)
                            ->where('is_accepted', 0)
                            ->where('has_request_follow', 1)->first();
        // cheched if has request
        if (!$follow_request) {
            return $this->sendError('Not found requested');
        } else {
            $follow_request->is_accepted = true;
            $follow_request->has_request_follow = false;
            $follow_request->accepted_at = Carbon::now()->format('Y-m-d');
            $follow_request->save();
            // make following user2
            $user2->toggleFollow($user1);
            // send notification
            $user2->notify(new AcceptFollowed($user1, $user2->fcmtoken));
            // delete notification request_follow
            $notification = Notification::query()->where('type', 'App\Notifications\RequestFollowed')
            ->where('notifiable_id', $user1->id)
            ->where('data->user->id', $user2->id)->first();
            $notification->delete();
            // return response
            $success['status']=["User followed"];
            return $this->sendResponse($success, "User accepting following by {$user2->username}");
        }
    }

    /**
     * rejectedFollow
     *
     * @param  mixed $request
     * @return void
     */
    public function rejectedFollow(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
            'id' => 'required|exists:users,id',
        ],
            [  'id.required' => 'The :attribute field can not be blank value.',
               'id.exists' => 'The :attribute is not exist.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // get id user for follow
        $id=$request->id;

        // get user banned if founded
        $user_banned = Ban::query()
                    ->where('ban_user_id', $id)
                    ->orWhere('ban_user_id', $this->user->id)
                    ->where('user_id', $this->user->id)
                    ->orWhere('user_id', $id)
                    ->first();

        // checked
        if ($user_banned && $id != $this->user->id) {
            // send response for banned user
            if (Gate::allows('check-user-banned', $user_banned)) {
                return $this->sendError('unauthoized cause banned .');
            }
        }

        $user1 = User::find($this->user->id);
        $user2 = User::find($id);
        // get request follow
        $checked_requesed = FollowRequest::query()->where('user_follow_id', $user2->id)
                                            ->where('user_id', $user1->id)
                                            ->where('is_accepted', 0)
                                            ->where('has_request_follow', 0)->exists();

        // checked is exists accepted or not
        if ($checked_requesed) {
            $success['status']=["User request follow"];
            return $this->sendResponse($success, "User Already rejected request {$user2->username}.");
        }
        // checked account_type
        if ($user1->account_type != 'private') {
            return $this->sendError('this account not private, lose your info');
        }

        // get request follow
        $follow_request = FollowRequest::query()->where('user_follow_id', $user2->id)
                            ->where('user_id', $user1->id)
                            ->where('is_accepted', 0)
                            ->where('has_request_follow', 1)->first();
        // cheched if has request
        if (!$follow_request) {
            return $this->sendError('Not found requested');
        } else {
            $follow_request->has_request_follow = false;
            $follow_request->save();
            // send notification
            $user2->notify(new CancelFollowed($user1, $user2->fcmtoken));
            // delete notification request_follow
            $notification = Notification::query()->where('type', 'App\Notifications\RequestFollowed')
            ->where('notifiable_id', $user1->id)
            ->where('data->user->id', $user2->id)->first();
            $notification->delete();
            // return response
            $success['status']=["User followed"];
            return $this->sendResponse($success, "User canceled followed {$user2->username}");
        }
    }


    public function followersList(Request $request)
    {
        $followersids=$this->user->followers->pluck('id')->toArray();
        if (count($followersids)>0) {
            $followers=User::whereIn('id', $followersids)->paginate(20);
            UserResource::collection($followers);
            $success =   $followers;
            return $this->sendResponse($success, 'followers data retrieved successfully.');
        } else {
            return $this->sendError('no followers.', array());
            //return $this->sendError('no followers.', array('followers' => ["no followers exist related to this user."]));
        }
    }

    public function followingList(Request $request)
    {
        $followingids=$this->user->followings->pluck('followable_id')->toArray();
        if (count($followingids)>0) {
            $followings=User::whereIn('id', $followingids)->paginate(20);
            UserResource::collection($followings);
            $success =   $followings;
            return $this->sendResponse($success, 'followings data retrieved successfully.');
        } else {
            return $this->sendError('no followings.', array());
            //return $this->sendError('no followings.', array('followings' => ["no followings exist related to this user."]));
        }
    }
}
