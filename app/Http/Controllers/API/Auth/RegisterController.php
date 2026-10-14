<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    use ApiResponse;

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'agreeToTerms' => 'required|boolean|accepted',
        ]);

        $exists = User::where('email', $request->email)->first();

        if ($exists) {
            if ($exists->email_verified_at !== null) {
                return $this->error('Email already verified', 400);
            }

            // Resend OTP instead of creating new user
            $otp = (new Otp)->generate($request->email, 'numeric', 6, 60);

            // Mail::to($exists->email)->send(new EmailVerification($otp->token, $exists));
            \Mail::to($exists->email)->send(new \App\Mail\OTP($otp->token, $exists, 'Verify your email address', 'verify'));

            return $this->success('Otp sent to your email successfully', ['otp' => $otp->token, 'user' => $exists], 201);
        }

        DB::beginTransaction();
        try {

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'User',
                'agree_to_terms' => $request->agreeToTerms,
            ]);

            // Send OTP
            $otp = $this->send_otp($user);

            if (! $otp) {
                throw new \Exception('Failed to send OTP.');
            }
            // $tokenResult = $user->createToken('AuthToken');

            DB::commit();

            return $this->success('Otp sent to your email successfully.', [
                'otp' => $otp->token,
                'user' => $user,
            ], 201);
        } catch (\Exception $exception) {
            DB::rollBack();

            return $this->safeError($exception);
        }
    }

    public function send_otp(User $user, $mailType = 'verify')
    {
        $otp = (new Otp)->generate($user->email, 'numeric', 6, 60);
        $message = $mailType === 'verify' ? 'Verify Your Email Address' : 'Reset Your Password';
        \Mail::to($user->email)->send(new \App\Mail\OTP($otp->token, $user, $message, $mailType));

        return $otp;
    }

    public function resend_otp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $otp = $this->send_otp($user);

                return $this->success('OTP send successfully.', ['otp' => $otp->token], 201);
            } else {
                return $this->error('Email not found', 404);
            }
        } catch (\Exception $exception) {
            return $this->safeError($exception);
        }
    }

    public function verify_otp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'otp' => 'required|string|digits:6',
        ]);
        try {
            $user = User::where('email', $request->email)->first();
            if (! $user) {
                return $this->error('Email not found', 404);
            }

            if ($user->email_verified_at !== null) {
                return $this->error('Email already verified', 404);
            }

            $verify = (new Otp)->validate($request->email, $request->otp);
            if ($verify->status) {
                $user->email_verified_at = now();
                $user->save();

                return $this->success('Email verified successfully.', [
                    'token_type' => 'Bearer',
                    'token' => $user->createToken('AuthToken')->plainTextToken,
                    'user' => $user,
                ]);
            }

            return $this->error($verify->message, 404);
        } catch (\Exception $exception) {
            return $this->safeError($exception);
        }
    }

    public function forgot_password(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        try {
            $user = User::where('email', $request->email)->first();
            if (! $user) {
                return $this->error('User not found', 404);
            }
            $otp = $this->send_otp($user, 'forget');

            return $this->success('OTP send to your email successfully.', ['otp' => $otp->token], 201);
        } catch (\Exception $exception) {
            return $this->safeError($exception);
        }
    }

    public function forgot_verify_otp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'otp' => 'required|string|digits:6',
        ]);

        $verify = (new Otp)->validate($request->email, $request->otp);
        if ($verify->status) {
            $user = User::where('email', $request->email)->first();
            if (! $user) {
                return $this->error('Email not found', 404);
            }
            $user->reset_code = \Str::random(40);
            $user->reset_code_expires_at = Carbon::now()->addDays(1);
            $user->save();

            return $this->success('OTP verified successfully', [
                'token' => $user->reset_code,
            ], 201);
        } else {
            return $this->error($verify->message, 404);
        }
    }

    public function reset_password(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|confirmed',
        ]);

        try {
            $user = User::where('reset_code', $request->token)->first();

            if (! $user) {
                return $this->error('Invalid Token', 404);
            }

            if ($user->reset_code_expires_at < Carbon::now()) {
                return $this->error('Token expired', 404);
            }

            $user->password = Hash::make($request->password);
            $user->reset_code = null;
            $user->reset_code_expires_at = null;
            $user->save();

            return $this->ok('Password reset successfully');
        } catch (\Exception $exception) {
            return $this->safeError($exception);
        }
    }
}
