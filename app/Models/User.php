<?php

namespace App\Models;

use App\Notifications\Employee\AccountStatusChangeNotification;
use App\Notifications\SetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'img_path',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function requests()
    {
        return $this->hasMany(AssetRequest::class)->withTrashed();
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class)->withTrashed();
    }

    public function maintenanceReports()
    {
        return $this->hasMany(AssetMaintenance::class, 'reported_by');
    }

    public function logs()
    {
        return $this->hasMany(AssetLog::class);
    }

    // Helpers

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isActive(): bool
    {
        return $this->status === 1;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new SetPasswordNotification($token));
    }

    protected static function booted()
    {
        static::updated(function ($user) {
            if ($user->wasChanged('status')) {
                $user->notify(new AccountStatusChangeNotification($user->status));
            }
        });
    }
}
