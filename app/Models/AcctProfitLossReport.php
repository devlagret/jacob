<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctProfitLossReport extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_profit_loss_report'; 
    protected $primaryKey   = 'profit_loss_report_id';
    
    protected $guarded = [
        'profit_loss_report_id',
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
