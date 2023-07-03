<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CommentRepliesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user->id,
            'full_name' => $this->user->full_name,
            'username' => $this->user->username,
            'profile_image' => $this->user->profile_image ? Storage::disk('public')->url($this->user->profile_image) : url('/')."/images/ahjili.png",
            'body' => $this->body,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
