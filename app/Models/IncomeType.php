<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

class IncomeType extends Model
{
    use Actionable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'zus'
    ];

    protected $hidden = [
        'id',
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    protected $casts = [
        'zus' => 'double',
    ];

    //TO-DO PECZIS: Add automatic ZUS expense calculation on create/update from current period
}
