<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreDusun extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_dusun'; 
    protected $primaryKey   = 'dusun_id';
    
    protected $guarded = [
        'dusun_id',
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
    public function kelurahan() {
        return $this->belongsTo(CoreKelurahan::class,'kelurahan_id','kelurahan_id');
    }
}
