{!! view_render_event('bagisto.admin.catalog.product.edit.form.inventories.controls.before', ['product' => $product]) !!}

<div
    id="stock-inventories-panel"
    class="{{ $product->manage_stock ? '' : 'hidden' }}"
>
    @php
        $inventorySources = app(\Webkul\Inventory\Repositories\InventorySourceRepository::class)->findWhere(['status' => 1]);
        $hasSingleInventorySource = $inventorySources->count() === 1;
        $singleInventorySource = $hasSingleInventorySource ? $inventorySources->first() : null;
        $totalInventoryQty = $product->inventories->sum('qty');
    @endphp

    <!-- Panel Content -->
    <div class="mb-5 text-sm text-gray-600 dark:text-gray-300">
        <div class="relative mb-2.5 flex items-center">
            <span class="inline-block rounded-full bg-yellow-500 p-1.5 ltr:mr-1.5 rtl:ml-1.5"></span>

            @lang('admin::app.catalog.products.edit.inventories.pending-ordered-qty', [
                'qty' => $product->ordered_inventories->pluck('qty')->first() ?? 0,
            ])
            
            <i class="icon-information peer rounded-full bg-gray-700 text-lg font-bold text-white transition-all hover:bg-gray-800 ltr:ml-2.5 rtl:mr-2.5"></i>

            <div class="absolute bottom-6 hidden rounded-lg bg-black p-2.5 text-sm italic text-white opacity-80 peer-hover:block">
                @lang('admin::app.catalog.products.edit.inventories.pending-ordered-qty-info')
            </div>
        </div>

        <p class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-sm text-blue-700 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-200">
            Renseignez ici la quantite disponible de votre produit.
        </p>
    </div>

    @if ($inventorySources->isEmpty())
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
            Aucun depot actif n'est configure. Activez au moins une source d'inventaire pour saisir la quantite produit.
        </div>
    @endif

    @if ($hasSingleInventorySource)
        <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-950">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Depot utilise
            </p>

            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ $singleInventorySource->name }}
            </p>

            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Quantite totale actuelle: {{ $totalInventoryQty }}
            </p>
        </div>
    @endif

    @foreach ($inventorySources as $inventorySource)
        @php
            $qty = old('inventories[' . $inventorySource->id . ']')
                ?: ($product->inventories->where('inventory_source_id', $inventorySource->id)->pluck('qty')->first() ?? 0);
        @endphp

        <x-admin::form.control-group>
            <x-admin::form.control-group.label v-pre>
                {{ $hasSingleInventorySource ? 'Quantite disponible' : $inventorySource->name }}
            </x-admin::form.control-group.label>

            @if ($hasSingleInventorySource)
                <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                    Saisie du stock pour {{ $inventorySource->name }}.
                </p>
            @endif

            <x-admin::form.control-group.control
                type="number"
                :name="'inventories[' . $inventorySource->id . ']'"
                :rules="'numeric|min:0'"
                :value="$qty"
                :label="$hasSingleInventorySource ? 'Quantite disponible' : $inventorySource->name"
                min="0"
                step="1"
            />

            <x-admin::form.control-group.error :control-name="'inventories[' . $inventorySource->id . ']'" />
        </x-admin::form.control-group>
    @endforeach
</div>

{!! view_render_event('bagisto.admin.catalog.product.edit.form.inventories.controls.after', ['product' => $product]) !!}

@pushOnce('scripts')
    <script type="module">
        const initializeStockInventoriesPanel = () => {
            const manageStockElement = document.getElementById('manage_stock');
            const stockInventoriesPanel = document.getElementById('stock-inventories-panel');

            if (! manageStockElement || ! stockInventoriesPanel) {
                return;
            }

            const togglePanel = () => {
                stockInventoriesPanel.classList.toggle('hidden', ! manageStockElement.checked);
            };

            togglePanel();

            manageStockElement.addEventListener('change', togglePanel);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeStockInventoriesPanel, { once: true });
        } else {
            initializeStockInventoriesPanel();
        }
    </script>
@endpushOnce