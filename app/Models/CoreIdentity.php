<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreIdentity extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_identity'; 
    protected $primaryKey   = 'identity_id';
    
    protected $guarded = [
        'identity_id',
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
