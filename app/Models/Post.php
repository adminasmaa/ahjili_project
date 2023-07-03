<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Overtrue\LaravelLike\Traits\Likeable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Models\PostImage;
use Spatie\Tags\HasTags;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

;
use Maize\Markable\Markable;
use Maize\Markable\Models\Bookmark;

class Post extends Model
{
    use HasFactory;
    use HasApiTokens;
    use Likeable;
    use HasTags ;
    use Markable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'title',
        'post_type',
        'anonymous_status',
        'active_status',
        'mention_users',
        'latitude',
        'longitude',
        'post_tags',
    ];

    protected static $marks = [
        Bookmark::class,
    ];

    // ------------------------------------ contants ----------------------------------------
    // paginate
    public const PAGINATE = 10;

    // status post
    private const ACTIVE = true;
    private const DISACTIVE = false;
    // status anonymous
    private const ACTIVE_ANONYMOUS = true;
    private const DISACTIVE_ANONYMOUS = false;

    // type post
    public const IMAGE  = 'image';
    public const TEXT   = 'text';
    public const GIF    = 'gif';
    public const AUDIO  = 'audio';
    public const VIDEO  = 'video';


    //-------------------------------------- scopes -----------------------------------------

    /**
     * scopeActive
     *
     * @param  mixed $q
     * @return void
     */
    public function scopeActive($query)
    {
        return $query->where('active_status', self::ACTIVE);
    }

    /**
     * scopeDisactive
     *
     * @param  mixed $query
     * @return void
     */
    public function scopeDisactive($query)
    {
        return $query->where('active_status', self::DISACTIVE);
    }

    /**
     * scopeActiveAnonymous
     *
     * @param  mixed $query
     * @return void
     */
    public function scopeActiveAnonymous($query)
    {
        return $query->where('anonymous_status', self::ACTIVE_ANONYMOUS);
    }

    /**
     * scopeDisactiveAnonymous
     *
     * @param  mixed $query
     * @return void
     */
    public function scopeDisactiveAnonymous($query)
    {
        return $query->where('anonymous_status', self::DISACTIVE_ANONYMOUS);
    }



    //---------------------------------- Relationship ---------------------------------------

    /**
     * getPostImages
     *
     * @return HasMany
     */
    public function getPostImages(): HasMany
    {
        return $this->hasMany(PostImage::class)->select(['post_id','path','path_thumbnail']);
    }

    /**
     * comments
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    /**
     * likes
     *
     * @return MorphMany
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }


    /**
     * user
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * reportAbusePost
     *
     * @return HasMany
     */
    public function reportAbusePost(): HasMany
    {
        return $this->hasMany(ReportAbusePost::class);
    }
}
