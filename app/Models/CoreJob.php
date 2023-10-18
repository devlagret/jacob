<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreJob extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_job'; 
    protected $primaryKey   = 'job_id';
    
    protected $guarded = [
        'job_id',
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
