<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemEndOfDays extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'system_end_of_days'; 
    protected $primaryKey   = 'end_of_days_id';
    
    protected $guarded = [
        'end_of_days_id',
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
