<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctDeposito extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_deposito'; 
    protected $primaryKey   = 'deposito_id';
    
    protected $guarded = [
        'deposito_id',
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
