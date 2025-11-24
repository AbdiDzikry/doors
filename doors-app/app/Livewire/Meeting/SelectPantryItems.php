<?php

namespace App\Livewire\Meeting;

use Livewire\Component;
use App\Models\PantryItem;

class SelectPantryItems extends Component
{
    public $pantryItems;
    public $selectedPantryItems = []; // [pantry_item_id => quantity]
    public $itemToAdd = null;

    public function mount($initialPantryItems = [])
    {
        $this->pantryItems = PantryItem::all();
        foreach ($initialPantryItems as $item) {
            $this->selectedPantryItems[$item['pantry_item_id']] = $item['quantity'];
        }
    }

    public function render()
    {
        $selectedItems = PantryItem::whereIn('id', array_keys($this->selectedPantryItems))->get();

        return view('livewire.meeting.select-pantry-items', [
            'selectedItems' => $selectedItems
        ]);
    }

    public function addPantryItem()
    {
        if ($this->itemToAdd && !isset($this->selectedPantryItems[$this->itemToAdd])) {
            $this->selectedPantryItems[$this->itemToAdd] = 1; // Default quantity
            $this->dispatch('pantryOrdersUpdated', $this->selectedPantryItems);
        }
        $this->itemToAdd = null; // Reset dropdown
    }

    public function removePantryItem($pantryItemId)
    {
        unset($this->selectedPantryItems[$pantryItemId]);
        $this->dispatch('pantryOrdersUpdated', $this->selectedPantryItems);
    }

    public function updatedSelectedPantryItems($value, $key)
    {
        if ($value <= 0) {
            unset($this->selectedPantryItems[$key]);
        }
        $this->dispatch('pantryOrdersUpdated', $this->selectedPantryItems);
    }
}