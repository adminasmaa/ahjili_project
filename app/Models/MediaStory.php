<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaStory extends Model
{
    use HasFactory;

    // ---------------------------------- constants --------------------------------------
    // type of media story:
    public const VIDEO   = 'video';
    public const GIF     = 'gif';
    public const IMAGE   = 'image';
}
