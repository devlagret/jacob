<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctAccountSetting extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_account_setting'; 
    protected $primaryKey   = 'account_setting_id';
    
    protected $guarded = [
        'account_setting_id',
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
