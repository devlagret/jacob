<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctAccountMutation extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_account_mutation'; 
    protected $primaryKey   = 'account_mutation_id';
    
    protected $guarded = [
        'account_mutation_id',
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
