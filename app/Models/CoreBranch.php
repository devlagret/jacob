<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreBranch extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_branch'; 
    protected $primaryKey   = 'branch_id';
    
    protected $guarded = [
        'branch_id',
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
