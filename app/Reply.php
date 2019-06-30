<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    protected $fillable = [
        'transaction_id', 'email', 'subject', 'status',
    ];
}
