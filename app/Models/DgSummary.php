<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class DgSummary extends Model
{
    use Actionable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date',
        'gross',
        'net',
        'zus',
        'tax',
        'vat',
        'complete_document',
        'zus_document',
        'tax_document',
        'vat_document',
        'documents_archive',
    ];

    protected $hidden = [
        'id',
        'updated_at',
        'created_at',
    ];

    protected $casts = [
        'date'      => 'date',
        'gross'     => 'double',
        'net'       => 'double',
        'zus'       => 'double',
        'tax'       => 'double',
        'vat'       => 'double',
    ];

    public function getGrossVatAttribute($value)
    {
        return round($this->gross - $this->net, 2);
    }

    public function setGrossVatAttribute($value)
    {
        $this->attributes['gross'] = $this->net + $value;
        unset($this->attributes['gross_vat']);
    }
}
