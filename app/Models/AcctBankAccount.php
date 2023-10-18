<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctBankAccount extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_bank_account'; 
    protected $primaryKey   = 'bank_account_id';
    
    protected $guarded = [
        'bank_account_id',
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
