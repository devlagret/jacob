<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctCreditsPayment extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_credits_payment'; 
    protected $primaryKey   = 'credits_payment_id';
    
    protected $guarded = [
        'credits_payment_id',
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
