<?php

namespace App\Livewire\Meeting;

use Livewire\Component;
use App\Models\Room;
use App\Models\PriorityGuest;
use App\Models\PantryItem;
use App\Models\User;
use App\Models\ExternalParticipant;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\PantryOrder;
use App\Models\RecurringMeeting;
use App\Mail\MeetingInvitation;
use App\Services\IcsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request; // Although not directly used as a type-hinted parameter in Livewire methods, it's good to have for context if needed.

class BookingForm extends Component
{
    // Form properties
    public $selectedRoomId;
    public $room_id;
    public $topic;
    public $start_time;
    public $duration = 60; // Default duration
    public $priority_guest_id;
    public $recurring = false;
    public $frequency = 'daily';
    public $ends_at;

    // Data for dropdowns/lists
    public $rooms;
    public $priorityGuests;
    public $defaultMeetingDurationValue = 60; // Default value for duration select

    // Participants and Pantry Items
    public $internalParticipants = []; // Array of user IDs
    public $externalParticipants = []; // Array of external participant IDs
    public $pantryOrders = []; // Array of ['pantry_item_id' => id, 'quantity' => qty]

    public $current_meeting;
    public $selectedRoom;

    // Listeners for child components
    protected $listeners = [
        'internalParticipantsUpdated' => 'updateInternalParticipants',
        'externalParticipantsUpdated' => 'updateExternalParticipants',
        'pantryOrdersUpdated' => 'updatePantryOrders',
    ];
    public function mount($selectedRoomId = null)
    {
        $this->selectedRoomId = $selectedRoomId;
        $this->room_id = $selectedRoomId; // Pre-select room if provided

        $this->rooms = Room::all();
        $this->priorityGuests = PriorityGuest::all();
        $currentTime = now();
        $minute = $currentTime->minute;
        $remainder = $minute % 15;

        if ($remainder !== 0) {
            $currentTime->addMinutes(15 - $remainder);
        }
        $this->start_time = $currentTime->format('Y-m-d\TH:i'); // Default to nearest 15-minute interval
        $this->ends_at = now()->addDays(7)->format('Y-m-d'); // Default end date for recurring

        // Fetch selected room and current meeting logic
        $this->selectedRoom = Room::find($this->selectedRoomId);
        $this->current_meeting = null;
        if ($this->selectedRoom) {
            $now = now();
            $this->current_meeting = $this->selectedRoom->meetings()
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->where('status', '!=', 'cancelled')
                ->first();
        }
    }

    public function render()
    {
        // $selectedRoom is now a public property, no need to re-fetch
        return view('livewire.meeting.booking-form', [
            // 'selectedRoom' => $this->selectedRoom, // No longer needed here
            'rooms' => $this->rooms,
            'priorityGuests' => $this->priorityGuests,
            'defaultMeetingDurationValue' => $this->defaultMeetingDurationValue,
        ]);
    }

    public function submitForm()
    {
        $this->validate([
            'room_id' => 'required|exists:rooms,id',
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
            'duration' => 'required|integer|min:1',
            'priority_guest_id' => 'nullable|exists:priority_guests,id',
            'recurring' => 'nullable|boolean',
            'frequency' => 'required_if:recurring,true|string',
            'ends_at' => 'required_if:recurring,true|date|after:start_time',
        ]);

        $newStartTime = new \DateTime($this->start_time);
        $newEndTime = (clone $newStartTime)->add(new \DateInterval('PT' . $this->duration . 'M'));

        DB::beginTransaction();
        try {
            if ($this->recurring) {
                $startDate = new \DateTime($this->start_time);
                $endDate = new \DateTime($this->ends_at);
                $interval = new \DateInterval($this->getRecurringInterval($this->frequency));
                $period = new \DatePeriod($startDate, $interval, $endDate);

                foreach ($period as $date) {
                    $recurringStartTime = $date;
                    $recurringEndTime = (clone $recurringStartTime)->add(new \DateInterval('PT' . $this->duration . 'M'));
                    if (!$this->isRoomAvailable($recurringStartTime->format('Y-m-d H:i:s'), $recurringEndTime->format('Y-m-d H:i:s'), $this->room_id)) {
                        throw new \Exception('The room is not available for the recurring schedule on ' . $recurringStartTime->format('d-m-Y H:i'));
                    }
                }

                // If all recurring slots are available, proceed to create them
                $recurringMeeting = RecurringMeeting::create([
                    'frequency' => $this->frequency,
                    'ends_at' => $this->ends_at,
                ]);

                foreach ($period as $date) {
                     $recurringStartTime = $date;
                     $recurringEndTime = (clone $recurringStartTime)->add(new \DateInterval('PT' . $this->duration . 'M'));
                    $meeting = Meeting::create([
                        'room_id' => $this->room_id,
                        'topic' => $this->topic,
                        'start_time' => $recurringStartTime,
                        'end_time' => $recurringEndTime,
                        'priority_guest_id' => $this->priority_guest_id,
                        'meeting_type' => 'recurring',
                        'recurring_meeting_id' => $recurringMeeting->id,
                        'user_id' => auth()->id(),
                        'status' => 'scheduled',
                    ]);
                    $this->attachParticipantsAndPantryOrders($meeting);
                    $this->sendMeetingInvitation($meeting);
                    \App\Events\MeetingStatusUpdated::dispatch($meeting->room);
                }

            } else {
                // Single meeting validation
                if (!$this->isRoomAvailable($newStartTime->format('Y-m-d H:i:s'), $newEndTime->format('Y-m-d H:i:s'), $this->room_id)) {
                     throw new \Exception('The selected room is not available at the chosen time.');
                }

                $meeting = Meeting::create([
                    'room_id' => $this->room_id,
                    'topic' => $this->topic,
                    'start_time' => $newStartTime,
                    'end_time' => $newEndTime,
                    'priority_guest_id' => $this->priority_guest_id,
                    'meeting_type' => 'non-recurring',
                    'user_id' => auth()->id(),
                    'status' => 'scheduled',
                ]);
                $this->attachParticipantsAndPantryOrders($meeting);
                $this->sendMeetingInvitation($meeting);
                \App\Events\MeetingStatusUpdated::dispatch($meeting->room);
            }

            DB::commit();
            session()->flash('success', 'Meeting scheduled successfully!');
            return redirect()->route('meeting.meeting-lists.index');
        } catch (\Exception $e) {
            DB::rollBack();
            // Use Livewire's validation error system to show the message on the form
            $this->addError('room_id', $e->getMessage());
        }
    }

    private function isRoomAvailable(string $startTime, string $endTime, int $roomId, ?int $excludeMeetingId = null): bool
    {
        $query = Meeting::where('room_id', $roomId)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // Case 1: New meeting starts during an existing meeting
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeMeetingId) {
            $query->where('id', '!=', $excludeMeetingId);
        }

        return !$query->exists();
    }


    private function attachParticipantsAndPantryOrders(Meeting $meeting)
    {
        // Attach Internal Participants
        foreach ($this->internalParticipants as $userId) {
            MeetingParticipant::create([
                'meeting_id' => $meeting->id,
                'participant_id' => $userId,
                'participant_type' => User::class,
            ]);
        }

        // Attach External Participants
        foreach ($this->externalParticipants as $participantId) {
            MeetingParticipant::create([
                'meeting_id' => $meeting->id,
                'participant_id' => $participantId,
                'participant_type' => ExternalParticipant::class,
            ]);
        }

        // Create Pantry Orders
        foreach ($this->pantryOrders as $order) {
            if (!empty($order['pantry_item_id']) && !empty($order['quantity'])) {
                PantryOrder::create([
                    'meeting_id' => $meeting->id,
                    'pantry_item_id' => $order['pantry_item_id'],
                    'quantity' => $order['quantity'],
                    'status' => 'pending',
                ]);
            }
        }
    }

    private function sendMeetingInvitation(Meeting $meeting)
    {
        $icsService = new IcsService();
        $icsContent = $icsService->generateIcsFile($meeting);

        $participants = collect();
        if ($meeting->user) {
            $participants->push($meeting->user);
        }
        $participants = $participants->merge($meeting->meetingParticipants->map(function ($mp) {
            return $mp->user ?? $mp->externalParticipant;
        })->filter());

        foreach ($participants as $participant) {
            if ($participant->email) {
                Mail::to($participant->email)->send(new MeetingInvitation($meeting, $icsContent));
            }
        }
    }

    private function getRecurringInterval(string $pattern): string
    {
        return match ($pattern) {
            'daily' => 'P1D',
            'weekly' => 'P1W',
            'monthly' => 'P1M',
            default => 'P1D',
        };
    }

    // Listener methods to update participants and pantry orders from child components
    public function updateInternalParticipants($participants)
    {
        $this->internalParticipants = $participants;
    }

    public function updateExternalParticipants($participants)
    {
        $this->externalParticipants = $participants;
    }

    public function updatePantryOrders($orders)
    {
        $this->pantryOrders = $orders;
    }
}
