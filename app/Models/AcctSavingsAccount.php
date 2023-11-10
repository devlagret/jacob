<?php

namespace App\Models;

use App\Scopes\NotDeletedScope;
use Illuminate\Database\Eloquent\Model;

class AcctSavingsAccount extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_savings_account';
    protected $primaryKey   = 'savings_account_id';

    protected $guarded = [
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
    public function savingdata() {
        return $this->belongsTo(AcctSavings::class,'savings_id','savings_id');
    }
    protected static function booted()
    {
        static::addGlobalScope(new NotDeletedScope);
    }
}
