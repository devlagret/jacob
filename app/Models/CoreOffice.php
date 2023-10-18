<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreOffice extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_office'; 
    protected $primaryKey   = 'office_id';
    
    protected $guarded = [
        'office_id',
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
