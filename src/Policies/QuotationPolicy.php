<?php

namespace Dev3bdulrahman\Sales\Policies;

use App\Models\User;
use Dev3bdulrahman\Sales\Models\Quotation;

class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sales.quotations.view');
    }

    public function view(User $user, Quotation $quotation): bool
    {
        return $user->can('sales.quotations.view') && $quotation->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('sales.quotations.create');
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $user->can('sales.quotations.update') && $quotation->company_id === $user->company_id;
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        return $user->can('sales.quotations.delete') && $quotation->company_id === $user->company_id;
    }

    public function approve(User $user, Quotation $quotation): bool
    {
        return $user->can('sales.quotations.approve') && $quotation->company_id === $user->company_id;
    }
}
