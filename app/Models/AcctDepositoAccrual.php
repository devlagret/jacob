<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctDepositoAccrual extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_deposito_accrual'; 
    protected $primaryKey   = 'deposito_accrual_id';
    
    protected $guarded = [
        'deposito_accrual_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
