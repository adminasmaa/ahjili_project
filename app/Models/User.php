<?php

namespace App\Models;

//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelLike\Traits\Liker;
use Overtrue\LaravelFollow\Traits\Follower;
use Overtrue\LaravelFollow\Traits\Followable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laratrust\Traits\LaratrustUserTrait;

class User extends Authenticatable
{
    use LaratrustUserTrait;
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use Liker;
    use Follower;
    use Followable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'full_name',
        'username',
        'country_code',
        'phone_number',
        'email',
        'password',
        'dob',
        'gender',
        'website',
        'description',
        'profile_image',
        'verified',
        'active_status',
        'fcmtoken',
        'account_type',
        'avatar',
        'job'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */

    protected $with = ['blocked'];
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // ----------------------------------------- contants ----------------------------------------
    // type account
    public const PUBLIC = 'public';
    public const PRIVATE = 'private';

    // type sosialite
    public const FACEBOOK = 'facebook';
    public const GOOGLE = 'google';

    // ------------------------------------------- relationship ---------------------------------------

    /**
     * socialAccounts
     *
     * @return HasMany
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(socialAccount::class);
    }

    /**
     * posts
     *
     * @return HasMany
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * stories
     *
     * @return HasMany
     */
    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    /**
     * comments
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }


    /**
     * bans
     *
     * @return HasMany
     */
    public function bans(): HasMany
    {
        return $this->hasMany(Ban::class);
    }

    public function blocked(): HasMany
    {
        return $this->hasMany(Blocked::class);
    }

    /**
     * follows_requests
     *
     * @return HasMany
     */
    public function follows_requests(): HasMany
    {
        return $this->hasMany(FollowRequest::class)->where('account_type', self::PRIVATE);
    }
}
