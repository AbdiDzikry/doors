<?php

namespace App\Livewire\Meeting;

use Livewire\Component;
use App\Models\ExternalParticipant;

class SearchExternalParticipants extends Component
{
    public $search = '';
    public $selectedParticipants = [];

    public function mount($initialParticipants = [])
    {
        $this->selectedParticipants = $initialParticipants;
    }

    public function render()
    {
        $externalParticipants = collect();
        if (strlen($this->search) >= 2) {
            $externalParticipants = ExternalParticipant::where('name', 'like', '%' . $this->search . '%')
                                                     ->orWhere('email', 'like', '%' . $this->search . '%')
                                                     ->orWhere('company', 'like', '%' . $this->search . '%')
                                                     ->take(5)
                                                     ->get();
        }

        $selectedExternalParticipants = ExternalParticipant::whereIn('id', $this->selectedParticipants)->get();

        return view('livewire.meeting.search-external-participants', [
            'externalParticipants' => $externalParticipants,
            'selectedExternalParticipants' => $selectedExternalParticipants,
        ]);
    }

    public function addParticipant($participantId)
    {
        if (!in_array($participantId, $this->selectedParticipants)) {
            $this->selectedParticipants[] = $participantId;
            $this->dispatch('externalParticipantsUpdated', $this->selectedParticipants);
        }
    }

    public function removeParticipant($participantId)
    {
        $this->selectedParticipants = array_diff($this->selectedParticipants, [$participantId]);
        $this->dispatch('externalParticipantsUpdated', $this->selectedParticipants);
    }
}