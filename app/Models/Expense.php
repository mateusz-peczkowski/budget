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
        'block_mass_update',
        'date',
        'pay_date',
        'status',
        'expense_type_id',
        'period_id',
        'value',
        'file',
        'list_in_files',
    ];

    protected $hidden = [
        'id',
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    protected $casts = [
        'date'              => 'date',
        'pay_date'          => 'date',
        'expense_type_id'   => 'integer',
        'period_id'         => 'integer',
        'value'             => 'double',
        'list_in_files'     => 'boolean',
        'block_mass_update' => 'boolean',
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
     * The loan that expense belongs to.
     */
    public function loan()
    {
        return $this->belongsTo('\App\Models\Loan', 'repeatable_key', 'expense_repeatable_key');
    }

    /**
     * The period that expense belongs to.
     */
    public function period()
    {
        return $this->belongsTo('\App\Models\Period');
    }

    /**
     * The similar expenses
     */
    public function similar()
    {
        if (!$this->attributes['repeatable_key'])
            return $this->where('id', '-1');

        $model = $this
            ->where('id', '!=', $this->attributes['id']);

        return $model
            ->withTrashed()
            ->where('repeatable_key', $this->attributes['repeatable_key']);
    }
}
