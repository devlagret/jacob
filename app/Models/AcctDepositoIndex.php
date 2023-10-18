<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctDepositoIndex extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_deposito_index'; 
    protected $primaryKey   = 'deposito_index_id';
    
    protected $guarded = [
        'deposito_index_id',
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
