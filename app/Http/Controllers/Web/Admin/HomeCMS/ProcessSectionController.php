<?php

namespace App\Http\Controllers\Web\Admin\HomeCMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomeCMS\StoreProcessItemRequest;
use App\Models\HomeProcessItem;
use App\Traits\FileManager;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ProcessSectionController extends Controller
{
    use FileManager;

    public function index()
    {
        $items = HomeProcessItem::search(['title'])
            ->latest('id')
            ->paginateData();

        return Inertia::render('home-cms/process/index', [
            'items' => $items,
        ]);
    }

    public function edit(HomeProcessItem $homeProcessItem)
    {
        return Inertia::render('home-cms/process/edit', [
            'item' => $homeProcessItem,
        ]);
    }

    public function update(StoreProcessItemRequest $request, HomeProcessItem $homeProcessItem)
    {
        $data = $request->validated();

        if ($request->hasFile('icon')) {
            $this->deleteFromPublic($homeProcessItem, 'icon');
            $paths = $this->uploadToPublic($request->file('icon'), 'uploads/home/process');
            $data['icon'] = $paths[0] ?? null;
        } else {
            unset($data['icon']);
        }

        $data['updated_by'] = auth()->id();

        $homeProcessItem->update($data);

        Cache::forget('home_process_items');
        Cache::forget('home_page_all');

        return redirect()->route('home-cms.process.index')->with('success', 'Process item updated successfully!');
    }

    public function updateStatus(HomeProcessItem $homeProcessItem)
    {
        $homeProcessItem->update([
            'status' => $homeProcessItem->status === 'Active' ? 'Inactive' : 'Active',
            'updated_by' => auth()->id(),
        ]);

        Cache::forget('home_process_items');
        Cache::forget('home_page_all');

        return redirect()->back()->with('success', 'Status updated successfully!');
    }
}
