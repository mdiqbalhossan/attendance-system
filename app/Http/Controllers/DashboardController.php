<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with attendance statistics.
     */
    public function index(): Response
    {
        $today = Carbon::today();
        $currentMonth = $today->month;
        $currentYear = $today->year;

        // Get today's attendance summary
        $todayStats = $this->getTodayAttendanceStats($today);

        // Get monthly attendance data for chart
        $monthlyChartData = $this->getMonthlyChartData($currentMonth, $currentYear);

        // Get overall statistics
        $overallStats = $this->getOverallStats();

        // Get recent attendance activity
        $recentActivity = $this->getRecentActivity();

        return Inertia::render('Dashboard', [
            'todayStats' => $todayStats,
            'monthlyChartData' => $monthlyChartData,
            'overallStats' => $overallStats,
            'recentActivity' => $recentActivity,
            'currentMonth' => $today->format('F'),
            'currentYear' => $currentYear,
        ]);
    }

    /**
     * Get today's attendance statistics.
     */
    private function getTodayAttendanceStats(Carbon $date): array
    {
        $totalStudents = Student::count();

        // Get attendance counts by status for today
        $attendanceToday = Attendance::byDate($date->format('Y-m-d'))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $present = $attendanceToday['present'] ?? 0;
        $absent = $attendanceToday['absent'] ?? 0;
        $late = $attendanceToday['late'] ?? 0;
        $excused = $attendanceToday['excused'] ?? 0;

        $totalRecorded = $present + $absent + $late + $excused;
        $notRecorded = $totalStudents - $totalRecorded;

        $attendanceRate = $totalStudents > 0
            ? round(($present / $totalStudents) * 100, 1)
            : 0;

        return [
            'date' => $date->format('Y-m-d'),
            'dateFormatted' => $date->format('F j, Y'),
            'totalStudents' => $totalStudents,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused,
            'notRecorded' => $notRecorded,
            'totalRecorded' => $totalRecorded,
            'attendanceRate' => $attendanceRate,
        ];
    }

    /**
     * Get monthly attendance data for chart visualization.
     */
    private function getMonthlyChartData(int $month, int $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Get daily attendance counts for the month
        $dailyAttendance = Attendance::whereBetween('date', [$startDate, $endDate])
            ->select(
                'date',
                'status',
                DB::raw('count(*) as count')
            )
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        // Organize data by date
        $dates = [];
        $presentData = [];
        $absentData = [];
        $lateData = [];
        $excusedData = [];

        // Create array with all dates in the month
        for ($day = 1; $day <= $endDate->day; $day++) {
            $date = Carbon::create($year, $month, $day)->format('Y-m-d');
            $dates[] = Carbon::create($year, $month, $day)->format('M j');

            // Initialize counts
            $counts = [
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0,
            ];

            // Get counts for this date
            $dateAttendance = $dailyAttendance->where('date', $date);
            foreach ($dateAttendance as $record) {
                $counts[$record->status] = $record->count;
            }

            $presentData[] = $counts['present'];
            $absentData[] = $counts['absent'];
            $lateData[] = $counts['late'];
            $excusedData[] = $counts['excused'];
        }

        // Calculate monthly totals
        $monthlyTotals = Attendance::byMonth($month, $year)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => $presentData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Absent',
                    'data' => $absentData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Late',
                    'data' => $lateData,
                    'backgroundColor' => 'rgba(251, 191, 36, 0.8)',
                    'borderColor' => 'rgba(251, 191, 36, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Excused',
                    'data' => $excusedData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'monthlyTotals' => [
                'present' => $monthlyTotals['present'] ?? 0,
                'absent' => $monthlyTotals['absent'] ?? 0,
                'late' => $monthlyTotals['late'] ?? 0,
                'excused' => $monthlyTotals['excused'] ?? 0,
            ],
        ];
    }

    /**
     * Get overall attendance statistics.
     */
    private function getOverallStats(): array
    {
        $totalStudents = Student::count();
        $totalAttendanceRecords = Attendance::count();

        // Get stats for the current week
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $weeklyAttendance = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $weeklyPresent = $weeklyAttendance['present'] ?? 0;
        $weeklyTotal = array_sum($weeklyAttendance);
        $weeklyAttendanceRate = $weeklyTotal > 0
            ? round(($weeklyPresent / $weeklyTotal) * 100, 1)
            : 0;

        // Get average monthly attendance rate
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $monthlyAttendance = Attendance::byMonth($currentMonth, $currentYear)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $monthlyPresent = $monthlyAttendance['present'] ?? 0;
        $monthlyTotal = array_sum($monthlyAttendance);
        $monthlyAttendanceRate = $monthlyTotal > 0
            ? round(($monthlyPresent / $monthlyTotal) * 100, 1)
            : 0;

        return [
            'totalStudents' => $totalStudents,
            'totalRecords' => $totalAttendanceRecords,
            'weeklyAttendanceRate' => $weeklyAttendanceRate,
            'monthlyAttendanceRate' => $monthlyAttendanceRate,
            'weeklyStats' => [
                'present' => $weeklyPresent,
                'absent' => $weeklyAttendance['absent'] ?? 0,
                'late' => $weeklyAttendance['late'] ?? 0,
                'excused' => $weeklyAttendance['excused'] ?? 0,
            ],
        ];
    }

    /**
     * Get recent attendance activity.
     */
    private function getRecentActivity(): array
    {
        $recentRecords = Attendance::with(['student', 'recordedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'student_name' => $record->student->name,
                    'student_id' => $record->student->student_id,
                    'status' => $record->status,
                    'date' => $record->date->format('M j, Y'),
                    'recorded_by' => $record->recordedBy->name,
                    'recorded_at' => $record->created_at->diffForHumans(),
                ];
            });

        return $recentRecords->toArray();
    }
}
