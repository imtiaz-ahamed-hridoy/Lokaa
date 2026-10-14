<?php

namespace App\Http\Controllers\Web\Admin\HomeCMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomeCMS\StoreHeroSectionRequest;
use App\Models\HomeHeroSection;
use App\Traits\FileManager;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class HeroSectionController extends Controller
{
    use FileManager;

    public function index()
    {
        $heroes = HomeHeroSection::search(['badge', 'heading'])
            ->paginateData();

        return Inertia::render('home-cms/hero/index', [
            'heroes' => $heroes,
        ]);
    }

    public function edit(HomeHeroSection $homeHeroSection)
    {
        return Inertia::render('home-cms/hero/edit', [
            'hero' => $homeHeroSection,
        ]);
    }

    public function update(StoreHeroSectionRequest $request, HomeHeroSection $homeHeroSection)
    {
        $data = $request->validated();

        if ($request->hasFile('background_image')) {
            $this->deleteFromPublic($homeHeroSection, 'background_image');
            $paths = $this->uploadToPublic($request->file('background_image'), 'uploads/home/hero');
            $data['background_image'] = $paths[0] ?? null;
        } else {
            unset($data['background_image']);
        }

        $data['updated_by'] = auth()->id();

        $homeHeroSection->update($data);

        Cache::forget('home_hero_section');
        Cache::forget('home_page_all');

        return redirect()->route('home-cms.hero.index')->with('success', 'Hero section updated successfully!');
    }

    public function updateStatus(HomeHeroSection $homeHeroSection)
    {
        $homeHeroSection->update([
            'status' => $homeHeroSection->status === 'Active' ? 'Inactive' : 'Active',
            'updated_by' => auth()->id(),
        ]);

        Cache::forget('home_hero_section');
        Cache::forget('home_page_all');

        return redirect()->back()->with('success', 'Status updated successfully!');
    }
}
