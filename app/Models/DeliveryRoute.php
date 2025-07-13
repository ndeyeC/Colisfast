<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryRoute extends Model
{
    protected $fillable = [
        'commnande_id',
        'driver_id',
        'livreur_id',
        'start_point',
        'end_point',
        'current_position',
        'polyline',
        'steps',
        'distance_km',
        'duration_minutes',
        'started_at',
        'completed_at',
        'final_position',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'start_point' => 'array',
        'end_point' => 'array',
        'current_position' => 'array',
        'final_position' => 'array',
        'polyline' => 'array',
        'steps' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function commande()
    {
        return $this->belongsTo(Commnande::class, 'commnande_id');
    }

    public function livreur()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}