<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctFinancialAnalysis extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_financial_analysis'; 
    protected $primaryKey   = 'financial_analysis_id';
    
    protected $guarded = [
        'financial_analysis_id',
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
