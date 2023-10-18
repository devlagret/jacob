<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctZakatFund extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_zakat_fund'; 
    protected $primaryKey   = 'zakat_fund_id';
    
    protected $guarded = [
        'zakat_fund_id',
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
