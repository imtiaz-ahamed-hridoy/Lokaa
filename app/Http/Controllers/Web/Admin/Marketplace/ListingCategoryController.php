<?php

namespace App\Http\Controllers\Web\Admin\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListingCategoryRequest;
use App\Models\ListingCategory;
use App\Models\ListingType;
use App\Traits\FileManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ListingCategoryController extends Controller
{
    use FileManager;

    public function index(): Response
    {
        $categories = ListingCategory::with('type:id,name')
            ->search(['name', 'slug'])
            ->paginateData();

        return Inertia::render('marketplace/categories/index', [
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('marketplace/categories/create', [
            'types' => $this->types(),
        ]);
    }

    public function store(ListingCategoryRequest $request)
    {
        $data = $this->payload($request->validated());

        if ($request->hasFile('image')) {
            $paths = $this->uploadToPublic($request->file('image'), 'uploads/marketplace/categories');
            $data['image'] = $paths[0] ?? null;
        }

        ListingCategory::create($data);
        $this->clearCategoryCache();

        return redirect()->route('listing-category.index')->with('success', 'Category created successfully!');
    }

    public function show(ListingCategory $listingCategory)
    {
        return redirect()->route('listing-category.edit', $listingCategory);
    }

    public function edit(ListingCategory $listingCategory): Response
    {
        return Inertia::render('marketplace/categories/edit', [
            'category' => $listingCategory,
            'types' => $this->types(),
        ]);
    }

    public function update(ListingCategoryRequest $request, ListingCategory $listingCategory)
    {
        $data = $this->payload($request->validated());

        if ($request->hasFile('image')) {
            $this->deleteFromPublic($listingCategory, 'image');
            $paths = $this->uploadToPublic($request->file('image'), 'uploads/marketplace/categories');
            $data['image'] = $paths[0] ?? null;
        } else {
            unset($data['image']);
        }

        $listingCategory->update($data);
        $this->clearCategoryCache();

        return redirect()->route('listing-category.index')->with('success', 'Category updated successfully!');
    }

    public function updateStatus(ListingCategory $listingCategory)
    {
        $listingCategory->update([
            'status' => $listingCategory->status === ListingCategory::STATUS_ACTIVE
                ? ListingCategory::STATUS_INACTIVE
                : ListingCategory::STATUS_ACTIVE,
        ]);
        $this->clearCategoryCache();

        return redirect()->back()->with('success', 'Category status updated successfully!');
    }

    public function destroy(ListingCategory $listingCategory)
    {
        if ($listingCategory->listings()->exists()) {
            return redirect()->back()->with('error', 'Category cannot be deleted while listings use it.');
        }

        $this->deleteFromPublic($listingCategory, 'image');
        $listingCategory->delete();
        $this->clearCategoryCache();

        return redirect()->route('listing-category.index')->with('success', 'Category deleted successfully!');
    }

    private function clearCategoryCache(): void
    {
        Cache::forget('home_explore_categories');
        Cache::forget('home_page_all');
    }

    private function types()
    {
        return ListingType::select(['id', 'name'])->orderBy('id')->get();
    }

    private function payload(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);

        return $data;
    }
}
