<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClosingQuote extends Model
{
    protected $fillable = [
        'symbol',
        'date_stamp',
        'date_quote',
        'open',
        'high',
        'low',
        'close',
        'volumes'
    ];
}
