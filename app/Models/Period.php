<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'is_closed'
    ];

    protected $hidden = [
        'id',
        'updated_at',
        'created_at',
    ];

    //TO-DO PECZIS: Add activePeriod function from cache
    //TO-DO PECZIS: Add option to close period
}
