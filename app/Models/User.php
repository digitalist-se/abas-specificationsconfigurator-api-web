<?php

namespace App\Models;

use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Lang;
use Laravel\Passport\HasApiTokens;

/**
 * @property \App\Models\Role $role
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use Notifiable;
    use HasFactory;

    const REQUIRED_FIELDS_FOR_SPECIFICATION = [
        'first_name',
        'last_name',
        'email',
        'sex',
        'contact_first_name',
        'contact_last_name',
        'company_name',
        'phone',
        'website',
        'street',
        'zipcode',
        'city',
        'email_verified_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
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
        'country',
        'contact_first_name',
        'contact_last_name',
        'contact_function',
        'partner_tracking',
        'user_company',
        'user_role',
        'user_url',
        'crm_company_id',
        'crm_user_contact_id',
        'crm_company_contact_id',
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
        'role'              => 'int',
        'email_verified_at' => 'datetime',
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
        return $this->hasMany(Answer::class);
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
        }

        return '';
    }

    public function getZipcodeAndCityAttribute()
    {
        return $this->zipcode.', '.$this->city;
    }

    /**
     * country value that should be used inside of lead mails
     */
    public function leadCountry(): Attribute
    {
        return Attribute::get(
            function ($value, $attributes) {
                $country = Country::tryFrom($attributes['country'] ?? Country::Other->value) ?? Country::Other;

                return $country->getLeadName();
            }
        );
    }

    public function getFullStreetAttribute()
    {
        return collect($this->only(['street', 'additional_street_info']))
            ->filter()
            ->join(' ');
    }

    public function name(): Attribute
    {
        return Attribute::make(get: function ($value, $attributes) {
            $name = [];

            if (isset($attributes['first_name'])) {
                $name[] = $attributes['first_name'];
            }

            if (isset($attributes['last_name'])) {
                $name[] = $attributes['last_name'];
            }

            return implode(' ', $name);
        });
    }
}
