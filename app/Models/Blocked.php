<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blocked extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_comment',
        'block_message',
        'block_post',
        'user_id'

    ];
}
