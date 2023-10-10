<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Loan extends Model
{
    use Actionable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'expense_repeatable_key',
        'title',
        'status',
        'who',
        'user_id',
        'notes',
        'last_payment',
        'overall_value',
        'paid_value',
        'remaining_value',
        'paid_percent',
        'next_payment_value',
        'remaining_payments_count',
        'remaining_payments_years',
        'date_ending',
    ];

    protected $hidden = [
        'id',
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    protected $casts = [
        'user_id'                  => 'integer',
        'last_payment'             => 'date',
        'overall_value'            => 'double',
        'paid_value'               => 'double',
        'remaining_value'          => 'double',
        'paid_percent'             => 'double',
        'next_payment_value'       => 'double',
        'remaining_payments_count' => 'integer',
        'remaining_payments_years' => 'integer',
        'date_ending'              => 'date',
    ];

    //

    /**
     * The loan has many expenses as payments.
     */
    public function payments()
    {
        return $this->belongsTo('\App\Models\Expense', 'expense_repeatable_key', 'repeatable_key')->withTrashed();
    }

    /**
     * The user that this loan belongs to
     */
    public function user()
    {
        return $this->belongsTo('\App\Models\User');
    }
}
