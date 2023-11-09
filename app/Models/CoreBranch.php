<?php

namespace App\Models;

use App\Scopes\NotDeletedScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CoreBranch extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_branch'; 
    protected $primaryKey   = 'branch_id';
    
    protected $guarded = [
        'branch_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    public function scopeFlt($query) {
        if(Auth::user()->branch_id!==0){
            return $query->where('branch_id',Auth::user()->branch_id);
        }
    }
    protected $hidden = [
    ];
}
