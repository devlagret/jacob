<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctSavingsAccountDetailTemp extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_savings_account_detail_temp'; 
    protected $primaryKey   = 'savings_account_detail_temp_id';
    
    protected $guarded = [
        'savings_account_detail_temp_id',
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
