<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreKecamatan extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_kecamatan'; 
    protected $primaryKey   = 'kecamatan_id';
    
    protected $guarded = [
        'kecamatan_id',
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
    public function kabupaten() {
        return $this->belongsTo(CoreCity::class,'city_id','city_id');
    }
}
