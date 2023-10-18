<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreferenceIncome extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'preference_income'; 
    protected $primaryKey   = 'income_id';
    
    protected $guarded = [
        'income_id',
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
    public function account() {
        return $this->belongsTo(AcctAccount::class,'account_id','account_id');
    }
}
