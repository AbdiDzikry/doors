<div>
    <!-- Dropdown for adding Pantry Items -->
    <div class="mb-4 flex items-center space-x-2">
        <select wire:model="itemToAdd" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
            <option value="">Select a pantry item</option>
            @foreach ($pantryItems as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
        </select>
        <button type="button" wire:click="addPantryItem" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Add
        </button>
    </div>

    <!-- Selected Items -->
    <div class="space-y-3">
        <h3 class="text-md font-semibold text-gray-700">Selected Items</h3>
        @if ($selectedItems->isNotEmpty())
            @foreach ($selectedItems as $item)
                <div class="flex items-center justify-between bg-white p-3 rounded-md shadow-sm">
                    <span class="text-sm font-medium text-gray-800">{{ $item->name }}</span>
                    <div class="flex items-center space-x-3">
                        <label for="quantity-{{ $item->id }}" class="text-sm">Qty:</label>
                        <input type="number" id="quantity-{{ $item->id }}" wire:model.live="selectedPantryItems.{{ $item->id }}" min="1" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                        <button type="button" wire:click="removePantryItem({{ $item->id }})" class="text-red-500 hover:text-red-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-4 px-3 bg-gray-50 rounded-md">
                <p class="text-sm text-gray-500">No pantry items added yet.</p>
            </div>
        @endif
    </div>
</div>