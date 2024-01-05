<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctSavingsCashMutation extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_savings_cash_mutation'; 
    protected $primaryKey   = 'savings_cash_mutation_id';
    
    protected $guarded = [
        'savings_cash_mutation_id',
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
    public function member() {
        return $this->belongsTo(CoreMember::class,'member_id','member_id');
    }
    public function mutation() {
        return $this->belongsTo(AcctMutation::class,'mutation_id','mutation_id');
    }
    public function savings() {
        return $this->belongsTo(AcctSavings::class,'savings_id','savings_id');
    }
    public function savingsaccount() {
        return $this->belongsTo(AcctSavingsAccount::class,'savings_account_id','savings_account_id');
    }
}
