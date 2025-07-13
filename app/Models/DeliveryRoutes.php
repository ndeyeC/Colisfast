<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryRoute extends Model
{
    // protected $fillable = [
    //     'commande_id',
    //     'driver_id',
    //     'start_point',
    //     'end_point',
    //     'current_position',
    //     'polyline',
    //     'steps',
    //     'distance_km',
    //     'duration_minutes',
    //     'started_at',
    //     'completed_at',
    //     'final_position'
    // ];

    // protected $casts = [
    //     'start_point' => 'array',
    //     'end_point' => 'array',
    //     'current_position' => 'array',
    //     'final_position' => 'array',
    //     'polyline' => 'array',
    //     'steps' => 'array',
    //     'started_at' => 'datetime',
    //     'completed_at' => 'datetime'
    // ];

    // public function commande()
    // {
    //     return $this->belongsTo(Commnande::class, 'commande_id');
    // }
}