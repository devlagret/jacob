<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreferenceInventory extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'preference_inventory'; 
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
