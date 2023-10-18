<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctSavings extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_savings'; 
    protected $primaryKey   = 'savings_id';
    
    protected $guarded = [
        'savings_id',
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
