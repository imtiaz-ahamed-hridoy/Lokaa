<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class HomeHotDealsConfig extends Model
{
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'section_title',
        'listing_type_id',
        'number_of_listings',
        'listing_source',
        'listing_category_id',
        'manual_listing_ids',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'number_of_listings' => 'integer',
        'manual_listing_ids' => 'array',
        'status' => 'string',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function listingType(): BelongsTo
    {
        return $this->belongsTo(ListingType::class, 'listing_type_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ListingCategory::class, 'listing_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Dynamically load listings based on the configured source.
     */
    public function loadListingsRelation(): static
    {
        $query = Listing::active()
            ->with('coverMedia')
            ->withCount(['views', 'favorites'])
            ->take($this->number_of_listings ?? 8);

        // Filter by listing type if set
        if ($this->listing_type_id) {
            $query->where('listing_type_id', $this->listing_type_id);
        }

        // Filter by category if set
        if ($this->listing_category_id) {
            $query->where('listing_category_id', $this->listing_category_id);
        }

        switch ($this->listing_source) {
            case 'featured':
                $query->where('is_featured', true)->latest();
                break;
            case 'trending':
                $query->orderBy('views_count', 'desc');
                break;
            case 'manual':
                $rawIds = (array) ($this->manual_listing_ids ?? []);
                $ids = array_values(array_filter(array_map('intval', $rawIds), fn ($id) => $id > 0));
                if (! empty($ids)) {
                    $query->whereIn('id', $ids);
                    if (DB::getDriverName() === 'mysql') {
                        $query->orderByRaw('FIELD(id,'.implode(',', $ids).')');
                    } else {
                        $cases = collect($ids)->map(fn ($id, $i) => "WHEN {$id} THEN {$i}")->implode(' ');
                        $query->orderByRaw("CASE id {$cases} ELSE ".count($ids).' END');
                    }
                }
                break;
            case 'random':
                $query->inRandomOrder();
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $this->setRelation('listings', $query->get());

        return $this;
    }
}
