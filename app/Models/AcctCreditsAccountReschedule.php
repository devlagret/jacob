<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctCreditsAccountReschedule extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_credits_account_reschedule'; 
    protected $primaryKey   = 'credits_account_reschedule_id';
    
    protected $guarded = [
        'credits_account_reschedule_id',
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
