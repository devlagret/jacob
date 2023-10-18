<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemPeriodLog extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'system_period_log'; 
    protected $primaryKey   = 'period_log_id';
    
    protected $guarded = [
        'period_log_id',
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
