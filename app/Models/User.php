<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'username',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'activation_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }



    public function scopeFilterByEmail($q, $email = null, $s = 0) {
        if (!$email) {
            return $q;
        }

        return ($s) ? $q->where('email', '=', $email) : $q->where('email', 'like', '%' . $email . '%');
    }

    public function scopeFilterByUsername($q, $username = null, $s = 0) {
        if (!$username) {
            return $q;
        }

        return ($s) ? $q->where('username', '=', $username) : $q->where('username', 'like', '%' . $username . '%');
    }

    public function scopeFilterByRoleId($q, $role_id = null) {
        if (!$role_id) {
            return $q;
        }

        return $q->whereHas('roles', function ($q) use ($role_id) {
            $q->where('role_id', '=', $role_id);
        });
    }

    public function scopeFilterByStatus($q, $status = null) {
        if (!$status) {
            return $q;
        }

        return $q->where('status', '=', $status);
    }

    public function scopeCreatedAtDateBetween($q, $dates) {
        if ((!$dates['start_date'] || !$dates['end_date']) && $dates['start_date'] <= $dates['end_date']) {
            return $q;
        }

        return $q->where('created_at', '>=', getStartOfDate($dates['start_date']))->where('created_at', '<=', getEndOfDate($dates['end_date']));
    }
}
