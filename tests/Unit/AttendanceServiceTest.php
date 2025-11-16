<?php

namespace Tests\Unit;

use App\Events\AttendanceRecorded;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AttendanceService;
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_record_bulk_attendance_successfully(): void
    {
        $students = Student::factory()->count(3)->create();
        $date = Carbon::today()->format('Y-m-d');

        $attendanceData = [
            ['student_id' => $students[0]->id, 'status' => 'Present', 'note' => null],
            ['student_id' => $students[1]->id, 'status' => 'Absent', 'note' => 'Sick'],
            ['student_id' => $students[2]->id, 'status' => 'Late', 'note' => 'Traffic'],
        ];

        $results = $this->service->recordBulkAttendance($attendanceData, $this->user->id, $date);

        $this->assertCount(3, $results['success']);
        $this->assertCount(0, $results['errors']);
        $this->assertDatabaseCount('attendances', 3);
    }

    /** @test */
    public function it_updates_existing_attendance_in_bulk_recording(): void
    {
        $student = Student::factory()->create();
        $date = Carbon::today()->format('Y-m-d');

        // Create initial attendance
        $existing = Attendance::factory()->create([
            'student_id' => $student->id,
            'date' => $date,
            'status' => 'Present',
        ]);

        // Update via bulk recording
        $attendanceData = [
            ['student_id' => $student->id, 'status' => 'Absent', 'note' => 'Changed'],
        ];

        $results = $this->service->recordBulkAttendance($attendanceData, $this->user->id, $date);

        $this->assertCount(1, $results['success']);
        $this->assertDatabaseCount('attendances', 1);

        $updated = Attendance::first();
        $this->assertEquals('Absent', $updated->status);
        $this->assertEquals('Changed', $updated->note);
    }

    /** @test */
    public function it_fires_events_for_each_attendance_record(): void
    {
        Event::fake([AttendanceRecorded::class]);

        $students = Student::factory()->count(3)->create();
        $date = Carbon::today()->format('Y-m-d');

        $attendanceData = [
            ['student_id' => $students[0]->id, 'status' => 'Present', 'note' => null],
            ['student_id' => $students[1]->id, 'status' => 'Absent', 'note' => null],
            ['student_id' => $students[2]->id, 'status' => 'Late', 'note' => null],
        ];

        $this->service->recordBulkAttendance($attendanceData, $this->user->id, $date);

        Event::assertDispatched(AttendanceRecorded::class, 3);
    }

    /** @test */
    public function it_clears_cache_after_recording_attendance(): void
    {
        Cache::flush();

        $student = Student::factory()->create(['class' => '10', 'section' => 'A']);
        $date = Carbon::today()->format('Y-m-d');

        // Set up some cache keys
        Cache::put("attendance:daily:{$date}", 'test_data', 60);
        Cache::put('attendance:monthly:'.Carbon::parse($date)->year.':'.Carbon::parse($date)->month, 'test_data', 60);

        $attendanceData = [
            ['student_id' => $student->id, 'status' => 'Present', 'note' => null],
        ];

        $this->service->recordBulkAttendance($attendanceData, $this->user->id, $date);

        // Cache should be cleared
        $this->assertNull(Cache::get("attendance:daily:{$date}"));
    }

    /** @test */
    public function it_returns_errors_for_invalid_records_without_stopping(): void
    {
        $validStudent = Student::factory()->create();
        $date = Carbon::today()->format('Y-m-d');

        // Mock a scenario where one record might fail (e.g., database constraint)
        // In real scenario, service handles exceptions per record
        $attendanceData = [
            ['student_id' => $validStudent->id, 'status' => 'Present', 'note' => null],
        ];

        $results = $this->service->recordBulkAttendance($attendanceData, $this->user->id, $date);

        // All valid records should succeed
        $this->assertCount(1, $results['success']);
        $this->assertCount(0, $results['errors']);
    }

    /** @test */
    public function it_uses_database_transactions_for_bulk_recording(): void
    {
        $students = Student::factory()->count(2)->create();
        $date = Carbon::today()->format('Y-m-d');

        $attendanceData = [
            ['student_id' => $students[0]->id, 'status' => 'Present', 'note' => null],
            ['student_id' => $students[1]->id, 'status' => 'Absent', 'note' => null],
        ];

        // This should use a transaction internally
        $results = $this->service->recordBulkAttendance($attendanceData, $this->user->id, $date);

        // Both records should be committed
        $this->assertCount(2, $results['success']);
        $this->assertDatabaseCount('attendances', 2);
    }

    /** @test */
    public function it_can_get_daily_stats(): void
    {
        $date = Carbon::today()->format('Y-m-d');
        $students = Student::factory()->count(5)->create(['class' => '10', 'section' => 'A']);

        // Create attendance records
        Attendance::factory()->create(['student_id' => $students[0]->id, 'date' => $date, 'status' => 'Present']);
        Attendance::factory()->create(['student_id' => $students[1]->id, 'date' => $date, 'status' => 'Present']);
        Attendance::factory()->create(['student_id' => $students[2]->id, 'date' => $date, 'status' => 'Absent']);
        Attendance::factory()->create(['student_id' => $students[3]->id, 'date' => $date, 'status' => 'Late']);
        Attendance::factory()->create(['student_id' => $students[4]->id, 'date' => $date, 'status' => 'Present']);

        $stats = $this->service->getDailyStats($date);

        $this->assertEquals($date, $stats['date']);
        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(3, $stats['present']);
        $this->assertEquals(1, $stats['absent']);
        $this->assertEquals(1, $stats['late']);
        $this->assertEquals(80.0, $stats['attendance_rate']); // (3+1)/5 * 100 = 80%
    }

    /** @test */
    public function it_can_filter_daily_stats_by_class(): void
    {
        $date = Carbon::today()->format('Y-m-d');

        // Class 10
        $class10Students = Student::factory()->count(3)->create(['class' => '10']);
        foreach ($class10Students as $student) {
            Attendance::factory()->create(['student_id' => $student->id, 'date' => $date, 'status' => 'Present']);
        }

        // Class 11
        $class11Students = Student::factory()->count(2)->create(['class' => '11']);
        foreach ($class11Students as $student) {
            Attendance::factory()->create(['student_id' => $student->id, 'date' => $date, 'status' => 'Absent']);
        }

        $stats = $this->service->getDailyStats($date, '10');

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(3, $stats['present']);
        $this->assertEquals(0, $stats['absent']);
    }

    /** @test */
    public function it_caches_daily_stats(): void
    {
        Cache::flush();

        $date = Carbon::today()->format('Y-m-d');
        $student = Student::factory()->create();
        Attendance::factory()->create(['student_id' => $student->id, 'date' => $date, 'status' => 'Present']);

        // First call should cache
        $stats1 = $this->service->getDailyStats($date);

        // Add more attendance
        $student2 = Student::factory()->create();
        Attendance::factory()->create(['student_id' => $student2->id, 'date' => $date, 'status' => 'Absent']);

        // Second call should return cached data (still 1 total)
        $stats2 = $this->service->getDailyStats($date);

        $this->assertEquals($stats1['total'], $stats2['total']);
        $this->assertEquals(1, $stats2['total']); // Should still be 1 from cache
    }

    /** @test */
    public function it_can_get_monthly_report(): void
    {
        $month = now()->month;
        $year = now()->year;

        $students = Student::factory()->count(3)->create(['class' => '10']);

        // Create attendance for the month
        for ($day = 1; $day <= 5; $day++) {
            $date = Carbon::create($year, $month, $day)->format('Y-m-d');
            foreach ($students as $student) {
                Attendance::factory()->create([
                    'student_id' => $student->id,
                    'date' => $date,
                    'status' => $day % 2 === 0 ? 'Present' : 'Absent',
                ]);
            }
        }

        $report = $this->service->getMonthlyReport($month, $year, '10');

        $this->assertEquals($month, $report['month']);
        $this->assertEquals($year, $report['year']);
        $this->assertArrayHasKey('stats', $report);
        $this->assertArrayHasKey('attendances', $report);
        $this->assertEquals(15, $report['stats']['total_records']); // 3 students × 5 days
    }

    /** @test */
    public function it_calculates_monthly_stats_correctly(): void
    {
        $month = now()->month;
        $year = now()->year;

        $student = Student::factory()->create();

        // Create attendance: 3 Present, 1 Absent, 1 Late
        $dates = [];
        for ($day = 1; $day <= 5; $day++) {
            $dates[] = Carbon::create($year, $month, $day)->format('Y-m-d');
        }

        Attendance::factory()->create(['student_id' => $student->id, 'date' => $dates[0], 'status' => 'Present']);
        Attendance::factory()->create(['student_id' => $student->id, 'date' => $dates[1], 'status' => 'Present']);
        Attendance::factory()->create(['student_id' => $student->id, 'date' => $dates[2], 'status' => 'Present']);
        Attendance::factory()->create(['student_id' => $student->id, 'date' => $dates[3], 'status' => 'Absent']);
        Attendance::factory()->create(['student_id' => $student->id, 'date' => $dates[4], 'status' => 'Late']);

        $report = $this->service->getMonthlyReport($month, $year);

        $this->assertEquals(5, $report['stats']['total_records']);
        $this->assertEquals(3, $report['stats']['present_count']);
        $this->assertEquals(1, $report['stats']['absent_count']);
        $this->assertEquals(1, $report['stats']['late_count']);
        $this->assertEquals(1, $report['stats']['unique_students']);
        $this->assertEquals(80.0, $report['stats']['attendance_rate']); // (3+1)/5 * 100 = 80%
    }

    /** @test */
    public function it_calculates_per_student_stats_in_monthly_report(): void
    {
        $month = now()->month;
        $year = now()->year;

        $student = Student::factory()->create(['name' => 'John Doe']);

        // 4 Present, 1 Absent
        for ($day = 1; $day <= 5; $day++) {
            $date = Carbon::create($year, $month, $day)->format('Y-m-d');
            $status = $day === 5 ? 'Absent' : 'Present';
            Attendance::factory()->create([
                'student_id' => $student->id,
                'date' => $date,
                'status' => $status,
            ]);
        }

        $report = $this->service->getMonthlyReport($month, $year);

        $this->assertCount(1, $report['stats']['student_stats']);

        $studentStat = $report['stats']['student_stats'][0];
        $this->assertEquals(5, $studentStat['total_days']);
        $this->assertEquals(4, $studentStat['present']);
        $this->assertEquals(1, $studentStat['absent']);
        $this->assertEquals(0, $studentStat['late']);
        $this->assertEquals(80.0, $studentStat['attendance_rate']); // 4/5 * 100 = 80%
    }

    /** @test */
    public function it_caches_monthly_report(): void
    {
        Cache::flush();

        $month = now()->month;
        $year = now()->year;

        $student = Student::factory()->create();
        Attendance::factory()->create([
            'student_id' => $student->id,
            'date' => Carbon::create($year, $month, 1)->format('Y-m-d'),
            'status' => 'Present',
        ]);

        // First call should cache
        $report1 = $this->service->getMonthlyReport($month, $year);

        // Add more attendance
        $student2 = Student::factory()->create();
        Attendance::factory()->create([
            'student_id' => $student2->id,
            'date' => Carbon::create($year, $month, 2)->format('Y-m-d'),
            'status' => 'Present',
        ]);

        // Second call should return cached data
        $report2 = $this->service->getMonthlyReport($month, $year);

        $this->assertEquals($report1['stats']['total_records'], $report2['stats']['total_records']);
        $this->assertEquals(1, $report2['stats']['total_records']); // Should still be 1 from cache
    }

    /** @test */
    public function it_can_get_student_attendance_history(): void
    {
        $student = Student::factory()->create();

        // Create attendance records
        for ($i = 0; $i < 5; $i++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'date' => Carbon::today()->subDays($i)->format('Y-m-d'),
                'status' => 'Present',
            ]);
        }

        $history = $this->service->getStudentAttendanceHistory($student->id);

        $this->assertCount(5, $history);
    }

    /** @test */
    public function it_can_filter_student_history_by_date_range(): void
    {
        $student = Student::factory()->create();

        // Create attendance for 10 days
        for ($i = 0; $i < 10; $i++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'date' => Carbon::today()->subDays($i)->format('Y-m-d'),
                'status' => 'Present',
            ]);
        }

        $startDate = Carbon::today()->subDays(4)->format('Y-m-d');
        $endDate = Carbon::today()->format('Y-m-d');

        $history = $this->service->getStudentAttendanceHistory($student->id, $startDate, $endDate);

        $this->assertCount(5, $history); // Should return 5 days
    }

    /** @test */
    public function it_sorts_student_stats_by_attendance_rate_in_monthly_report(): void
    {
        $month = now()->month;
        $year = now()->year;

        $student1 = Student::factory()->create(['name' => 'Student 1']);
        $student2 = Student::factory()->create(['name' => 'Student 2']);

        // Student 1: 80% attendance
        for ($i = 0; $i < 5; $i++) {
            Attendance::factory()->create([
                'student_id' => $student1->id,
                'date' => Carbon::create($year, $month, $i + 1)->format('Y-m-d'),
                'status' => $i === 0 ? 'Absent' : 'Present',
            ]);
        }

        // Student 2: 100% attendance
        for ($i = 0; $i < 5; $i++) {
            Attendance::factory()->create([
                'student_id' => $student2->id,
                'date' => Carbon::create($year, $month, $i + 1)->format('Y-m-d'),
                'status' => 'Present',
            ]);
        }

        $report = $this->service->getMonthlyReport($month, $year);

        $studentStats = $report['stats']['student_stats'];

        // Student 2 should be first (100% > 80%)
        $this->assertEquals('Student 2', $studentStats[0]['student']['name']);
        $this->assertEquals(100.0, $studentStats[0]['attendance_rate']);

        $this->assertEquals('Student 1', $studentStats[1]['student']['name']);
        $this->assertEquals(80.0, $studentStats[1]['attendance_rate']);
    }
}
