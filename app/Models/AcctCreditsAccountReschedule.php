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

    public function member() {
        return $this->belongsTo(CoreMember::class,'member_id','member_id');
    }
    public function office() {
        return $this->belongsTo(CoreOffice::class,'office_id','office_id');
    }
    public function branch() {
        return $this->belongsTo(CoreBranch::class,'branch_id','branch_id');
    }
    public function credit() {
        return $this->belongsTo(AcctCredits::class,'credits_id','credits_id');
    }
    public function sourcefund() {
        return $this->belongsTo(AcctSourceFund::class,'source_fund_id','source_fund_id');
    }
    public function anggunan() {
        return $this->hasMany(AcctCreditsAgunan::class,'credits_account_id','credits_account_id');
    }
    public function savingacc() {
        return $this->belongsTo(AcctSavingsAccount::class,'savings_account_id','savings_account_id');
    }
    public function account() {
        return $this->belongsTo(AcctCreditsAccount::class,'credits_account_id','credits_account_id');
    }
}
