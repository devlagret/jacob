<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctCreditsAgunan extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_credits_agunan'; 
    protected $primaryKey   = 'credits_agunan_id';
    
    protected $guarded = [
        'credits_agunan_id',
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
