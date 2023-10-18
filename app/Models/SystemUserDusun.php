<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemUserDusun extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'system_user_dusun'; 
    protected $primaryKey   = 'user_dusun_id';
    
    protected $guarded = [
        'user_dusun_id',
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
