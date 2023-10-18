<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctRecalculateLog extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_recalculate_log'; 
    protected $primaryKey   = 'recalculate_log_id';
    
    protected $guarded = [
        'recalculate_log_id',
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
