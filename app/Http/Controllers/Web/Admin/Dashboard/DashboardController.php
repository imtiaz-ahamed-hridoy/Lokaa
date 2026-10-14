<?php

namespace App\Http\Controllers\Web\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\ListingType;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('dashboard', [
            'stats' => [
                'total_users' => User::where('role', 'User')->count(),
                'total_listing_types' => ListingType::count(),
                'total_categories' => ListingCategory::count(),
                'total_listings' => Listing::count(),
                'active_listings' => Listing::where('status', Listing::STATUS_ACTIVE)->count(),
                'pending_listings' => Listing::where('status', Listing::STATUS_PENDING)->count(),
                'rejected_listings' => Listing::where('status', Listing::STATUS_REJECTED)->count(),
                'total_contacts' => Contact::count(),
                'total_newsletters' => Newsletter::count(),
            ],
        ]);
    }
}
