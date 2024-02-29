<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class TaxSettlementType extends Model
{
    use Actionable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'issuer',
    ];

    protected $hidden = [
        'id',
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    //

    /**
     * The tax settlement type has many tax settlements.
     */
    public function taxSettlements()
    {
        return $this->hasMany('\App\Models\TaxSettlement');
    }
}
