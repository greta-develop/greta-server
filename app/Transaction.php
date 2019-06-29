<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'group_id', 'transaction_date', 'handling', 'balance', 'transaction_amount', 'deposit_amount',
    ];
}
