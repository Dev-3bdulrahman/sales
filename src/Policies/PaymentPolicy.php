<?php

namespace Dev3bdulrahman\Sales\Policies;

use App\Models\User;
use Dev3bdulrahman\Sales\Models\Payment;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sales.payments.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->can('sales.payments.view') && $payment->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('sales.payments.create');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->can('sales.payments.delete') && $payment->company_id === $user->company_id;
    }
}
