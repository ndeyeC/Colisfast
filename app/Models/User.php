<?php

 namespace App\Models;

  use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
 use Illuminate\Foundation\Auth\User as Authenticatable;
 use Illuminate\Notifications\Notifiable;

 class User extends Authenticatable
 {
    /** @use HasFactory<\Database\Factories\UserFactory> */
     use HasFactory, Notifiable;

     /**
     * The attributes that are mass assignable.
      *
      * @var list<string>
      */

 protected $table = 'users';
protected $primaryKey = 'user_id';
public $incrementing = true;
protected $keyType = 'int';

protected $fillable = [
        'name',
         'email',
        'password',
        'numero_telephone',
        'adress',
        'role',
        'fcm_token'
     ];
     

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'user_id');
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'user_id');
    }

    public function historiqueLivraisons()
    {
        return $this->hasMany(HistoriqueLivraison::class, 'user_id');
    }

    public function tokens() {
        return $this->hasMany(TokenTransaction::class, 'user_id', 'user_id');
    }
    
    
    public function getTokenBalanceAttribute() {
        return $this->tokens()->sum('amount');
    }

    public function livreur()
    {
        return $this->hasOne(Livreur::class, 'user_id');
    }

    public function sentCommunications()
    {
        return $this->morphMany(Communication::class, 'sender');
    }

    public function livraisons()
{
    return $this->hasMany(Commnande::class, 'driver_id', 'user_id');
}

    
    public function receivedCommunications()
    {
        return $this->morphMany(Communication::class, 'receiver');
    }
    
    public function communications()
    {
        return $this->sentCommunications()->union($this->receivedCommunications());
    }

    public function getValidTokensForZone($zoneId)
    {
        return $this->tokens()
            ->where('status', TokenTransaction::STATUS_COMPLETED)
            ->where('delivery_zone_id', $zoneId)
            ->where(function($query) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>', now());
            })
            ->sum('amount');
    }
    

    public function hasEnoughTokensForDelivery($zoneId, $tokensNeeded = 1)
    {
        return $this->getValidTokensForZone($zoneId) >= $tokensNeeded;
    }
    
    
    public function debitTokensForDelivery($zoneId, $tokensNeeded = 1, $notes = null)
    {
    
        if (!$this->hasEnoughTokensForDelivery($zoneId, $tokensNeeded)) {
            throw new \Exception("Pas assez de jetons disponibles pour cette zone");
        }
        
        
        return $this->tokens()->create([
            'amount' => -$tokensNeeded,
            'delivery_zone_id' => $zoneId,
            'status' => TokenTransaction::STATUS_COMPLETED,
            'reference' => 'USE-'.uniqid(),
            'notes' => $notes ?: 'Utilisation pour livraison'
        ]);
    }

     /**
      * The attributes that should be hidden for serialization.
      *
      * @var list<string>
      */
    protected $hidden = [
         'password',
         'remember_token',
     ];

    /**
     * Get the attributes that should be cast.
      *
     * @return array<string, string>
     */
    protected function casts(): array
     {
         return [
             'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

   
 }
