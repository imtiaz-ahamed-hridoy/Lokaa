<?php

namespace App\Http\Controllers\API\Newsletter;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    use ApiResponse;

    /**
     * Store a newly created newsletter subscriber.
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:newsletters,email',
        ]);

        $newsletter = Newsletter::create([
            'email' => $validated['email'],
        ]);

        return $this->success('Newsletter subscribed successfully.', $newsletter, 201);
    }
}
