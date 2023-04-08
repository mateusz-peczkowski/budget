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
        'user_id',
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

    //

    /**
     * The user that income type belongs to.
     */
    public function user()
    {
        return $this->belongsTo('\App\Models\User');
    }
}
