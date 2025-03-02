<?php

namespace App\Policies;

use App\Models\InternetProtocolAddress;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InternetProtocolAddressPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InternetProtocolAddress $internetProtocolAddress): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InternetProtocolAddress $internetProtocolAddress): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('super-admin')) {
            return true;
        }

        return $user->id === $internetProtocolAddress->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InternetProtocolAddress $internetProtocolAddress): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('super-admin')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InternetProtocolAddress $internetProtocolAddress): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InternetProtocolAddress $internetProtocolAddress): bool
    {
        return false;
    }
}
