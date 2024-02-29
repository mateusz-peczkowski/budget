<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class TaxSettlement extends Model
{
    use Actionable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'tax_settlement_type_id',
        'submit_date',
        'file',
    ];

    protected $hidden = [
        'id',
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    protected $casts = [
        'user_id'                  => 'integer',
        'tax_settlement_type_id'   => 'integer',
        'submit_date'              => 'date',
    ];

    //

    /**
     * The tax settlement belongs to tax settlement type.
     */
    public function taxSettlementType()
    {
        return $this->belongsTo('\App\Models\TaxSettlementType');
    }

    /**
     * The tax settlement belongs to user.
     */
    public function user()
    {
        return $this->belongsTo('\App\Models\User');
    }
}
