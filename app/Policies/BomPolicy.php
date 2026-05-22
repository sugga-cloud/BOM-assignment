<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BomHeader;

class BomPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BomHeader $bomHeader): bool
    {
        return false; // Direct modifications are locked
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BomHeader $bomHeader): bool
    {
        return false; // Deletes are locked
    }
}
