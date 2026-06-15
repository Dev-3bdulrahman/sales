<?php

namespace Dev3bdulrahman\Sales\Policies;

use App\Models\User;
use Dev3bdulrahman\Sales\Models\SalesReturn;

class SalesReturnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sales.returns.view');
    }

    public function view(User $user, SalesReturn $return): bool
    {
        return $user->can('sales.returns.view') && $return->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('sales.returns.create');
    }

    public function update(User $user, SalesReturn $return): bool
    {
        return $user->can('sales.returns.update') && $return->company_id === $user->company_id;
    }

    public function delete(User $user, SalesReturn $return): bool
    {
        return $user->can('sales.returns.delete') && $return->company_id === $user->company_id;
    }
}
