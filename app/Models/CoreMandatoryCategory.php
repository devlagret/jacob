<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreMandatoryCategory extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_mandatory_catageory'; 
    protected $primaryKey   = 'mandatory_category_id';
    
    protected $guarded = [
        'mandatory_category_id',
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
