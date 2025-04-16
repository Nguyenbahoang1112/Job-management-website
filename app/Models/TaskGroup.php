<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskGroup extends Model
{
    use HasFactory;

    public const CREATED_BY_ADMIN = 1;
    public const CREATED_BY_USER = 0;

    public static array $createdByLabels = [
        self::CREATED_BY_ADMIN => 'Được tạo bởi admin',
        self::CREATED_BY_USER => 'Được tạo bởi user'
    ];



    protected $fillable = ['name','is_admin_created','user_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function getCreatedByLabel(){
        return self::$createdByLabels[$this->is_admin_created];
    }
}
