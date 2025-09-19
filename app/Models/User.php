<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
    protected $fillable = [
        'name',
        'email',
        'role',
        'avatar',
        'highest_score',
        'token',
        'password',
    ];

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = strtoupper(bin2hex(random_bytes(4)));
            }
        });
    }

    // 🔹 Rounds where this user is the first player
    public function firstRounds()
    {
        return $this->hasMany(GameRound::class, 'first_player');
    }

    // 🔹 Rounds where this user is the second player
    public function secondRounds()
    {
        return $this->hasMany(GameRound::class, 'second_player');
    }

    // 🔹 If you want to fetch all rounds (as first OR second player)
    public function gameRounds()
    {
        return $this->hasMany(GameRound::class, 'first_player')
                    ->orWhere('second_player', $this->id);
    }
}
