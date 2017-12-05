<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IntraDayQuote extends Model
{
    /**
     * Attributes that can be mass filled
     *
     * @var array
     */
    protected $fillable = [
        'symbol',
        'unknown_value1',
        'date_stamp',
        'time_stamp',
        'datetime_quote',
        'open',
        'high',
        'low',
        'close',
        'volumes',
        'unknown_value2'
    ];

    /**
     * The attributes that should be mutated to dates
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'datetime_quote',
    ];
}
