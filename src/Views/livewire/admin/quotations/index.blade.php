<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('sales::sales.quotations') }}</h2>
            <p class="text-gray-500 text-sm mt-1">{{ __('sales::sales.manage_quotations') }}</p>
        </div>
        <button wire:click="openCreateModal"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>{{ __('sales::sales.add_quotation') }}</span>
        </button>
    </div>

    <!-- Filters Panel -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-4 mb-6 shadow-sm">
        <div class="flex flex-wrap items-end gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-gray-400 mb-1.5 uppercase">{{ __('sales::sales.search') }}</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('sales::sales.search_placeholder') }}"
                        class="w-full text-right pl-3 pr-10 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Customer Filter -->
            <div class="w-full sm:w-auto sm:min-w-[160px]">
                <label class="block text-xs font-bold text-gray-400 mb-1.5 uppercase">{{ __('sales::sales.customer') }}</label>
                <select wire:model.live="customerFilter"
                    class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                    <option value="">{{ __('sales::sales.select_customer') }}</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust->id }}">{{ $cust->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="w-full sm:w-auto sm:min-w-[160px]">
                <label class="block text-xs font-bold text-gray-400 mb-1.5 uppercase">{{ __('sales::sales.status') }}</label>
                <select wire:model.live="statusFilter"
                    class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                    <option value="">{{ __('sales::sales.status') }}</option>
                    <option value="draft">{{ __('sales::sales.draft') }}</option>
                    <option value="sent">{{ __('sales::sales.sent') }}</option>
                    <option value="accepted">{{ __('sales::sales.accepted') }}</option>
                    <option value="rejected">{{ __('sales::sales.rejected') }}</option>
                    <option value="expired">{{ __('sales::sales.expired') }}</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">{{ __('sales::sales.quotation_number') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">{{ __('sales::sales.customer') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">{{ __('sales::sales.quotation_date') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">{{ __('sales::sales.expiry_date') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">{{ __('sales::sales.grand_total') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">{{ __('sales::sales.status') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase text-center">{{ __('sales::sales.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($quotations as $quotation)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                                {{ $quotation->quotation_number }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $quotation->customer ? $quotation->customer->name : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $quotation->quotation_date->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $quotation->expiry_date->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($quotation->grand_total, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusClasses = [
                                        'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                        'sent' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400',
                                        'accepted' => 'bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400',
                                        'rejected' => 'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-400',
                                        'expired' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-950/30 dark:text-yellow-400',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClasses[$quotation->status] ?? $statusClasses['draft'] }}">
                                    {{ __('sales::sales.' . $quotation->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    @if($quotation->status !== 'accepted')
                                        <button wire:click="openConvertModal({{ $quotation->id }})" title="{{ __('sales::sales.convert_to_order') }}"
                                            class="p-2 text-gray-500 hover:text-purple-600 dark:hover:text-purple-400 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                        </button>
                                    @endif
                                    <button wire:click="openEditModal({{ $quotation->id }})" title="{{ __('sales::sales.edit_quotation') }}"
                                        class="p-2 text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button 
                                        wire:click="$dispatch('swal:confirm', { 
                                            title: '{{ __('sales::sales.delete') }}',
                                            text: '{{ __('sales::sales.delete_confirm') }}',
                                            onConfirm: 'delete',
                                            params: { id: {{ $quotation->id }} }
                                        })"
                                        title="{{ __('sales::sales.delete') }}"
                                        class="p-2 text-gray-500 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <span>{{ __('sales::sales.no_quotations') }}</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotations->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                {{ $quotations->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    <div x-data="{ open: @entangle('showFormModal') }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div @click="open = false" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-950/75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-middle bg-white dark:bg-gray-900 rounded-xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-100 dark:border-gray-800">
                <form wire:submit.prevent="save">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6 border-b border-gray-50 dark:border-gray-800 pb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ $quotationId ? __('sales::sales.edit_quotation') : __('sales::sales.add_quotation') }}
                            </h3>
                            <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Form Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('sales::sales.customer') }} *</label>
                                <select wire:model="customer_id" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                    <option value="">{{ __('sales::sales.select_customer') }}</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('sales::sales.quotation_number') }} *</label>
                                <input type="text" wire:model="quotation_number" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                @error('quotation_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('sales::sales.status') }} *</label>
                                <select wire:model="status" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                    <option value="draft">{{ __('sales::sales.draft') }}</option>
                                    <option value="sent">{{ __('sales::sales.sent') }}</option>
                                    <option value="accepted">{{ __('sales::sales.accepted') }}</option>
                                    <option value="rejected">{{ __('sales::sales.rejected') }}</option>
                                    <option value="expired">{{ __('sales::sales.expired') }}</option>
                                </select>
                                @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('sales::sales.quotation_date') }} *</label>
                                <input type="date" wire:model="quotation_date" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                @error('quotation_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('sales::sales.expiry_date') }} *</label>
                                <input type="date" wire:model="expiry_date" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                @error('expiry_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="mb-6 border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden">
                            <div class="bg-gray-50 dark:bg-gray-800 p-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
                                <span class="font-bold text-gray-800 dark:text-white">{{ __('sales::sales.items') }}</span>
                                <button type="button" wire:click="addItem" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition-colors">
                                    {{ __('sales::sales.add_item') }}
                                </button>
                            </div>
                            <table class="w-full text-right">
                                <thead class="bg-gray-50 dark:bg-gray-800/50">
                                    <tr>
                                        <th class="px-4 py-2 text-xs font-bold text-gray-400 uppercase w-1/3">{{ __('sales::sales.product') }}</th>
                                        <th class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">{{ __('sales::sales.quantity') }}</th>
                                        <th class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">{{ __('sales::sales.unit_price') }}</th>
                                        <th class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">{{ __('sales::sales.tax_rate') }}</th>
                                        <th class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">{{ __('sales::sales.discount') }}</th>
                                        <th class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">{{ __('sales::sales.total') }}</th>
                                        <th class="px-4 py-2 text-xs font-bold text-gray-400 uppercase text-center"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $index => $item)
                                        <tr class="border-t border-gray-50 dark:border-gray-800">
                                            <td class="px-4 py-2">
                                                <select wire:model="items.{{ $index }}.product_id" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                                    <option value="">{{ __('sales::sales.select_product') }}</option>
                                                    @foreach($products as $p)
                                                        <option value="{{ $p->id }}">{{ $p->translated_name }}</option>
                                                    @endforeach
                                                </select>
                                                @error("items.{$index}.product_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" step="any" wire:model.live.debounce.300ms="items.{{ $index }}.quantity" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" step="any" wire:model.live.debounce.300ms="items.{{ $index }}.unit_price" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" step="any" wire:model.live.debounce.300ms="items.{{ $index }}.tax_rate" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" step="any" wire:model.live.debounce.300ms="items.{{ $index }}.discount_amount" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                            </td>
                                            <td class="px-4 py-2 text-sm font-semibold text-gray-800 dark:text-white">
                                                {{ number_format($item['total'] ?? 0, 2) }}
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                <button type="button" wire:click="removeItem({{ $index }})" class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Block -->
                        <div class="flex justify-end gap-6 mb-6">
                            <div class="w-80 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl space-y-3">
                                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                    <span>{{ __('sales::sales.subtotal') }}:</span>
                                    <span>{{ number_format($subtotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                    <span>{{ __('sales::sales.discount_total') }}:</span>
                                    <span>{{ number_format($discount_total, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                    <span>{{ __('sales::sales.tax_total') }}:</span>
                                    <span>{{ number_format($tax_total, 2) }}</span>
                                </div>
                                <div class="flex justify-between font-bold text-gray-900 dark:text-white border-t border-gray-200 dark:border-gray-700 pt-3">
                                    <span>{{ __('sales::sales.grand_total') }}:</span>
                                    <span>{{ number_format($grand_total, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('sales::sales.notes') }}</label>
                            <textarea wire:model="notes" rows="3" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"></textarea>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-2">
                        <button type="button" @click="open = false" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            {{ __('sales::sales.cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors">
                            {{ __('sales::sales.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Convert to Order Modal -->
    <div x-data="{ open: @entangle('showConvertModal') }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div @click="open = false" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-950/75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-middle bg-white dark:bg-gray-900 rounded-xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 dark:border-gray-800">
                <form wire:submit.prevent="convert">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6 border-b border-gray-50 dark:border-gray-800 pb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ __('sales::sales.convert_to_order') }}
                            </h3>
                            <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('sales::sales.order_number') }} *</label>
                                <input type="text" wire:model="order_number" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                @error('order_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('sales::sales.order_date') }} *</label>
                                <input type="date" wire:model="order_date" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                @error('order_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('sales::sales.delivery_date') }}</label>
                                <input type="date" wire:model="delivery_date" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                @error('delivery_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('sales::sales.notes') }}</label>
                                <textarea wire:model="conversion_notes" rows="3" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-2">
                        <button type="button" @click="open = false" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            {{ __('sales::sales.cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold transition-colors">
                            {{ __('sales::sales.convert_to_order') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
