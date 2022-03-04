<?php

namespace App\Http\Controllers;

use App\Http\Resources\User as UserResource;
use App\Mail\LeadRegisterMail;
use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use App\Notifications\Register;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UserController extends Controller
{
    const CREATE_FIELDS = [
        'first_name'             => 'first_name',
        'last_name'              => 'last_name',
        'email'                  => 'email',
        'sex'                    => 'sex',
        'company_name'           => 'company_name',
        'phone'                  => 'phone',
        'website'                => 'website',
        'street'                 => 'street',
        'additional_street_info' => 'additional_street_info',
        'zipcode'                => 'zipcode',
        'city'                   => 'city',
        'country'                => 'country',
        'contact_first_name'     => 'contact_first_name',
        'contact_last_name'      => 'contact_last_name',
        'contact_function'       => 'contact_function',
        'partner_tracking'       => 'partner_tracking',
        'company'                => 'user_company',
        'role'                   => 'user_role',
        'url'                    => 'user_url',
    ];

    const UPDATE_FIELDS = self::CREATE_FIELDS;

    public function get(Request $request)
    {
        return UserResource::make($request->user());
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|unique:users|checkdomains',
            'password' => 'required|confirmed|min:6',
            'country'  => [
                new Enum(Country::class),
            ],
        ]);
        $data = [];
        $this->mapToInputData($request, self::UPDATE_FIELDS, $data);
        $data['password'] = Hash::make($request->input('password'));
        $data['role'] = Role::USER;
        $newUser = User::create($data);

        event(new Registered($newUser));

        return response('', 204);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $this->validate($request, [
            'email' => [
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
                'checkdomains',
            ],
            'country' => [
                new Enum(Country::class),
            ],
        ]);
        if ($request->input('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        $this->mapToInputData($request, self::UPDATE_FIELDS, $user);
        $user->saveOrFail();

        return response('', 204);
    }
}
