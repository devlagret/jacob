<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctSavingsIndex extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_savings_index'; 
    protected $primaryKey   = 'savings_index_id';
    
    protected $guarded = [
        'savings_index_id',
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
