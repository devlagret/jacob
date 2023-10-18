<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctCreditsAcquittance extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_credits_acquittance'; 
    protected $primaryKey   = 'credits_acquittance_id';
    
    protected $guarded = [
        'credits_acquittance_id',
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
