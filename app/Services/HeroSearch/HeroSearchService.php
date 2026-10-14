<?php

namespace App\Services\HeroSearch;

use App\Models\Listing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class HeroSearchService
{
    private const PER_PAGE = 20;

    /** MySQL innodb_ft_min_token_size — words shorter than this are NOT indexed by FULLTEXT. */
    private const FT_MIN_TOKEN_SIZE = 3;

    /**
     * Perform a global hero search across listings, categories, types, and locations.
     *
     * Uses MySQL FULLTEXT index (MATCH...AGAINST IN BOOLEAN MODE) for fast
     * keyword matching on title, slug, and description. Falls back to LIKE
     * for short keywords (< 3 chars) that FULLTEXT cannot index.
     */
    public function search(?string $keyword, ?string $location, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        $keyword = trim((string) $keyword);
        $location = trim((string) $location);
        $hasKeyword = $keyword !== '';
        $hasLocation = $location !== '';

        // Start with active listings, select only required columns
        $query = Listing::query()
            ->active()
            ->select([
                'id',
                'user_id',
                'listing_type_id',
                'listing_category_id',
                'title',
                'slug',
                'pricing_mode',
                'price',
                'country',
                'city',
                'area',
                'is_featured',
                'is_verified',
                'created_at',
            ])
            ->with([
                'type:id,name',
                'category:id,name',
                'coverMedia:id,listing_id,path',
            ]);

        // Apply keyword search — FULLTEXT for 3+ chars, LIKE fallback for shorter
        if ($hasKeyword) {
            $this->applyKeywordSearch($query, $keyword);
        }

        // Apply location filter (AND with keyword search)
        if ($hasLocation) {
            $query->where(function ($q) use ($location) {
                $q->where('country', 'like', "%{$location}%")
                    ->orWhere('city', 'like', "%{$location}%")
                    ->orWhere('area', 'like', "%{$location}%");
            });
        }

        // Default ordering: featured first, then newest
        $query->orderByDesc('is_featured')
            ->latest('id');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Apply keyword search with FULLTEXT priority scoring.
     *
     * Uses MySQL MATCH...AGAINST IN BOOLEAN MODE (backed by the
     * idx_listings_fulltext_search FULLTEXT index on title, slug, description)
     * as the primary search mechanism. For keywords shorter than
     * innodb_ft_min_token_size (3), falls back to LIKE.
     *
     * Relevance scoring (higher = better match):
     *   10  = FULLTEXT match on title/slug/description (weighted × natural relevance)
     *   10  = Exact title match
     *    7  = Title starts with keyword
     *    6  = Title contains keyword (LIKE %keyword%)
     *    5  = Category name matches
     *    4  = Listing type name matches
     *    3  = Slug matches
     *    2  = Country/city/area matches
     *    1  = Description matches (LIKE)
     */
    private function applyKeywordSearch($query, string $keyword): void
    {
        $likePattern = "%{$keyword}%";
        $startPattern = "{$keyword}%";

        // Use PDO quoting for safe SQL injection prevention
        $pdo = DB::connection()->getPdo();
        $quotedKeyword = $pdo->quote($keyword);
        $quotedLikePattern = $pdo->quote($likePattern);

        $canUseFulltext = mb_strlen($keyword) >= self::FT_MIN_TOKEN_SIZE && DB::connection()->getDriverName() === 'mysql';

        if ($canUseFulltext) {
            $this->applyFulltextSearch($query, $keyword, $likePattern, $pdo, $quotedKeyword, $quotedLikePattern);
        } else {
            $this->applyLikeSearch($query, $keyword, $likePattern, $pdo, $quotedKeyword, $quotedLikePattern);
        }
    }

    /**
     * Apply FULLTEXT-based keyword search using MATCH...AGAINST IN BOOLEAN MODE.
     */
    private function applyFulltextSearch(
        $query,
        string $keyword,
        string $likePattern,
        \PDO $pdo,
        string $quotedKeyword,
        string $quotedLikePattern,
    ): void {
        $booleanModeQuery = $this->buildBooleanModeQuery($keyword);
        $fulltextBinding = $this->stripBooleanOperators($keyword);

        // Full MATCH expression for SELECT (natural mode — better relevance scoring)
        $matchSelect = 'MATCH(title, slug, description) AGAINST(? IN NATURAL LANGUAGE MODE)';
        // Full MATCH expression for WHERE (boolean mode — supports * prefix matching)
        $matchWhere = 'MATCH(title, slug, description) AGAINST(? IN BOOLEAN MODE)';

        $relevanceSql = 'CASE '
            // FULLTEXT match (highest — natural relevance score × 10)
            ."WHEN {$matchWhere} THEN {$matchSelect} * 10 "
            // Exact title match (10)
            ."WHEN title = {$quotedKeyword} THEN 10 "
            // Category name matches (5)
            .'WHEN EXISTS (SELECT 1 FROM listing_categories WHERE id = listing_category_id AND name LIKE ?) THEN 5 '
            // Listing type name matches (4)
            .'WHEN EXISTS (SELECT 1 FROM listing_types WHERE id = listing_type_id AND name LIKE ?) THEN 4 '
            // Location matches (2)
            ."WHEN country LIKE {$quotedLikePattern} OR city LIKE {$quotedLikePattern} OR area LIKE {$quotedLikePattern} THEN 2 "
            .'ELSE 0 END AS relevance_score';

        $query->selectRaw($relevanceSql, [
            $booleanModeQuery, // FIRST binding: MATCH...WHERE (boolean mode)
            $fulltextBinding,  // SECOND binding: MATCH...SELECT (natural mode — same match used in WHEN)
            $likePattern,       // THIRD binding: category LIKE
            $likePattern,       // FOURTH binding: type LIKE
        ])
            ->where(function ($q) use ($booleanModeQuery, $likePattern) {
                // FULLTEXT search as primary
                $q->whereRaw('MATCH(title, slug, description) AGAINST(? IN BOOLEAN MODE)', [$booleanModeQuery])
                  // Category/type via relationship LIKE (these tables don't have FULLTEXT)
                    ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', $likePattern))
                    ->orWhereHas('type', fn ($tq) => $tq->where('name', 'like', $likePattern))
                  // Location via LIKE
                    ->orWhere('country', 'like', $likePattern)
                    ->orWhere('city', 'like', $likePattern)
                    ->orWhere('area', 'like', $likePattern);
            })
            ->orderByDesc('relevance_score');
    }

    /**
     * Apply LIKE-based keyword search (fallback for short keywords < 3 chars).
     *
     * This mirrors the original implementation for short keywords that
     * MySQL FULLTEXT cannot index (below innodb_ft_min_token_size).
     */
    private function applyLikeSearch(
        $query,
        string $keyword,
        string $likePattern,
        \PDO $pdo,
        string $quotedKeyword,
        string $quotedLikePattern,
    ): void {
        $relevanceSql = 'CASE '
            ."WHEN title = {$quotedKeyword} THEN 10 "
            ."WHEN title LIKE {$pdo->quote($keyword.'%')} THEN 7 "
            ."WHEN title LIKE {$quotedLikePattern} THEN 6 "
            .'WHEN EXISTS (SELECT 1 FROM listing_categories WHERE id = listing_category_id AND name LIKE ?) THEN 5 '
            .'WHEN EXISTS (SELECT 1 FROM listing_types WHERE id = listing_type_id AND name LIKE ?) THEN 4 '
            ."WHEN slug LIKE {$quotedLikePattern} THEN 3 "
            ."WHEN country LIKE {$quotedLikePattern} OR city LIKE {$quotedLikePattern} OR area LIKE {$quotedLikePattern} THEN 2 "
            ."WHEN description LIKE {$quotedLikePattern} THEN 1 "
            .'ELSE 0 END AS relevance_score';

        $query->selectRaw($relevanceSql, [$likePattern, $likePattern])
            ->where(function ($q) use ($likePattern) {
                $q->where('title', 'like', $likePattern)
                    ->orWhere('slug', 'like', $likePattern)
                    ->orWhere('description', 'like', $likePattern)
                    ->orWhere('country', 'like', $likePattern)
                    ->orWhere('city', 'like', $likePattern)
                    ->orWhere('area', 'like', $likePattern)
                    ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', $likePattern)->orWhere('slug', 'like', $likePattern))
                    ->orWhereHas('type', fn ($tq) => $tq->where('name', 'like', $likePattern)->orWhere('slug', 'like', $likePattern));
            })
            ->orderByDesc('relevance_score');
    }

    /**
     * Build a BOOLEAN MODE search query string from the user's keyword.
     *
     * - Escapes special BOOLEAN MODE characters
     * - Appends `*` to each word for prefix matching
     * - Multi-word queries use OR semantics (space-separated)
     */
    private function buildBooleanModeQuery(string $keyword): string
    {
        $escaped = $this->escapeBooleanMode($keyword);
        $words = preg_split('/\s+/', trim($escaped));
        $words = array_filter($words, fn ($w) => mb_strlen($w) >= self::FT_MIN_TOKEN_SIZE);

        if (empty($words)) {
            // Fallback: use the raw keyword with minimal escaping
            return $this->escapeBooleanMode($keyword);
        }

        return implode(' ', array_map(fn ($w) => "+{$w}*", $words));
    }

    /**
     * Strip boolean mode operators to get the clean keyword for natural mode scoring.
     */
    private function stripBooleanOperators(string $keyword): string
    {
        return preg_replace('/[+"@<>()~*\-><]+/', ' ', $keyword);
    }

    /**
     * Escape BOOLEAN MODE special characters that would alter query behavior.
     *
     * Characters: + - > < ( ) ~ * : " & |
     */
    private function escapeBooleanMode(string $keyword): string
    {
        // Replace BOOLEAN MODE special chars with space (they break the query syntax)
        return preg_replace('/[+"@|:<>()~*\-><]+/', ' ', $keyword);
    }
}
