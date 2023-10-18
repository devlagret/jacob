<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctSavingsTransferMutationTo extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_savings_transfer_mutation_to'; 
    protected $primaryKey   = 'savings_transfer_mutation_to_id';
    
    protected $guarded = [
        'savings_transfer_mutation_to_id',
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
