<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class DgAgreement extends Model
{
    use Actionable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date',
        'title',
        'company',
        'document',
    ];

    protected $hidden = [
        'id',
        'updated_at',
        'created_at',
    ];

    protected $casts = [
        'date'      => 'date',
    ];
}
