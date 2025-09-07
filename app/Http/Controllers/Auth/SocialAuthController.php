<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle provider callback and authenticate user.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback(string $provider): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // Check if user already exists with this provider ID
            $user = User::where("{$provider}_id", $socialUser->getId())->first();
            
            // If user doesn't exist with provider ID, check by email
            if (!$user) {
                $user = User::where('email', $socialUser->getEmail())->first();
                
                // If user exists with email, update their provider ID
                if ($user) {
                    $user->update([
                        "{$provider}_id" => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                    ]);
                } else {
                    // Create a new user
                    $user = User::create([
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        "{$provider}_id" => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                        'password' => Hash::make(\Illuminate\Support\Str::random(16)), // Random password
                    ]);
                }
            }
            
            // Login the user
            Auth::login($user);
            
            return redirect()->intended(route('home', absolute: false));
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to login with ' . ucfirst($provider) . '. Please try again.',
            ]);
        }
    }
}