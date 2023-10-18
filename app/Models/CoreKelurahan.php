<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreKelurahan extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_kelurahan'; 
    protected $primaryKey   = 'kelurahan_id';
    
    protected $guarded = [
        'kelurahan_id',
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
    public function kecamatan() {
        return $this->belongsTo(CoreKecamatan::class,'kecamatan_id','kecamatan_id');
    }
}
