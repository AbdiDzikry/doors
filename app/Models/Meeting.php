<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Room;
use App\Models\User;
use App\Models\ExternalParticipant;
use App\Models\PantryItem;
use Carbon\Carbon;

class Meeting extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'room_id',
        'topic',
        'start_time',
        'end_time',
        'meeting_type',
        'priority_guest_id',
        'status',
    ];

    protected $appends = ['calculated_status'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function getCalculatedStatusAttribute()
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        if ($this->end_time && $this->end_time->isPast()) {
            return 'completed';
        }

        return 'scheduled';
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function meetingParticipants()
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    public function externalParticipants()
    {
        return $this->morphToMany(ExternalParticipant::class, 'participant', 'meeting_participants');
    }

    public function pantryOrders()
    {
        return $this->hasMany(PantryOrder::class);
    }

    public function recurringMeeting()
    {
        return $this->belongsTo(RecurringMeeting::class);
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
