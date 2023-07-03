<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Post;
use App\Http\Controllers\API\BaseController as BaseController;
use Maize\Markable\Models\Bookmark;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user=auth()->user();
        
        if(!is_null($this->mention_users)){
            $mention_ids=explode(',',$this->mention_users);
            $mentionusers=User::whereIn('id',$mention_ids)->get();
            (new BaseController)->transformgetUsers($mentionusers);
        }else{ $mentionusers=[]; }
        
        $post=Post::find($this->id);
        $is_bookmarked_by_user=Bookmark::has($post, $user);
        $bookmark_count=Bookmark::count($post);

        return [
            'id' => $this->id,
            'user_name' =>$this->user->username,
            'is_liked_by_user' => $this->isLikedBy($user),
            'is_bookmarked_by_user' => $is_bookmarked_by_user,
            'profile_image' => $this->user->profile_image ? Storage::disk('public')->url($this->user->profile_image) : url('/')."/images/ahjili.png",
            'user_id' => $this->user_id,
            'title' => $this->title ?? "",
            'post_type' => $this->post_type,
            'anonymous_status' => $this->anonymous_status==1?true:false,
            'active_status' => $this->active_status==1?true:false,
            'latitude' => $this->latitude ?? "",
            'longitude' => $this->longitude ?? "",
            'created_at' => $this->created_at->diffForHumans(),
            'post_files' => $this->getPostImages ?? [],
            'tags' => $this->tags->pluck('name')->toArray() ?? [],
            'mention_users' => $mentionusers ?? [],
            'comments' => $this->comments ?? [],
            'liked_users' => $this->likers ?? [],
            'pos_likes_count' => $this->likers->count() ?? 0,
            'comments_count' => $this->comments->count() ?? 0,
            'bookmark_count' => $bookmark_count ?? 0,
        ];
    }
}
