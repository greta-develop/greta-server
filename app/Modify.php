<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Modify extends Model
{
    protected $fillable = [
        'group_id', 'transaction_id', 'user_id', 'subject', 'prev_subject'
    ];
}
