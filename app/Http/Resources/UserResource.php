<?php

namespace App\Http\Resources;

use App\Models\Ban;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if (auth()->user()) {
            $has_followed=$this->isFollowedBy(auth()->user());
        } else {
            $has_followed=false;
        }
        $user = auth()->user()->id ?? 0;
        // get user banned
        $user_banned = Ban::query()->where('ban_user_id', $this->id)
                                   ->where('user_id', $user)
                                   ->exists();
        return [
            'id' => $this->id,
            'full_name' => ucfirst($this->full_name) ?? "",
            'username' => strtolower($this->username) ?? "",
            'country_code' => $this->country_code ?? "",
            'phone_number' => $this->phone_number ?? "",
            'email' => $this->email,
            'dob' => $this->dob ?? "",
            'gender' => $this->gender ?? "",
            'website' => $this->website ?? "",
            'description' => $this->description ?? "",
            'profile_image' => $this->profile_image ? Storage::disk('public')->url($this->profile_image) : url('/')."/images/ahjili.png",
            'verified' => $this->verified ?? false,
            'active_status' => $this->verified ?? true,
            'fcmtoken' => $this->fcmtoken ?? "",
            'account_type' => $this->account_type ?? "",
            'followers_count' => $this->followers->count() ?? 0,
            'following_count'  => $this->followings->count() ?? 0,
            'total_post_count' => $this->posts->count() ?? 0,
            'reels_count'   => $this->posts->where('post_type', 'video')->count() ?? 0,
            'image_gif_posts_count'=> $this->posts->whereIn('post_type', ['image','gif'])->count() ?? 0,
            'text_posts_count'=> $this->posts->where('post_type', 'text')->count() ?? 0,
            'audio_posts_count'=> $this->posts->where('post_type', 'audio')->count() ?? 0,
            'has_followed' => $has_followed ?? false,
            'blocked' => $user_banned ,
            'notifications_unreaded' => $this->unreadNotifications->count(),
            'is_deleted' => $this->deleted_at ? true : false,
            'created_at' => $this->created_at->format('d-m-Y'),
            'updated_at' => $this->updated_at->format('d-m-Y'),
        ];
    }
}
