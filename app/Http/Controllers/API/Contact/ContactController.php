<?php

namespace App\Http\Controllers\API\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use App\Traits\ApiResponse;

class ContactController extends Controller
{
    use ApiResponse;

    public function store(StoreContactRequest $request)
    {
        $data = $request->only(['name', 'email', 'phone', 'message']);
        $data['topic'] = $request->input('topic', $request->input('subject'));

        $contact = Contact::create($data);

        return $this->success('Contact submitted successfully!', $contact, 201);
    }
}
