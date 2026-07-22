<?php

namespace Dev3bdulrahman\Sales\Policies;

use App\Models\User;
use Dev3bdulrahman\Sales\Models\Invoice;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sales.invoices.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->can('sales.invoices.view') && $invoice->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('sales.invoices.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can('sales.invoices.update') && $invoice->company_id === $user->company_id;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->can('sales.invoices.delete') && $invoice->company_id === $user->company_id;
    }

    public function approve(User $user, Invoice $invoice): bool
    {
        return $user->can('sales.invoices.approve') && $invoice->company_id === $user->company_id;
    }
}
