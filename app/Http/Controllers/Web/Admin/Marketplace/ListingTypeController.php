<?php

namespace App\Http\Controllers\Web\Admin\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListingTypeRequest;
use App\Models\ListingType;
use App\Traits\FileManager;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ListingTypeController extends Controller
{
    use FileManager;

    public function index(): Response
    {
        return Inertia::render('marketplace/listing-types/index', [
            'types' => ListingType::search(['name', 'slug'])->paginateData(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('marketplace/listing-types/create');
    }

    public function store(ListingTypeRequest $request)
    {
        $data = $this->payload($request->validated());

        if ($request->hasFile('image')) {
            $paths = $this->uploadToPublic($request->file('image'), 'uploads/marketplace/types');
            $data['image'] = $paths[0] ?? null;
        }

        ListingType::create($data);

        return redirect()->route('listing-type.index')->with('success', 'Listing type created successfully!');
    }

    public function show(ListingType $listingType)
    {
        return redirect()->route('listing-type.edit', $listingType);
    }

    public function edit(ListingType $listingType): Response
    {
        return Inertia::render('marketplace/listing-types/edit', [
            'type' => $listingType,
        ]);
    }

    public function update(ListingTypeRequest $request, ListingType $listingType)
    {
        $data = $this->payload($request->validated());

        if ($request->hasFile('image')) {
            $this->deleteFromPublic($listingType, 'image');
            $paths = $this->uploadToPublic($request->file('image'), 'uploads/marketplace/types');
            $data['image'] = $paths[0] ?? null;
        } else {
            unset($data['image']);
        }

        $listingType->update($data);

        return redirect()->route('listing-type.index')->with('success', 'Listing type updated successfully!');
    }

    public function updateStatus(ListingType $listingType)
    {
        $listingType->update([
            'status' => $listingType->status === ListingType::STATUS_ACTIVE
                ? ListingType::STATUS_INACTIVE
                : ListingType::STATUS_ACTIVE,
        ]);

        return redirect()->back()->with('success', 'Listing type status updated successfully!');
    }

    public function destroy(ListingType $listingType)
    {
        if ($listingType->listings()->exists() || $listingType->categories()->exists()) {
            return redirect()->back()->with('error', 'Listing type cannot be deleted while categories or listings use it.');
        }

        $this->deleteFromPublic($listingType, 'image');
        $listingType->delete();

        return redirect()->route('listing-type.index')->with('success', 'Listing type deleted successfully!');
    }

    private function payload(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);

        return $data;
    }
}
