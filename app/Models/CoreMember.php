<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreMember extends Model
{

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
}
