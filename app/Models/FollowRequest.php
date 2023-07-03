<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowRequest extends Model
{
    use HasFactory;

    public $table ='follows_requests';

    // array of fillable
    protected $fillable = ['id','user_id', 'user_follow_id', 'is_accepted', 'has_request_follow','accepted_at','created_at'];


    //---------------------------------------- constants  --------------------------------------
    public const PAGINATE = 10;

    // --------------------------------------- scopes ------------------------------------------

    // ------------------------------- mutators and accessors -----------------------------------

    // --------------------------------------- relations ----------------------------------------


    /**
     * user
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
