<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemChangeLog extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'system_change_log'; 
    protected $primaryKey   = 'change_log_id';
    
    protected $guarded = [
        'change_log_id',
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
