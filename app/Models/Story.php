<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Story extends Model
{
    use HasFactory;

    /**
      * The attributes that are mass assignable.
      *
      * @var string[]
      */
    protected $fillable = [
        'user_id',
        'media',
        'description',
        'status',
        'created_at'
    ];

    //-------------------------------- relationship --------------------------------------

    /**
     * getStoryMedia
     *
     * @return HasMany
     */
    public function getStoryMedia(): HasMany
    {
        return $this->hasMany(MediaStory::class, 'story_media_id', 'media');
    }
}
