<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    use ApiResponse;

    /**
     * POST /api/social-login
     *
     * Accepts a provider access token and authenticates/registers the user.
     *
     * Request:
     * {
     *     "provider": "google|facebook|apple|github",
     *     "token": "PROVIDER_ACCESS_TOKEN"
     * }
     */
    public function socialLogin(Request $request)
    {
        $request->validate([
            'provider' => 'required|string|in:google,facebook,apple,github',
            'token' => 'required|string',
        ]);

        $provider = $request->provider;

        try {
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->userFromToken($request->token);

            if (! $socialUser || ! $socialUser->getEmail()) {
                return $this->error('Invalid '.ucfirst($provider).' account. Email is required.', 401);
            }

            $email = $socialUser->getEmail();

            $user = User::firstOrNew(['email' => $email]);

            if (! $user->exists) {
                // New user — create from social profile
                $user->name = $socialUser->getName()
                    ?? $socialUser->getNickname()
                    ?? explode('@', $email)[0]
                    ?? 'User';

                $user->email = $email;
                $user->password = Hash::make(Str::random(32));
                $user->role = 'User';
                $user->email_verified_at = now();
                $user->avatar = $socialUser->getAvatar();
            } else {
                // Existing user — refresh avatar from provider
                $user->avatar = $socialUser->getAvatar() ?? $user->avatar;
            }

            if ($user->exists && $user->status === 'Inactive') {
                return $this->error('Your account is currently inactive. Please contact support.', 403);
            }

            // Update provider linkage
            $user->provider = $provider;
            $user->provider_id = $socialUser->getId();
            $user->provider_token = $request->token;
            $user->save();

            // Revoke previous tokens for this provider to keep sessions clean,
            // but leave tokens from other auth methods (email, other providers) intact.
            $user->tokens()
                ->where('name', 'SocialAuth-'.$provider)
                ->delete();

            // Issue a new token
            $plainToken = $user->createToken('SocialAuth-'.$provider)->plainTextToken;

            return $this->success('Login successful.', [
                'token_type' => 'Bearer',
                'token' => $plainToken,
                'user' => new UserResource($user),
            ]);
        } catch (\Throwable $e) {
            Log::error('Social Login Failed', [
                'provider' => $provider,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            $label = ucfirst($provider);

            return $this->error(
                $label.' authentication failed. Please try again later.',
                401
            );
        }
    }
}
