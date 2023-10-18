<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreferenceCollectibility extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'preference_collectibility'; 
    protected $primaryKey   = 'collectibility_id';
    
    protected $guarded = [
        'collectibility_id',
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
