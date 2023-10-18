<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreCity extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_city'; 
    protected $primaryKey   = 'city_id';
    
    protected $guarded = [
        'city_id',
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
