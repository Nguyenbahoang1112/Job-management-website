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


    public const ROLE_ADMIN = 1;
    public const ROLE_USER = 0;

    public const STATUS_ACTIVE = 1;
    public const STATUS_BANNED = 0;

    public static array $roleLabels = [
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_USER => 'Người dùng'
    ];
    
    public static array $statusLables = [
        self::STATUS_ACTIVE => 'Đang hoạt động',
        self::STATUS_BANNED => 'Bị khóa'
    ];

    public function getRoleLabel(){
        return self::$roleLabels[$this->role];
    }

    public function getStatusLabel(){
        return self::$statusLables[$this->status];
    }

    public function notes(){
        return $this->hasMany(Note::class);
    }


    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at'
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
}
