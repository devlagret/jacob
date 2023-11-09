<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctCreditsPaymentSuspend extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_credits_payment_suspend'; 
    protected $primaryKey   = 'credits_payment_suspend_id';
    
    protected $guarded = [
        'credits_payment_suspend_id',
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
    public function credit() {
        return $this->belongsTo(AcctCredits::class,'credits_id','credits_id');
    }
    public function account() {
        return $this->belongsTo(AcctCreditsAccount::class,'credits_account_id','credits_account_id');
    }
}
