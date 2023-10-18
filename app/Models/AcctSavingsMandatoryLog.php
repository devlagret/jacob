<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctSavingsMandatoryLog extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_savings_mandatory_log'; 
    protected $primaryKey   = 'savings_mandatory_log_id';
    
    protected $guarded = [
        'savings_mandatory_log_id',
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
