<?php

namespace App\Livewire\Meeting;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Meeting;
use Illuminate\Support\Facades\Auth;

class RecurringMeetingsList extends Component
{
    public $recurringMeetings;
    public function mount()
    {
        $this->loadRecurringMeetings();
    }

    #[On('recurringMeetingsTabActivated')]
    public function loadRecurringMeetings()
    {
        $this->recurringMeetings = Meeting::where('is_recurring', true)
            ->whereNull('parent_meeting_id') // Only fetch parent meetings
            ->where('user_id', Auth::id()) // Only fetch recurring meetings created by the authenticated user
            ->with(['children' => function ($query) {
                $query->with('room')->orderBy('start_time', 'asc');
            }, 'room'])
            ->orderBy('start_time', 'asc')
            ->get();
    }

    #[On('confirmMeeting')]
    public function confirmMeeting($meetingId)
    {
        $meeting = Meeting::find($meetingId);
        if ($meeting && ($meeting->user_id === Auth::id() || ($meeting->parent && $meeting->parent->user_id === Auth::id()))) {
            $meeting->update(['confirmation_status' => 'confirmed']);
            $this->loadRecurringMeetings();
        }
    }

    #[On('cancelMeeting')]
    public function cancelMeeting($meetingId)
    {
        $meeting = Meeting::find($meetingId);
        if ($meeting && ($meeting->user_id === Auth::id() || ($meeting->parent && $meeting->parent->user && $meeting->parent->user->id === Auth::id()))) {
            $meeting->update(['status' => 'cancelled']);
            $this->loadRecurringMeetings();
        }
    }

    public function render()
    {
        return view('livewire.meeting.recurring-meetings-list-view');
    }
}
