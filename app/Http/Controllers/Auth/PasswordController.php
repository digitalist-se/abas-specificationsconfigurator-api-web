<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    public function updatePassword(Request $request)
    {
        $user = $request->user();
        Validator::extend('password_old', function ($attribute, $value, $parameters, $validator) {
            return Hash::check($value, current($parameters));
        });
        $this->validate($request, [
            'password'     => 'required|confirmed|min:8',
            'password_old' => 'required|password_old:'.$user->password,
        ]);
        if ($request->input('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        $user->saveOrFail();

        return response('', 204);
    }
}
