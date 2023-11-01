<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;


class RequestLog extends Model
{
    use HasFactory;
    use MassPrunable;
    protected $table        = 'request_log'; 
    protected $primaryKey   = 'id';
    
    protected $guarded = [
        'created_at',
        'updated_at',
    ];
    public function prunable()
    {
        return static::where('created_at', '<=', now()->subWeek());
    }
}
