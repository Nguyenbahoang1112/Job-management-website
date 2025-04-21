<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens ,HasFactory, Notifiable;

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

    public static array $statusLabels = [
        self::STATUS_ACTIVE => 'Đang hoạt động',
        self::STATUS_BANNED => 'Bị khóa'
    ];


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

    public function getRoleLabel(){
        return self::$roleLabels[$this->role];
    }

    public function getStatusLabel(){
        return self::$statusLabels[$this->status];
    }


    public function isAdmin(): bool{
        return $this->role === self::ROLE_ADMIN;
    }

    public function isUser(): bool{
        return $this->role === self::ROLE_USER;
    }

    public function notes(){
        return $this->hasMany(Note::class);
    }

    public function searchHistories(){
        return $this->hasMany(SearchHistory::class);
    }

    public function providers(){
        return $this->hasMany(Provider::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_users', 'user_id', 'team_id');
    }


    public function userLogs(){
        return $this->hasMany(UserLog::class);
    }

    public function taskGroups(){
        return $this->hasMany(TaskGroup::class);
    }

    public function tasks(){
        return $this->hasMany(Task::class);
    }

    public function tags(){
        return $this->hasMany(Tag::class);
    }

}
