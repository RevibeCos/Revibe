<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }


    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::where('email', $googleUser->email)->first();
        if (!$user) {
            $user = User::create(['name' => $googleUser->name, 'email' => $googleUser->email, 'password' => Hash::make(rand(100000, 999999))]);
        }

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }


    public function redirectToFacebook(): RedirectResponse
    {
        return Socialite::driver('facebook')->redirect();
    }
    public function handleFacebookCallback(): RedirectResponse
    {
        $user = Socialite::driver('facebook')->stateless()->user();

        $existingUser = User::where('facebook_id', $user->id)->first();

        if ($existingUser) {
            // Log in the existing user.
            auth()->login($existingUser, true);
        } else {
            // Create a new user.
            $newUser = new User();
            $newUser->name = $user->name;
            $newUser->email = $user->email;
            $newUser->facebook_id = $user->id;
            $newUser->password = bcrypt(request(Str::random()));
            $newUser->save();

            auth()->login($newUser, true);
        }

        return redirect()->intended('/');
    }
    public function redirectToApple(): RedirectResponse
    {
        return Socialite::driver('apple')->redirect();
    }
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
