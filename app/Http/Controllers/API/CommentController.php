<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Comment;
use App\Models\Post;
use App\Http\Resources\CommentResource;
use App\Models\Ban;
use App\Models\User;
use App\Notifications\ReplyCommented;
use App\Notifications\UserCommented;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CommentController extends BaseController
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
        $validator = Validator::make(
            $request->all(),
            [
            'post_id' => 'required|exists:posts,id',
            'body'=>'required',
        ],
            [
                'post_id.required' => 'The :attribute field is required.',
                'post_id.exists' => 'The :attribute is not exists.',
                'body.required' => 'The :attribute field is required.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['user_id'] = $this->user->id;

        // get a user id had post
        $get_user_of_posted = Post::query()->findOrFail($request->post_id)->user_id;

        $user_banned = Ban::query()
                    ->where('ban_user_id', $get_user_of_posted)
                    ->orWhere('ban_user_id', $this->user->id)
                    ->where('user_id', $this->user->id)
                    ->orWhere('user_id', $get_user_of_posted)
                    ->first();
        if ($user_banned && $get_user_of_posted != $this->user->id) {
            // send response for banned user
            if (Gate::allows('check-user-banned', $user_banned)) {
                return $this->sendError('unauthorized cause banned .');
            }
        }
        if ($request->has('parent_id')) {
            // get information for send notification
            $comment = Comment::find($request->parent_id);
            $owner_comment =  User::find($comment->user_id);
            $user_reply = User::find($this->user->id);
            // create a comment
            $reply = Comment::create($input);
            // send notification
            $owner_comment->notify(new ReplyCommented($owner_comment, $user_reply, $comment, $reply));
        } else {
            // get information for send notification
            $post = Post::find($request->post_id);
            $owner_post =  User::find($post->user_id);
            $user_commented = User::find($this->user->id);
            // create a comment
            $comment = Comment::create($input);
            // send notification
            $owner_post->notify(new UserCommented($owner_post, $user_commented, $post, $comment));
        }



        return $this->sendResponse([], 'Comment created successfully.');
    }

    public function postComments(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
            'post_id' => 'required|exists:posts,id',
        ],
            [
                'post_id.required' => 'The :attribute field is required.',
                'post_id.exists' => 'The :attribute is not exists.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $comments = Comment::where('post_id', $request->post_id)
        ->where('parent_id', null)
        ->with('replies')->paginate(10);
        CommentResource::collection($comments);
        return $this->sendResponse($comments, 'Post comments retrieved successfully.');
        //return $this->sendPostComments($comments, 'Post all comments');
    }
}
