<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    public function before(User $user): ?bool
    {
        return $user->role === 'Admin' ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Listing $listing): bool
    {
        return $listing->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->status === 'Active';
    }

    public function update(User $user, Listing $listing): bool
    {
        return $listing->user_id === $user->id;
    }

    public function delete(User $user, Listing $listing): bool
    {
        return $listing->user_id === $user->id;
    }

    public function restore(User $user, Listing $listing): bool
    {
        return $listing->user_id === $user->id;
    }
}
