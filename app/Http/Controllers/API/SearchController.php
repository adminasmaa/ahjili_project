<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\Ban;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Tags\Tag;

class SearchController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if ($request->has('search')) {
            // get users blocked:
            $users_blocked = Ban::query()->where('user_id', Auth::id())
                                         ->orWhere('ban_user_id', Auth::id())
                                         ->pluck('ban_user_id');
            // search a users
            $users = User::query()->select("id", "username", "full_name")->where('username', 'like', "%{$request->search}%")
                                    ->orWhere('full_name', 'like', "%{$request->search}")
                                    ->whereNotIn('id', $users_blocked)
                                    ->paginate(5);
            // search a posts
            $posts = Post::query()->active()->where('title', 'like', "%{$request->search}%")
                                            ->whereHas('user', function ($q) use ($users_blocked) {
                                                $q->whereNotIn('id', $users_blocked);
                                            })

                                            ->paginate(5);
            $tags = Tag::query()->where('name->en', 'like', "%{$request->search}%")
                                                        ->paginate(5);

            // make a data in array
            $data_of_search = [
                'users' => $users,
                'posts' => $posts,
                'tags'  => $tags
            ];

            return $this->sendResponse($data_of_search, 'result of search');
        }
    }
}
