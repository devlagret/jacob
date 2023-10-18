<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctSourceFund extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_source_fund'; 
    protected $primaryKey   = 'source_fund_id';
    
    protected $guarded = [
        'source_fund_id',
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
