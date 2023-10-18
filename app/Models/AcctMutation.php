<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctMutation extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_mutation'; 
    protected $primaryKey   = 'mutation_id';
    
    protected $guarded = [
        'mutation_id',
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
