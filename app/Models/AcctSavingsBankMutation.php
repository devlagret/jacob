<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctSavingsBankMutation extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_savings_bank_mutation'; 
    protected $primaryKey   = 'savings_bank_mutation_id';
    
    protected $guarded = [
        'savings_bank_mutation_id',
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
