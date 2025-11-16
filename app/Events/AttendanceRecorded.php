<?php

namespace App\Events;

use App\Models\Attendance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The attendance instance.
     *
     * @var \App\Models\Attendance
     */
    public $attendance;

    /**
     * Create a new event instance.
     */
    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }
}
