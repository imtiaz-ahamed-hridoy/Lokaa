<?php

namespace App\Services\Marketplace;

use App\Models\Listing;
use App\Models\ListingMedia;
use App\Models\User;
use App\Traits\FileManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ListingService
{
    use FileManager;

    public function create(array $data, array $media, User $owner): Listing
    {
        return DB::transaction(function () use ($data, $media, $owner): Listing {
            $listing = Listing::create($this->listingPayload($data, $owner));
            $this->replaceMedia($listing, $media);

            return $listing->fresh(['type', 'category', 'media', 'coverMedia']);
        });
    }

    public function update(Listing $listing, array $data, ?array $media = null): Listing
    {
        return DB::transaction(function () use ($listing, $data, $media): Listing {
            $listing->update($this->listingPayload($data, $listing->user, false));

            if ($media !== null) {
                $this->replaceMedia($listing, $media);
            }

            return $listing->fresh(['type', 'category', 'media', 'coverMedia']);
        });
    }

    public function updateStatus(Listing $listing, string $status): Listing
    {
        $payload = ['status' => $status];

        if ($status === Listing::STATUS_ACTIVE) {
            $payload['published_at'] = now();
            $payload['rejected_at'] = null;
        }

        if ($status === Listing::STATUS_REJECTED) {
            $payload['rejected_at'] = now();
        }

        $listing->update($payload);

        return $listing->fresh(['type', 'category', 'media', 'coverMedia']);
    }

    private function listingPayload(array $data, User $owner, bool $creating = true): array
    {
        $title = $data['title'] ?? ($creating ? '' : null);
        $payload = [
            'user_id' => $data['user_id'] ?? $owner->id,
            'listing_type_id' => $data['listing_type_id'] ?? null,
            'listing_category_id' => $data['listing_category_id'] ?? null,
            'title' => $title,
            'description' => $data['description'] ?? null,
            'pricing_mode' => $data['pricing_mode'] ?? null,
            'price' => $data['price'] ?? null,
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'area' => $data['area'] ?? null,
            'street_address' => $data['street_address'] ?? null,
            'status' => $data['status'] ?? ($creating ? Listing::STATUS_PENDING : null),
            'is_featured' => $data['is_featured'] ?? ($creating ? false : null),
            'is_verified' => $data['is_verified'] ?? ($creating ? false : null),
        ];

        if ($creating) {
            $payload['slug'] = $this->uniqueSlug($title);
        }

        // When updating, only include fields that were actually provided
        if (! $creating) {
            return array_filter($payload, fn ($value) => $value !== null);
        }

        return $payload;
    }

    private function replaceMedia(Listing $listing, array $media): void
    {
        $files = collect($media)
            ->filter(fn ($file) => $file instanceof UploadedFile && $file->isValid())
            ->values();

        // Delete all existing media
        $listing->media()->get()->each(function (ListingMedia $item): void {
            $this->deletePublicPath($item->path);
            $item->delete();
        });

        if ($files->isEmpty()) {
            return;
        }

        $paths = $this->uploadToPublic($files->all(), 'uploads/listings');

        foreach ($paths as $path) {
            if ($path) {
                $listing->media()->create(['path' => $path]);
            }
        }
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 1;

        while (Listing::where('slug', $slug)->withTrashed()->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function deletePublicPath(?string $path): void
    {
        if ($path && File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}
