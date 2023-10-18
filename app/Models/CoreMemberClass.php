<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreMemberClass extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_member_class'; 
    protected $primaryKey   = 'member_class_id';
    
    protected $guarded = [
        'member_class_id',
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
