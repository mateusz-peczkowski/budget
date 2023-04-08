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
}
