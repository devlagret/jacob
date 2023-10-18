<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreAccountOfficer extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_account_officer'; 
    protected $primaryKey   = 'id';
    
    protected $guarded = [
        'id',
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
