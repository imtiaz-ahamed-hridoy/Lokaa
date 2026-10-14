<?php

namespace App\Http\Controllers\Web\Admin\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListingRequest;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\ListingType;
use App\Models\User;
use App\Services\Marketplace\ListingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    public function index(Request $request): Response
    {
        $listings = Listing::query()
            ->with(['user:id,name,email', 'type:id,name,slug', 'category:id,name,slug', 'coverMedia'])
            ->withCount(['views', 'favorites'])
            ->search(['title', 'city', 'area'])
            ->when($request->status, fn ($query) => $query->where('status', $request->status))
            ->when($request->listing_type_id, fn ($query) => $query->where('listing_type_id', $request->listing_type_id))
            ->when($request->listing_category_id, fn ($query) => $query->where('listing_category_id', $request->listing_category_id))
            ->paginateData();

        return Inertia::render('marketplace/listings/index', [
            'listings' => $listings,
            'types' => ListingType::select(['id', 'name'])->orderBy('id')->get(),
            'categories' => ListingCategory::select(['id', 'listing_type_id', 'name'])->orderBy('id')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('marketplace/listings/create', $this->formLookups());
    }

    public function store(AdminListingRequest $request, ListingService $listingService)
    {
        $user = User::findOrFail($request->integer('user_id'));
        $listingService->create($request->validated(), $request->file('media', []), $user);

        return redirect()->route('listing.index')->with('success', 'Listing created successfully!');
    }

    public function show(Listing $listing)
    {
        return redirect()->route('listing.edit', $listing);
    }

    public function edit(Listing $listing): Response
    {
        $listing->load(['media', 'type', 'category']);

        return Inertia::render('marketplace/listings/edit', [
            ...$this->formLookups(),
            'listing' => $listing,
        ]);
    }

    public function update(AdminListingRequest $request, Listing $listing, ListingService $listingService)
    {
        $listingService->update(
            $listing,
            $request->validated(),
            $request->hasFile('media') ? $request->file('media', []) : null
        );

        return redirect()->route('listing.index')->with('success', 'Listing updated successfully!');
    }

    public function updateStatus(Request $request, Listing $listing, ListingService $listingService)
    {
        $request->validate([
            'status' => ['required', Rule::in([Listing::STATUS_DRAFT, Listing::STATUS_PENDING, Listing::STATUS_ACTIVE, Listing::STATUS_REJECTED, Listing::STATUS_ARCHIVED])],
        ]);

        $listingService->updateStatus($listing, $request->status);

        return redirect()->back()->with('success', 'Listing status updated successfully!');
    }

    public function destroy(Listing $listing)
    {
        $listing->delete();

        return redirect()->route('listing.index')->with('success', 'Listing deleted successfully!');
    }

    public function restore(int $listing)
    {
        Listing::onlyTrashed()->findOrFail($listing)->restore();

        return redirect()->back()->with('success', 'Listing restored successfully!');
    }

    private function formLookups(): array
    {
        return [
            'users' => User::where('role', 'User')->select(['id', 'name', 'email'])->orderBy('id')->get(),
            'types' => ListingType::select(['id', 'name', 'slug'])->orderBy('id')->get(),
            'categories' => ListingCategory::select(['id', 'listing_type_id', 'name'])->orderBy('id')->get(),
        ];
    }
}
