<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

class Expense extends Model
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
        'status',
        'expense_type_id',
        'period_id',
        'value',
    ];

    protected $hidden = [
        'id',
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    protected $casts = [
        'date'            => 'date',
        'expense_type_id' => 'integer',
        'period_id'       => 'integer',
        'value'           => 'double',
    ];

    //

    /**
     * The expense type that expense belongs to.
     */
    public function expenseType()
    {
        return $this->belongsTo('\App\Models\ExpenseType')->withTrashed();
    }

    /**
     * The period that expense belongs to.
     */
    public function period()
    {
        return $this->belongsTo('\App\Models\Period');
    }
}
