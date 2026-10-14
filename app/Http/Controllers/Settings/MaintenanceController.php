<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Traits\DatabaseExportable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MaintenanceController extends Controller
{
    use DatabaseExportable;

    /**
     * Display the maintenance page.
     */
    public function show(): Response
    {
        return Inertia::render('settings/maintenance');
    }

    /**
     * Clear application cache.
     *
     * @return RedirectResponse
     */
    public function clearCache(Request $request)
    {
        try {
            Artisan::call('cache:clear');

            return back()->with('success', 'Application cache cleared successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear cache: '.$e->getMessage());
        }
    }

    /**
     * Clear view cache.
     *
     * @return RedirectResponse
     */
    public function clearViews(Request $request)
    {
        try {
            Artisan::call('view:clear');

            return back()->with('success', 'View cache cleared successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear views: '.$e->getMessage());
        }
    }

    /**
     * Clear compiled files.
     *
     * @return RedirectResponse
     */
    public function clearCompiled(Request $request)
    {
        try {
            Artisan::call('optimize:clear');

            return back()->with('success', 'Compiled files cleared successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear compiled files: '.$e->getMessage());
        }
    }

    /**
     * Clear all caches.
     *
     * @return RedirectResponse
     */
    public function clearAll(Request $request)
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('optimize:clear');

            return back()->with('success', 'All caches cleared successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear all caches: '.$e->getMessage());
        }
    }

    /**
     * Download a SQL export of the database.
     *
     * @return BinaryFileResponse|RedirectResponse
     */
    public function export(Request $request)
    {
        try {
            return $this->exportDatabase();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export database: '.$e->getMessage());
        }
    }
}
