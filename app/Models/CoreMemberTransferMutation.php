<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreMemberTransferMutation extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_member_transfer_mutation'; 
    protected $primaryKey   = 'member_transfer_mutation_id';
    
    protected $guarded = [
        'member_transfer_mutation_id',
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
