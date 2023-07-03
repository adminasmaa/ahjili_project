<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ban extends Model
{
    use HasFactory;

    public $table ='bans';

    // array of fillable
    protected $fillable = ['id','user_id', 'ban_username', 'ban_full_name', 'ban_user_id','created_at'];


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
