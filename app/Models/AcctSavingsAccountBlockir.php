<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctSavingsAccountBlockir extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_savings_account_blockir'; 
    protected $primaryKey   = 'savings_account_blockir_id';
    
    protected $guarded = [
        'savings_account_blockir_id',
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
