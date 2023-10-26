<?php

namespace App\Models;

use App\Scopes\NotDeletedScope;
use Illuminate\Database\Eloquent\Model;

class CoreOffice extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_office'; 
    protected $primaryKey   = 'office_id';
    
    protected $guarded = [
        'office_id',
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
    protected static function booted()
    {
        static::addGlobalScope(new NotDeletedScope);
    }
    public function branch() {
        return $this->belongsTo(CoreBranch::class,'branch_id','branch_id');
    }
}
