<?php

namespace App\Http\Controllers;

use App\Mail\LeadRegisterMail;
use App\Models\Role;
use App\Models\User;
use App\Notifications\Register;
use Illuminate\Http\Request;
use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    const CREATE_FIELDS = [
        'name'                   => 'name',
        'email'                  => 'email',
        'sex'                    => 'sex',
        'company_name'           => 'company_name',
        'phone'                  => 'phone',
        'website'                => 'website',
        'street'                 => 'street',
        'additional_street_info' => 'additional_street_info',
        'zipcode'                => 'zipcode',
        'city'                   => 'city',
        'contact'                => 'contact',
        'contact_function'       => 'contact_function',
        'partner_tracking'       => 'partner_tracking',
    ];
    const UPDATE_FIELDS = self::CREATE_FIELDS;

    public function get(Request $request)
    {
        return UserResource::make($request->user());
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
        $data = [];
        $this->mapToInputData($request, self::UPDATE_FIELDS, $data);
        $data['password'] = Hash::make($request->input('password'));
        $data['role']     = Role::USER;
        $newUser          = User::create($data);

        $newUser->notify(new Register($newUser));
        Mail::to(config('mail.recipient.lead.address'))
            ->sendNow(new LeadRegisterMail($newUser));

        return response('', 204);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $this->validate($request, [
            'email'    => [
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
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
