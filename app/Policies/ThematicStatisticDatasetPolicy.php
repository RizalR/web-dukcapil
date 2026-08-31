<?php

namespace App\Policies;

use App\Models\ThematicStatisticDataset;
use App\Models\User;
use App\ThematicStatisticDatasetStatus;

class ThematicStatisticDatasetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, ThematicStatisticDataset $dataset): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, ThematicStatisticDataset $dataset): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, ThematicStatisticDataset $dataset): bool
    {
        return $user->isSuperAdmin() && $dataset->status === ThematicStatisticDatasetStatus::Draft;
    }
}
