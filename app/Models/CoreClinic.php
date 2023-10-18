<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreClinic extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_clinic'; 
    protected $primaryKey   = 'clinic_id';
    
    protected $guarded = [
        'clinic_id',
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
