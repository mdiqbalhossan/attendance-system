<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all students
        $students = Student::all();

        if ($students->isEmpty()) {
            $this->command->warn('No students found. Please run StudentSeeder first.');
            return;
        }

        // Get a user to be the recorder (assuming users exist)
        $recorder = User::first();

        if (!$recorder) {
            $this->command->warn('No users found. Creating a default user.');
            $recorder = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);
        }

        $this->command->info('Seeding attendance records...');

        // Generate attendance for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            // Skip weekends
            if (!$currentDate->isWeekend()) {
                foreach ($students as $student) {
                    // 85% chance of being present, 10% absent, 5% late
                    $rand = rand(1, 100);
                    
                    if ($rand <= 85) {
                        $status = 'Present';
                        $note = null;
                    } elseif ($rand <= 95) {
                        $status = 'Absent';
                        $note = collect([
                            'Sick leave',
                            'Family emergency',
                            'Not feeling well',
                            'Doctor appointment',
                        ])->random();
                    } else {
                        $status = 'Late';
                        $note = collect([
                            'Traffic',
                            'Overslept',
                            'Transport issue',
                            null,
                        ])->random();
                    }

                    Attendance::create([
                        'student_id' => $student->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'status' => $status,
                        'note' => $note,
                        'recorded_by' => $recorder->id,
                    ]);
                }

                $this->command->info("Attendance created for {$currentDate->format('Y-m-d')}");
            }

            $currentDate->addDay();
        }

        $this->command->info('Attendance seeding completed!');
    }
}
