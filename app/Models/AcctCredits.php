<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctCredits extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_credits'; 
    protected $primaryKey   = 'credits_id';
    
    protected $guarded = [
        'credits_id',
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
