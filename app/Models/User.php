<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public const ROLE_DEVELOPER              = 'developer';
    public const ROLE_ASSISTANT_COMMISSIONER = 'assistant_commissioner';
    public const ROLE_UNO                    = 'uno';
    public const ROLE_OFFICE_ASSISTANT       = 'office_assistant';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function roleLabels(): array
    {
        return [
            self::ROLE_DEVELOPER              => 'ডেভেলপার (সুপার অ্যাডমিন)',
            self::ROLE_ASSISTANT_COMMISSIONER => 'সহকারী কমিশনার (ভূমি)',
            self::ROLE_UNO                    => 'উপজেলা নির্বাহী অফিসার',
            self::ROLE_OFFICE_ASSISTANT       => 'অফিস সহকারী',
        ];
    }

    public function roleLabel(): string
    {
        return self::roleLabels()[$this->role] ?? $this->role;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        if ($this->role === self::ROLE_DEVELOPER) {
            return true; // সুপার এডমিন সবকিছু করতে পারবে
        }

        return in_array($this->role, $roles, true);
    }
}
