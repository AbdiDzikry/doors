<?php

namespace App\Livewire\Meeting;

use Livewire\Component;
use App\Models\User;

class SearchInternalParticipants extends Component
{
    public $search = '';
    public $selectedParticipants = [];

    public function mount($initialParticipants = [])
    {
        $this->selectedParticipants = $initialParticipants;
    }

    public function render()
    {
        $users = collect();
        if (strlen($this->search) >= 2) {
            $users = User::where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('email', 'like', '%' . $this->search . '%')
                         ->orWhere('npk', 'like', '%' . $this->search . '%')
                         ->take(5)
                         ->get();
        }

        $selectedUsers = User::whereIn('id', $this->selectedParticipants)->get();

        return view('livewire.meeting.search-internal-participants', [
            'users' => $users,
            'selectedUsers' => $selectedUsers,
        ]);
    }

    public function addParticipant($userId)
    {
        if (!in_array($userId, $this->selectedParticipants)) {
            $this->selectedParticipants[] = $userId;
            $this->dispatch('internalParticipantsUpdated', $this->selectedParticipants);
        }
    }

    public function removeParticipant($userId)
    {
        $this->selectedParticipants = array_diff($this->selectedParticipants, [$userId]);
        $this->dispatch('internalParticipantsUpdated', $this->selectedParticipants);
    }
}