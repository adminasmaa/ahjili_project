<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Help extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'reply',
        'user_id',

    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
