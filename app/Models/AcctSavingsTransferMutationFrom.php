<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctSavingsTransferMutationFrom extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_savings_transfer_mutation_from'; 
    protected $primaryKey   = 'savings_transfer_mutation_from_id';
    
    protected $guarded = [
        'savings_transfer_mutation_from_id',
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
