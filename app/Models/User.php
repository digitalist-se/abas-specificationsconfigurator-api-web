<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Lang;
use Laravel\Passport\HasApiTokens;
use App\Notifications\ResetPassword as ResetPasswordNotification;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    const REQUIRED_FIELDS_FOR_SPECIFICATION = [
        'name',
        'email',
        'sex',
        'contact',
        'company_name',
        'phone',
        'website',
        'street',
        'zipcode',
        'city',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',

        'sex',
        'company_name',
        'phone',
        'website',
        'street',
        'additional_street_info',
        'zipcode',
        'city',
        'contact',
        'contact_function',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'role' => 'int',
    ];

    /**
     * Send the password reset notification.
     *
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($this, $token));
    }

    public function getRoleAttribute()
    {
        return Role::byValue((int) $this->attributes['role']);
    }

    public function setRoleAttribute($role)
    {
        if (is_int($role)) {
            $this->attributes['role'] = $role;

            return;
        }
        if ($role instanceof Role) {
            $this->attributes['role'] = $role->getValue();
        }
    }

    public function answers()
    {
        return $this->hasMany('App\Models\Answer');
    }

    public function hasAllRequiredFieldsForSpecificationDocument()
    {
        foreach (self::REQUIRED_FIELDS_FOR_SPECIFICATION as $requiredField) {
            if (empty($this->$requiredField)) {
                return false;
            }
        }

        return true;
    }

    public function getSalutationAttribute()
    {
        if ($this->sex) {
            return Lang::get('email.salutation.'.$this->sex);
        } else {
            return '';
        }
    }
}
