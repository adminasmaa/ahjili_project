<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Story;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class AdminController extends Controller
{
    /**
     * redirect admin after login
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {    
        //code for auto story deletion    
        //dd(Carbon::now(),Carbon::parse('-24 hours'));
         
        $userCount = User::count();
        $postCount = Post::count();
        $commentCount = Comment::count();
        $likeCount = Like::count();
        return view('admin.admin-dashboard',compact('userCount','postCount','commentCount','likeCount'));
    }

    public function wdashboard()
    {                          
        $userCount = User::count();
        $postCount = Post::count();
        $commentCount = Comment::count();
        $likeCount = Like::count();
        return view('admin.admin-dashboard',compact('userCount','postCount','commentCount','likeCount'));
    }

    public function getComments(Request $request)
    {
        $comments = Comment::with('user')->paginate(10);
        
        return view('admin/dashboard/allComments',compact('comments'));
        dd($comments);
    }

    public function getLikes(Request $request)
    {
        //$users = User::with('likes')->get();
        $posts = Post::get();
        $likesArray = [];
        if(count($posts) > 0){
            foreach($posts as $post) {
                $likers = $post->likers();
                $data = $likers->get();
                if(count($data) > 0){

                
                $likesArray [] =array(
                    'post_id' => $post->id,
                    'post_body' => $post->body,
                    'username' => $data[0]['username'],
                    'user_id'  =>  $data[0]['id']
                ); 
                }
            }
        }
        return view('admin/dashboard/userLikes',compact('likesArray'));
    }

}
