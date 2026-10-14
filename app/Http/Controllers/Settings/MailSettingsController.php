<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Mail\OTP;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class MailSettingsController extends Controller
{
    public function edit()
    {
        return Inertia::render('settings/mail', [
            'mailConfig' => [
                'MAIL_MAILER' => env('MAIL_MAILER', ''),
                'MAIL_HOST' => env('MAIL_HOST', ''),
                'MAIL_PORT' => (string) env('MAIL_PORT', ''),
                'MAIL_USERNAME' => env('MAIL_USERNAME', ''),
                'MAIL_PASSWORD' => '',
                'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION', ''),
                'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS', ''),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'MAIL_MAILER' => 'nullable|string',
            'MAIL_HOST' => 'nullable|string',
            'MAIL_PORT' => 'nullable|string',
            'MAIL_USERNAME' => 'nullable|string',
            'MAIL_PASSWORD' => 'nullable|string',
            'MAIL_ENCRYPTION' => 'nullable|string',
            'MAIL_FROM_ADDRESS' => 'nullable|email',
        ]);

        if (empty($validated['MAIL_PASSWORD'])) {
            unset($validated['MAIL_PASSWORD']);
        }

        $this->updateEnvFile($validated);

        Artisan::call('config:clear');

        return back()->with('status', 'mail-settings-updated');
    }

    public function test(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            $user = auth()->user() ?? new User(['name' => 'Admin', 'email' => $request->test_email]);
            Mail::to($request->test_email)->send(new OTP('123456', $user, 'SMTP Configuration Test', 'verify'));

            return back()->with('status', 'mail-test-success');
        } catch (\Throwable $e) {
            return back()->withErrors(['test_email' => 'Failed to send test email: '.$e->getMessage()]);
        }
    }

    private function updateEnvFile(array $data)
    {
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $envContent = File::get($envPath);

            foreach ($data as $key => $value) {
                if (is_null($value)) {
                    $value = '';
                }
                $value = trim($value, '"');
                if (preg_match("/^{$key}=/m", $envContent)) {
                    $envContent = preg_replace(
                        "/^{$key}=.*/m",
                        "{$key}=".(preg_match('/\s/', $value) ? "\"{$value}\"" : $value),
                        $envContent
                    );
                } else {
                    $envContent .= "\n{$key}=".(preg_match('/\s/', $value) ? "\"{$value}\"" : $value);
                }
            }

            File::put($envPath, $envContent);
        }
    }
}
