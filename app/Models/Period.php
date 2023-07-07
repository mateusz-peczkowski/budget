<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    protected $hidden = [
        'id',
        'updated_at',
        'created_at',
    ];

    //

    /**
     * The incomes that period has.
     */
    public function incomes()
    {
        return $this->hasMany('\App\Models\Income');
    }

    /**
     * The expenses that period has.
     */
    public function expenses()
    {
        return $this->hasMany('\App\Models\Expense');
    }

    /**
     * Check if period is closed.
     */
    public function getIsClosedAttribute()
    {
        $return = true;

        if ($this->expenses()->where('status', 'pending')->count())
            $return = false;

        if ($this->incomes()->whereIn('status', ['pending', 'processing'])->count())
            $return = false;

        return $return;
    }

    /**
     * Check if period is closed.
     */
    public function getIsClosedDgAttribute()
    {
        $return = true;

        if ($this->expenses()->whereIn('repeatable_key', ['zus', 'tax', 'vat'])->where('status', 'pending')->count())
            $return = false;

        if ($this->incomes()->whereIn('status', ['pending', 'processing'])->count())
            $return = false;

        return $return;
    }
}
