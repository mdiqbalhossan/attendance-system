<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\BulkAttendanceRequest;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Student;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    /**
     * The attendance service instance.
     */
    protected AttendanceService $attendanceService;

    /**
     * Create a new controller instance.
     */
    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display a listing of attendance records.
     */
    public function index(Request $request): Response
    {
        $query = Attendance::query()->withRelations();

        // Filter by date
        if ($request->filled('date')) {
            $query->byDate($request->date);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by class
        if ($request->filled('class')) {
            $query->byClass($request->class);
        }

        // Filter by section
        if ($request->filled('section')) {
            $query->bySection($request->section);
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->byStudent($request->student_id);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Get filter options
        $classes = Student::distinct()
            ->whereNotNull('class')
            ->pluck('class')
            ->sort()
            ->values();

        $sections = Student::distinct()
            ->whereNotNull('section')
            ->pluck('section')
            ->sort()
            ->values();

        // Get daily stats if date is provided
        $dailyStats = null;
        if ($request->filled('date')) {
            $dailyStats = $this->attendanceService->getDailyStats(
                $request->date,
                $request->class,
                $request->section
            );
        }

        return Inertia::render('attendance/Index', [
            'attendances' => AttendanceResource::collection($attendances),
            'filters' => [
                'date' => $request->date ?? '',
                'start_date' => $request->start_date ?? '',
                'end_date' => $request->end_date ?? '',
                'status' => $request->status ?? '',
                'class' => $request->class ?? '',
                'section' => $request->section ?? '',
                'student_id' => $request->student_id ?? '',
            ],
            'classes' => $classes,
            'sections' => $sections,
            'dailyStats' => $dailyStats,
        ]);
    }

    /**
     * Show the form for creating a new attendance record.
     */
    public function create(Request $request): Response
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));

        $query = Student::query();

        // Filter by class
        if ($request->filled('class')) {
            $query->byClass($request->class);
        }

        // Filter by section
        if ($request->filled('section')) {
            $query->bySection($request->section);
        }

        $students = $query->orderBy('name')->get();

        // Get existing attendance for the date
        $existingAttendance = Attendance::byDate($date)
            ->pluck('status', 'student_id')
            ->toArray();

        // Get filter options
        $classes = Student::distinct()
            ->whereNotNull('class')
            ->pluck('class')
            ->sort()
            ->values();

        $sections = Student::distinct()
            ->whereNotNull('section')
            ->pluck('section')
            ->sort()
            ->values();

        return Inertia::render('attendance/BulkRecord', [
            'students' => $students,
            'date' => $date,
            'existingAttendance' => $existingAttendance,
            'filters' => [
                'class' => $request->class ?? '',
                'section' => $request->section ?? '',
            ],
            'classes' => $classes,
            'sections' => $sections,
        ]);
    }

    /**
     * Store a newly created attendance record.
     */
    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['recorded_by'] = auth()->id();

        Attendance::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'date' => $validated['date'],
            ],
            $validated
        );

        return back()->with('success', 'Attendance recorded successfully.');
    }

    /**
     * Store bulk attendance records.
     */
    public function bulkStore(BulkAttendanceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $results = $this->attendanceService->recordBulkAttendance(
            $validated['attendances'],
            auth()->id(),
            $validated['date']
        );
        $successCount = count($results['success']);
        $errorCount = count($results['errors']);
        if ($errorCount > 0) {
            $message = "{$successCount} attendance records saved successfully. {$errorCount} failed.";

            return back()->with('warning', $message);
        }

        return redirect()->route('attendance.index')->with('success', "Attendance recorded successfully for {$successCount} students.");
    }

    /**
     * Display the monthly attendance report.
     */
    public function monthlyReport(Request $request): Response
    {
        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2100',
            'class' => 'nullable|string',
            'section' => 'nullable|string',
        ]);

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $class = $request->get('class');
        $section = $request->get('section');

        $report = $this->attendanceService->getMonthlyReport(
            $month,
            $year,
            $class,
            $section
        );

        // Get filter options
        $classes = Student::distinct()
            ->whereNotNull('class')
            ->pluck('class')
            ->sort()
            ->values();

        $sections = Student::distinct()
            ->whereNotNull('section')
            ->pluck('section')
            ->sort()
            ->values();

        return Inertia::render('attendance/MonthlyReport', [
            'report' => $report,
            'filters' => [
                'month' => $month,
                'year' => $year,
                'class' => $class ?? '',
                'section' => $section ?? '',
            ],
            'classes' => $classes,
            'sections' => $sections,
        ]);
    }

    /**
     * Display the specified attendance record.
     */
    public function show(Attendance $attendance): Response
    {
        $attendance->load(['student', 'recordedBy']);

        return Inertia::render('attendance/Show', [
            'attendance' => new AttendanceResource($attendance),
        ]);
    }

    /**
     * Show the form for editing the specified attendance record.
     */
    public function edit(Attendance $attendance): Response
    {
        $attendance->load('student');

        return Inertia::render('attendance/Edit', [
            'attendance' => new AttendanceResource($attendance),
        ]);
    }

    /**
     * Update the specified attendance record.
     */
    public function update(StoreAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        $validated = $request->validated();
        $validated['recorded_by'] = auth()->id();

        $attendance->update($validated);

        return to_route('attendance.index')
            ->with('success', 'Attendance updated successfully.');
    }

    /**
     * Remove the specified attendance record.
     */
    public function destroy(Attendance $attendance): RedirectResponse
    {
        $attendance->delete();

        return to_route('attendance.index')
            ->with('success', 'Attendance record deleted successfully.');
    }
}
