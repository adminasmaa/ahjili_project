<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostImage extends Model
{
    use HasFactory;
    use HasApiTokens;


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'type',
        'path',
        'path_thumbnail'
    ];


    // -------------------------------------- mutators & accessor ----------------------------------

    /**
     * Path
     *
     * @return Attribute
     */
    protected function Path(): Attribute
    {
        return new Attribute(
            get: fn ($value) =>  $value ?? "",
        );
    }
    /**
     * PathThumbnail
     *
     * @return Attribute
     */
    protected function PathThumbnail(): Attribute
    {
        return new Attribute(
            get: fn ($value) =>  $value ?? "",
        );
    }
    //---------------------------------- relationship ---------------------------------------------
    /**
     * post
     *
     * @return BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
