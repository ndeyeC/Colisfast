<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    public function tokenTransaction() {
        return $this->belongsTo(TokenTransaction::class);
    }
}
