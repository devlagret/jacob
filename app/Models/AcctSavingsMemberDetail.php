<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctSavingsMemberDetail extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_savings_member_detail'; 
    protected $primaryKey   = 'savings_member_detail_id';
    
    protected $guarded = [
        'savings_member_detail_id',
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
