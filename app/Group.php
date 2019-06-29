<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'user_id', 'subject', 'alias_url',
    ];
}
