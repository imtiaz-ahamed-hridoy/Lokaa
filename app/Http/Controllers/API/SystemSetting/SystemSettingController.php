<?php

namespace App\Http\Controllers\API\SystemSetting;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SystemSetting\SystemSettingUpdateRequest;
use App\Http\Resources\SocialLinkResource;
use App\Http\Resources\SystemSettingResource;
use App\Models\SocialLink;
use App\Models\SystemSetting;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Storage;

class SystemSettingController extends Controller
{
    use ApiResponse;

    public function systemInfo()
    {
        $systemSetting = SystemSetting::first();

        if (! $systemSetting) {
            return $this->error('System Setting not found', 404);
        }

        return $this->ok('Data Retrieve Successfully!', new SystemSettingResource($systemSetting), 200);
    }

    public function socialLinks()
    {
        $socialLink = SocialLink::first();

        if (! $socialLink) {
            return $this->error('Social Link not found', 404);
        }

        return $this->ok('Data Retrieve Successfully!', new SocialLinkResource($socialLink), 200);
    }

    public function update(SystemSettingUpdateRequest $request)
    {
        $setting = SystemSetting::firstOrNew();
        $setting->fill($request->safe()->except(['logo', 'favicon']));

        if ($request->hasFile('logo')) {
            if ($setting->logo && Storage::disk('public')->exists($setting->logo)) {
                Storage::disk('public')->delete($setting->logo);
            }
            $logo = $request->file('logo');
            $logoName = time().'_'.str_replace(' ', '_', $logo->getClientOriginalName());
            $setting->logo = $logo->storeAs('uploads/logo', $logoName, 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($setting->favicon && Storage::disk('public')->exists($setting->favicon)) {
                Storage::disk('public')->delete($setting->favicon);
            }
            $favicon = $request->file('favicon');
            $faviconName = time().'_'.str_replace(' ', '_', $favicon->getClientOriginalName());
            $setting->favicon = $favicon->storeAs('uploads/favicon', $faviconName, 'public');
        }

        $setting->save();

        return $this->success(
            'System settings updated successfully.',
            new SystemSettingResource($setting->fresh())
        );
    }
}
