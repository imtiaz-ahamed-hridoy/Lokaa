<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\API\Marketplace\StoreListingRequest;
use Illuminate\Validation\Rule;

class AdminListingRequest extends StoreListingRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'Admin';
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['user_id'] = ['required', 'integer', Rule::exists('users', 'id')];
        $rules['status'] = ['required', Rule::in(['Draft', 'Pending', 'Active', 'Expired', 'Rejected', 'Archived'])];
        $rules['media'] = $this->isMethod('post')
            ? ['required', 'array', 'min:4']
            : ['nullable', 'array', 'min:4'];
        $rules['is_featured'] = ['nullable', 'boolean'];
        $rules['is_verified'] = ['nullable', 'boolean'];

        return $rules;
    }

    public function after(): array
    {
        return [
            ...parent::after(),
            function ($validator): void {
                // Future admin validation can go here
            },
        ];
    }
}
