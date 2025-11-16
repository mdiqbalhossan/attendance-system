<?php

namespace App\Services;

use App\Events\AttendanceRecorded;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Record bulk attendance for multiple students.
     *
     * @param array $attendanceData Array of attendance records
     * @param int $recordedBy User ID who is recording
     * @return array
     */
    public function recordBulkAttendance(array $attendanceData, int $recordedBy): array
    {
        $results = [
            'success' => [],
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($attendanceData as $data) {
                try {
                    $attendance = Attendance::updateOrCreate(
                        [
                            'student_id' => $data['student_id'],
                            'date' => $data['date'],
                        ],
                        [
                            'status' => $data['status'],
                            'note' => $data['note'] ?? null,
                            'recorded_by' => $recordedBy,
                        ]
                    );

                    $results['success'][] = $attendance;

                    // Fire event for each attendance record
                    event(new AttendanceRecorded($attendance));

                    // Clear cache for this student and date
                    $this->clearAttendanceCache($data['student_id'], $data['date']);
                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'student_id' => $data['student_id'] ?? null,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            return $results;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get monthly attendance report for a class.
     *
     * @param int $month
     * @param int $year
     * @param string|null $class
     * @param string|null $section
     * @return array
     */
    public function getMonthlyReport(
        int $month,
        int $year,
        ?string $class = null,
        ?string $section = null
    ): array {
        $cacheKey = "attendance:monthly:{$year}:{$month}";
        
        if ($class) {
            $cacheKey .= ":{$class}";
        }
        
        if ($section) {
            $cacheKey .= ":{$section}";
        }

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($month, $year, $class, $section) {
            $query = Attendance::query()
                ->with(['student', 'recordedBy'])
                ->byMonth($month, $year);

            if ($class) {
                $query->byClass($class);
            }

            if ($section) {
                $query->bySection($section);
            }

            $attendances = $query->get();

            // Calculate statistics
            $stats = $this->calculateMonthlyStats($attendances, $month, $year, $class, $section);

            return [
                'attendances' => $attendances,
                'stats' => $stats,
                'month' => $month,
                'year' => $year,
                'class' => $class,
                'section' => $section,
            ];
        });
    }

    /**
     * Calculate monthly attendance statistics.
     *
     * @param \Illuminate\Support\Collection $attendances
     * @param int $month
     * @param int $year
     * @param string|null $class
     * @param string|null $section
     * @return array
     */
    private function calculateMonthlyStats(
        $attendances,
        int $month,
        int $year,
        ?string $class = null,
        ?string $section = null
    ): array {
        $totalRecords = $attendances->count();
        $presentCount = $attendances->where('status', 'Present')->count();
        $absentCount = $attendances->where('status', 'Absent')->count();
        $lateCount = $attendances->where('status', 'Late')->count();

        // Get unique students
        $uniqueStudents = $attendances->pluck('student_id')->unique()->count();

        // Get working days in the month
        $workingDays = $this->getWorkingDaysInMonth($month, $year);

        // Calculate attendance rate
        $attendanceRate = $totalRecords > 0 
            ? round(($presentCount + $lateCount) / $totalRecords * 100, 2) 
            : 0;

        // Per student statistics
        $studentStats = [];
        
        foreach ($attendances->groupBy('student_id') as $studentId => $studentAttendances) {
            $student = $studentAttendances->first()->student;
            $studentStats[] = [
                'student' => $student,
                'total_days' => $studentAttendances->count(),
                'present' => $studentAttendances->where('status', 'Present')->count(),
                'absent' => $studentAttendances->where('status', 'Absent')->count(),
                'late' => $studentAttendances->where('status', 'Late')->count(),
                'attendance_rate' => $studentAttendances->count() > 0
                    ? round(
                        ($studentAttendances->where('status', 'Present')->count() + 
                         $studentAttendances->where('status', 'Late')->count()) / 
                        $studentAttendances->count() * 100,
                        2
                    )
                    : 0,
            ];
        }

        return [
            'total_records' => $totalRecords,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
            'unique_students' => $uniqueStudents,
            'working_days' => $workingDays,
            'attendance_rate' => $attendanceRate,
            'student_stats' => collect($studentStats)->sortByDesc('attendance_rate')->values()->all(),
        ];
    }

    /**
     * Get working days in a month (excluding weekends).
     *
     * @param int $month
     * @param int $year
     * @return int
     */
    private function getWorkingDaysInMonth(int $month, int $year): int
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $workingDays = 0;

        while ($startDate->lte($endDate)) {
            if (!$startDate->isWeekend()) {
                $workingDays++;
            }
            $startDate->addDay();
        }

        return $workingDays;
    }

    /**
     * Get attendance statistics for a specific date.
     *
     * @param string $date
     * @param string|null $class
     * @param string|null $section
     * @return array
     */
    public function getDailyStats(string $date, ?string $class = null, ?string $section = null): array
    {
        $cacheKey = "attendance:daily:{$date}";
        
        if ($class) {
            $cacheKey .= ":{$class}";
        }
        
        if ($section) {
            $cacheKey .= ":{$section}";
        }

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($date, $class, $section) {
            $query = Attendance::query()
                ->with('student')
                ->byDate($date);

            if ($class) {
                $query->byClass($class);
            }

            if ($section) {
                $query->bySection($section);
            }

            $attendances = $query->get();

            return [
                'date' => $date,
                'total' => $attendances->count(),
                'present' => $attendances->where('status', 'Present')->count(),
                'absent' => $attendances->where('status', 'Absent')->count(),
                'late' => $attendances->where('status', 'Late')->count(),
                'attendance_rate' => $attendances->count() > 0
                    ? round(
                        ($attendances->where('status', 'Present')->count() + 
                         $attendances->where('status', 'Late')->count()) / 
                        $attendances->count() * 100,
                        2
                    )
                    : 0,
            ];
        });
    }

    /**
     * Clear attendance cache for a student and date.
     *
     * @param int $studentId
     * @param string $date
     * @return void
     */
    private function clearAttendanceCache(int $studentId, string $date): void
    {
        $carbonDate = Carbon::parse($date);
        
        // Clear daily cache
        Cache::forget("attendance:daily:{$date}");
        
        // Clear monthly cache
        Cache::forget("attendance:monthly:{$carbonDate->year}:{$carbonDate->month}");
        
        // Clear with class/section variations (simplified - in production, you might want more specific clearing)
        $student = Student::find($studentId);
        if ($student) {
            Cache::forget("attendance:daily:{$date}:{$student->class}");
            Cache::forget("attendance:daily:{$date}:{$student->class}:{$student->section}");
            Cache::forget("attendance:monthly:{$carbonDate->year}:{$carbonDate->month}:{$student->class}");
            Cache::forget("attendance:monthly:{$carbonDate->year}:{$carbonDate->month}:{$student->class}:{$student->section}");
        }
    }

    /**
     * Get student attendance history.
     *
     * @param int $studentId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return \Illuminate\Support\Collection
     */
    public function getStudentAttendanceHistory(
        int $studentId,
        ?string $startDate = null,
        ?string $endDate = null
    ) {
        $query = Attendance::query()
            ->with('recordedBy')
            ->byStudent($studentId)
            ->orderBy('date', 'desc');

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->get();
    }
}

