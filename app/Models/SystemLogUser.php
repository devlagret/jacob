<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLogUser extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'system_log_user'; 
    protected $primaryKey   = 'log_user_id';
    
    protected $guarded = [
        'log_user_id',
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
