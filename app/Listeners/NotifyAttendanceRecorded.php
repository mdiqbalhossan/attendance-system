<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyAttendanceRecorded
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AttendanceRecorded $event): void
    {
        $attendance = $event->attendance;

        // Load relationships if not already loaded
        $attendance->load(['student', 'recordedBy']);

        // Log the attendance record
        Log::info('Attendance recorded', [
            'student_id' => $attendance->student_id,
            'student_name' => $attendance->student->name,
            'date' => $attendance->date->format('Y-m-d'),
            'status' => $attendance->status,
            'recorded_by' => $attendance->recordedBy->name,
        ]);

        // Here you can add additional notification logic:
        // - Send email to parents if student is absent
        // - Send notification to admin dashboard
        // - Send SMS notification
        // - Push notification to mobile app
        
        // Example: Notify if student is absent
        if ($attendance->status === 'Absent') {
            // You can implement email/SMS notification here
            Log::warning('Student marked absent', [
                'student' => $attendance->student->name,
                'date' => $attendance->date->format('Y-m-d'),
            ]);
            
            // Example notification implementation:
            // Notification::send($admins, new StudentAbsentNotification($attendance));
        }
    }
}
