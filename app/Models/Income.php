<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

class Income extends Model
{
    use Actionable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'sub_name',
        'repeatable_key',
        'date',
        'pay_date',
        'status',
        'income_type_id',
        'period_id',
        'quantity',
        'rate',
        'currency',
        'currency_rate',
        'rate_local_currency',
        'net',
        'gross',
        'tax_percent',
        'tax',
        'vat_percent',
        'vat',
    ];

    protected $hidden = [
        'id',
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    protected $casts = [
        'date'                => 'date',
        'pay_date'            => 'date',
        'income_type_id'      => 'integer',
        'period_id'           => 'integer',
        'quantity'            => 'double',
        'rate'                => 'double',
        'rate_local_currency' => 'double',
        'net'                 => 'double',
        'gross'               => 'double',
        'tax_percent'         => 'double',
        'tax'                 => 'double',
        'vat_percent'         => 'double',
        'vat'                 => 'double',
    ];

    //

    /**
     * The income type that income belongs to.
     */
    public function incomeType()
    {
        return $this->belongsTo('\App\Models\IncomeType')->withTrashed();
    }

    /**
     * The period that income belongs to.
     */
    public function period()
    {
        return $this->belongsTo('\App\Models\Period');
    }
}
