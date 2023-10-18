<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctSavingsProfitSharingTemp extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_savings_profit_sharing_temp'; 
    protected $primaryKey   = 'savings_profit_sharing_temp_id';
    
    protected $guarded = [
        'savings_profit_sharing_temp_id',
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
