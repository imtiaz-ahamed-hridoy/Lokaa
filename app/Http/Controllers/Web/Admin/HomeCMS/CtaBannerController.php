<?php

namespace App\Http\Controllers\Web\Admin\HomeCMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomeCMS\StoreCtaBannerRequest;
use App\Models\HomeCtaBanner;
use App\Traits\FileManager;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class CtaBannerController extends Controller
{
    use FileManager;

    public function index()
    {
        $banners = HomeCtaBanner::search(['title'])
            ->paginateData();

        return Inertia::render('home-cms/cta-banner/index', [
            'banners' => $banners,
        ]);
    }

    public function create()
    {
        return Inertia::render('home-cms/cta-banner/create');
    }

    public function store(StoreCtaBannerRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('background_image')) {
            $paths = $this->uploadToPublic($request->file('background_image'), 'uploads/home/cta-banner');
            $data['background_image'] = $paths[0] ?? null;
        }

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        HomeCtaBanner::create($data);

        Cache::forget('home_cta_banner');
        Cache::forget('home_page_all');

        return redirect()->route('home-cms.cta-banner.index')->with('success', 'CTA banner created successfully!');
    }

    public function edit(HomeCtaBanner $homeCtaBanner)
    {
        return Inertia::render('home-cms/cta-banner/edit', [
            'banner' => $homeCtaBanner,
        ]);
    }

    public function update(StoreCtaBannerRequest $request, HomeCtaBanner $homeCtaBanner)
    {
        $data = $request->validated();

        if ($request->hasFile('background_image')) {
            $this->deleteFromPublic($homeCtaBanner, 'background_image');
            $paths = $this->uploadToPublic($request->file('background_image'), 'uploads/home/cta-banner');
            $data['background_image'] = $paths[0] ?? null;
        } else {
            unset($data['background_image']);
        }

        $data['updated_by'] = auth()->id();

        $homeCtaBanner->update($data);

        Cache::forget('home_cta_banner');
        Cache::forget('home_page_all');

        return redirect()->route('home-cms.cta-banner.index')->with('success', 'CTA banner updated successfully!');
    }

    public function updateStatus(HomeCtaBanner $homeCtaBanner)
    {
        $homeCtaBanner->update([
            'status' => $homeCtaBanner->status === 'Active' ? 'Inactive' : 'Active',
            'updated_by' => auth()->id(),
        ]);

        Cache::forget('home_cta_banner');
        Cache::forget('home_page_all');

        return redirect()->back()->with('success', 'Status updated successfully!');
    }

    public function destroy(HomeCtaBanner $homeCtaBanner)
    {
        if ($homeCtaBanner->background_image) {
            $this->deleteFromPublic($homeCtaBanner, 'background_image');
        }
        $homeCtaBanner->delete();

        Cache::forget('home_cta_banner');
        Cache::forget('home_page_all');

        return redirect()->route('home-cms.cta-banner.index')->with('success', 'CTA banner deleted successfully!');
    }
}
