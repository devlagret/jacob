<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreMemberWorking extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_member_working'; 
    protected $primaryKey   = 'member_working_id';
    
    protected $guarded = [
        'member_working_id',
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
