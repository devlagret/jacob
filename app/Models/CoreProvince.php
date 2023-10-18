<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreProvince extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_province'; 
    protected $primaryKey   = 'province_id';
    
    protected $guarded = [
        'province_id',
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
