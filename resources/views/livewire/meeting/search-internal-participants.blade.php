<div class="p-4">
    <input type="text" wire:model.live="search" placeholder="Search internal participants..." class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">

    @if ($this->search !== '')
        <div class="border rounded max-h-60 overflow-y-auto mb-4">
            @forelse ($users as $user)
                <div class="flex items-center justify-between p-2 border-b last:border-b-0">
                    <span>{{ $user->name }} ({{ $user->email }})</span>
                    @if (!in_array($user->id, $selectedParticipants))
                        <button type="button" wire:click="addParticipant({{ $user->id }})" class="bg-blue-500 hover:bg-blue-700 text-white text-xs py-1 px-2 rounded">Add</button>
                    @else
                        <span class="text-green-500 text-xs">Added</span>
                    @endif
                </div>
            @empty
                <div class="p-2 text-gray-500">No internal participants found.</div>
            @endforelse
        </div>
    @endif

    <h3 class="font-bold mb-2">Selected Participants:</h3>
    @if ($selectedUsers->isNotEmpty())
        <div class="border rounded max-h-40 overflow-y-auto">
            @foreach ($selectedUsers as $participant)
                <div class="flex items-center justify-between p-2 border-b last:border-b-0">
                    <span>{{ $participant->name }} ({{ $participant->email }})</span>
                    <button type="button" wire:click="removeParticipant({{ $participant->id }})" class="bg-red-500 hover:bg-red-700 text-white text-xs py-1 px-2 rounded">Remove</button>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500">No internal participants selected.</p>
    @endif
</div>