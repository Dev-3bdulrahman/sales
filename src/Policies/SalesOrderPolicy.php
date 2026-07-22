<?php

namespace Dev3bdulrahman\Sales\Policies;

use App\Models\User;
use Dev3bdulrahman\Sales\Models\SalesOrder;

class SalesOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sales.orders.view');
    }

    public function view(User $user, SalesOrder $order): bool
    {
        return $user->can('sales.orders.view') && $order->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('sales.orders.create');
    }

    public function update(User $user, SalesOrder $order): bool
    {
        return $user->can('sales.orders.update') && $order->company_id === $user->company_id;
    }

    public function delete(User $user, SalesOrder $order): bool
    {
        return $user->can('sales.orders.delete') && $order->company_id === $user->company_id;
    }

    public function confirm(User $user, SalesOrder $order): bool
    {
        return $user->can('sales.orders.confirm') && $order->company_id === $user->company_id;
    }
}
