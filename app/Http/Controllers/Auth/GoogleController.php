<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name'      => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'avatar'    => $googleUser->avatar,
                ]
            );

            if (!$user->hasAnyRole()) {
                $user->assignRole('usuario');
            }

            if (!$user->is_active) {
                return redirect('/login')->withErrors(['email' => 'Tu cuenta está desactivada.']);
            }

            Auth::login($user);
            return redirect('/dashboard');

        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Error al autenticar con Google.']);
        }
    }
}