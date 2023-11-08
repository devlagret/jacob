<?php

namespace App\Models;

use App\Scopes\NotDeletedScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoreMember extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_member'; 
    protected $primaryKey   = 'member_id';
    
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

    public function savingDetail(){
        return $this->hasMany(AcctSavingsMemberDetail::class,'member_id','member_id');
    }
    public function branch() {
        return $this->belongsTo(CoreBranch::class,'branch_id','branch_id');
    }
    public function city() {
        return $this->belongsTo(CoreCity::class,'city_id','city_id');
    }
    public function kecamatan() {
        return $this->belongsTo(CoreKecamatan::class,'kecamatan_id','kecamatan_id');
    }
    public function province() {
        return $this->belongsTo(CoreProvince::class,'province_id','province_id');
    }
    public function savingacc() {
        return $this->hasMany(AcctSavingsAccount::class,'member_id','member_id');
    }
    public function creditacc() {
        return $this->hasMany(AcctCreditsAccount::class,'member_id','member_id');
    }
    public function depositoacc() {
        return $this->hasMany(AcctDepositoAccount::class,'member_id','member_id');
    }
    public function working() {
        return $this->hasOne(CoreMemberWorking::class,'member_id','member_id');
    }
    protected static function booted()
    {
        static::addGlobalScope(new NotDeletedScope);
    }
}
