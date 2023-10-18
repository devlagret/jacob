<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreferenceTransactionModule extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'preference_transaction_module'; 
    protected $primaryKey   = 'transaction_module_id';
    
    protected $guarded = [
        'transaction_module_id',
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
