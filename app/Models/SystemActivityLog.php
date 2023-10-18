<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemActivityLog extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'system_activity_log'; 
    protected $primaryKey   = 'user_log_id';
    
    protected $guarded = [
        'user_log_id',
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
