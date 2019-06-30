<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdmimReply extends Model
{
    protected $fillable = [
        'reply_id', 'subject',
    ];
}
