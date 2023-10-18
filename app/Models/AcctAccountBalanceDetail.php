<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctAccountBalanceDetail extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_account_balance_detail'; 
    protected $primaryKey   = 'account_balance_detail_id';
    
    protected $guarded = [
        'account_balance_detail_id',
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
