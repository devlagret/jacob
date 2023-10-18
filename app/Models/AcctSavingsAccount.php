<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctSavingsAccount extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_savings_account'; 
    protected $primaryKey   = 'savings_account_id';
    
    protected $guarded = [
        'savings_account_id',
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
