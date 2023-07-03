<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BanResource extends JsonResource
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
            'ban_username' => $this->ban_username,
            'ban_full_name' => $this->ban_full_name,
            'ban_user_id' => $this->ban_user_id,
            'profile_image' => $this->profile_image ? Storage::disk('public')->url($this->profile_image) : url('/')."/images/ahjili.png",
            'created_at' => $this->created_at->format('d-m-Y'),
        ];
    }
}
