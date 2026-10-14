<?php

namespace App\Http\Controllers\Web\Admin\HomeCMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomeCMS\StoreHotDealsConfigRequest;
use App\Models\HomeHotDealsConfig;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\ListingType;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class HotDealsConfigController extends Controller
{
    public function index()
    {
        $configs = HomeHotDealsConfig::search(['section_title'])
            ->paginateData();

        return Inertia::render('home-cms/hot-deals/index', [
            'configs' => $configs,
        ]);
    }

    public function create()
    {
        return Inertia::render('home-cms/hot-deals/create', [
            'listingTypes' => ListingType::select(['id', 'name'])->orderBy('name')->get(),
            'categories' => ListingCategory::select(['id', 'name', 'listing_type_id'])->orderBy('name')->get(),
        ]);
    }

    public function store(StoreHotDealsConfigRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        HomeHotDealsConfig::create($data);

        Cache::forget('home_hot_deals');
        Cache::forget('home_page_all');

        return redirect()->route('home-cms.hot-deals.index')->with('success', 'Hot deals configuration created successfully!');
    }

    public function edit(HomeHotDealsConfig $homeHotDealsConfig)
    {
        return Inertia::render('home-cms/hot-deals/edit', [
            'config' => $homeHotDealsConfig,
            'listingTypes' => ListingType::select(['id', 'name'])->orderBy('name')->get(),
            'categories' => ListingCategory::select(['id', 'name', 'listing_type_id'])->orderBy('name')->get(),
            'listings' => Listing::select(['id', 'title'])
                ->where('status', 'Active')
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function update(StoreHotDealsConfigRequest $request, HomeHotDealsConfig $homeHotDealsConfig)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $homeHotDealsConfig->update($data);

        Cache::forget('home_hot_deals');
        Cache::forget('home_page_all');

        return redirect()->route('home-cms.hot-deals.index')->with('success', 'Hot deals configuration updated successfully!');
    }

    public function updateStatus(HomeHotDealsConfig $homeHotDealsConfig)
    {
        $homeHotDealsConfig->update([
            'status' => $homeHotDealsConfig->status === 'Active' ? 'Inactive' : 'Active',
            'updated_by' => auth()->id(),
        ]);

        Cache::forget('home_hot_deals');
        Cache::forget('home_page_all');

        return redirect()->back()->with('success', 'Status updated successfully!');
    }

    public function destroy(HomeHotDealsConfig $homeHotDealsConfig)
    {
        $homeHotDealsConfig->delete();

        Cache::forget('home_hot_deals');
        Cache::forget('home_page_all');

        return redirect()->route('home-cms.hot-deals.index')->with('success', 'Hot deals configuration deleted successfully!');
    }
}
