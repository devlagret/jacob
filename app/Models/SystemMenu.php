<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemMenu extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'system_menu'; 
    protected $primaryKey   = 'id_menu';
    
    protected $guarded = [
    ];

    protected $casts = [
        'id_menu' => 'string',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
