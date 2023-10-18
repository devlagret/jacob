<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctDepositoAccountExtra extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_deposito_account_extra'; 
    protected $primaryKey   = 'deposito_account_extra_id';
    
    protected $guarded = [
        'deposito_account_extra_id',
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
