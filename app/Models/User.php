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
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_number',
        'role',
        'active',
        'verification_code',
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
        'active' => 'boolean',
    ];

    public function assignmentsAsSupervisor()
    {
        return $this->hasMany(Assignment::class, 'supervisor_id');
    }

    public function assignmentsAsTechnician()
    {
        return $this->hasMany(Assignment::class, 'technician_id');
    }

    public function auditAsTechnician()
    {
        return $this->hasMany(Audit::class, 'technician_id');
    }

    public function auditAsSupervisor()
    {
        return $this->hasMany(Audit::class, 'supervisor_id');
    }

    /** Scopes **/

    public function scopeSupervisors($q)
    {
        return $q->where('role', 'supervisor');
    }
    public function scopeTechnicians($q)
    {
        return $q->where('role', 'technician');
    }
    public function scopeActive($q)
    {
        return $q->where('active', true);
    }
}
