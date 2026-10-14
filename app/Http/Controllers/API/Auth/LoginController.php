<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        try {
            $request->merge([
                'remember_token' => filter_var($request->remember_token, FILTER_VALIDATE_BOOLEAN),
            ]);

            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if (! Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return $this->error(
                    'The provided credentials do not match our records.',
                    401,
                    ['email' => ['The provided credentials do not match our records.']]
                );
            }

            if (Auth::user()->email_verified_at === null) {
                return $this->error('Email not verified.', 403);
            }

            if (Auth::user()->status === 'Inactive') {
                return $this->error('Your account is currently inactive. Please contact support.', 403);
            }

            $user = Auth::user();

            if ($request->remember_token) {
                $user->setRememberToken(Str::random(60));
            }

            $user->save();

            $tokenResult = $user->createToken('AuthToken');

            return $this->success('Login Successful.', [
                'token_type' => 'Bearer',
                'token' => $tokenResult->plainTextToken,
                'user' => $user,
            ]);
        } catch (ValidationException $e) {
            // Let ValidationException bubble up to the global exception handler
            // which formats it as a proper 422 response
            throw $e;
        } catch (\Exception $exception) {
            return $this->safeError($exception);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Revoke the current user's token
            $request->user()->currentAccessToken()->delete();

            // Return a response indicating the user was logged out
            return $this->success('Logged out successfully.', [], 200);
        } catch (\Exception $exception) {
            return $this->safeError($exception);
        }
    }
}
