<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctAccountBalance extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_account_balance'; 
    protected $primaryKey   = 'account_balance_id';
    
    protected $guarded = [
        'account_balance_id',
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
