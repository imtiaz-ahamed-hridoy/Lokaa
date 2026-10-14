<?php

namespace App\Http\Controllers\Web\Admin\HomeCMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomeCMS\StoreFeaturedServicesConfigRequest;
use App\Models\HomeFeaturedServicesConfig;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\ListingType;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class FeaturedServicesConfigController extends Controller
{
    public function index()
    {
        $configs = HomeFeaturedServicesConfig::search(['title'])
            ->paginateData();

        return Inertia::render('home-cms/featured-services/index', [
            'configs' => $configs,
        ]);
    }

    public function create()
    {
        return Inertia::render('home-cms/featured-services/create', [
            'listingTypes' => ListingType::select(['id', 'name'])->orderBy('name')->get(),
            'categories' => ListingCategory::select(['id', 'name', 'listing_type_id'])->orderBy('name')->get(),
        ]);
    }

    public function store(StoreFeaturedServicesConfigRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        HomeFeaturedServicesConfig::create($data);

        Cache::forget('home_featured_services');
        Cache::forget('home_page_all');

        return redirect()->route('home-cms.featured-services.index')->with('success', 'Featured services configuration created successfully!');
    }

    public function edit(HomeFeaturedServicesConfig $homeFeaturedServicesConfig)
    {
        return Inertia::render('home-cms/featured-services/edit', [
            'config' => $homeFeaturedServicesConfig,
            'listingTypes' => ListingType::select(['id', 'name'])->orderBy('name')->get(),
            'categories' => ListingCategory::select(['id', 'name', 'listing_type_id'])->orderBy('name')->get(),
            'listings' => Listing::select(['id', 'title'])
                ->where('status', 'Active')
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function update(StoreFeaturedServicesConfigRequest $request, HomeFeaturedServicesConfig $homeFeaturedServicesConfig)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $homeFeaturedServicesConfig->update($data);

        Cache::forget('home_featured_services');
        Cache::forget('home_page_all');

        return redirect()->route('home-cms.featured-services.index')->with('success', 'Featured services configuration updated successfully!');
    }

    public function updateStatus(HomeFeaturedServicesConfig $homeFeaturedServicesConfig)
    {
        $homeFeaturedServicesConfig->update([
            'status' => $homeFeaturedServicesConfig->status === 'Active' ? 'Inactive' : 'Active',
            'updated_by' => auth()->id(),
        ]);

        Cache::forget('home_featured_services');
        Cache::forget('home_page_all');

        return redirect()->back()->with('success', 'Status updated successfully!');
    }

    public function destroy(HomeFeaturedServicesConfig $homeFeaturedServicesConfig)
    {
        $homeFeaturedServicesConfig->delete();

        Cache::forget('home_featured_services');
        Cache::forget('home_page_all');

        return redirect()->route('home-cms.featured-services.index')->with('success', 'Featured services configuration deleted successfully!');
    }
}
