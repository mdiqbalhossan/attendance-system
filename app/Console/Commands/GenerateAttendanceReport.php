<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateAttendanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-report 
                            {month? : The month (1-12) for the report}
                            {year? : The year for the report}
                            {--class= : Filter by class}
                            {--section= : Filter by section}
                            {--format=table : Output format (table|json)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate attendance report for a specific month and class';

    /**
     * The attendance service instance.
     */
    protected AttendanceService $attendanceService;

    /**
     * Create a new command instance.
     */
    public function __construct(AttendanceService $attendanceService)
    {
        parent::__construct();
        $this->attendanceService = $attendanceService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $month = $this->argument('month') ?? now()->month;
        $year = $this->argument('year') ?? now()->year;
        $class = $this->option('class');
        $section = $this->option('section');
        $format = $this->option('format');

        // Validate month and year
        if ($month < 1 || $month > 12) {
            $this->error('Invalid month. Please provide a month between 1 and 12.');

            return Command::FAILURE;
        }

        if ($year < 2020 || $year > 2100) {
            $this->error('Invalid year. Please provide a year between 2020 and 2100.');

            return Command::FAILURE;
        }

        $this->info('Generating attendance report...');
        $this->info('Month: '.Carbon::create($year, $month, 1)->format('F Y'));

        if ($class) {
            $this->info("Class: {$class}");
        }

        if ($section) {
            $this->info("Section: {$section}");
        }

        $this->newLine();

        try {
            // Get the report data
            $report = $this->attendanceService->getMonthlyReport(
                $month,
                $year,
                $class,
                $section
            );

            // Display overall statistics
            $this->displayOverallStats($report['stats']);

            $this->newLine();

            // Display student statistics based on format
            if ($format === 'json') {
                $this->displayJsonReport($report);
            } else {
                $this->displayTableReport($report['stats']['student_stats']);
            }

            $this->newLine();
            $this->info('Report generated successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate report: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Display overall statistics.
     */
    protected function displayOverallStats(array $stats): void
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Records', $stats['total_records']],
                ['Unique Students', $stats['unique_students']],
                ['Working Days', $stats['working_days']],
                ['Present Count', $stats['present_count']],
                ['Absent Count', $stats['absent_count']],
                ['Late Count', $stats['late_count']],
                ['Attendance Rate', $stats['attendance_rate'].'%'],
            ]
        );
    }

    /**
     * Display student statistics in table format.
     */
    protected function displayTableReport(array $studentStats): void
    {
        if (empty($studentStats)) {
            $this->warn('No attendance records found for the specified criteria.');

            return;
        }

        $headers = [
            'Student Name',
            'Student ID',
            'Class',
            'Section',
            'Total Days',
            'Present',
            'Absent',
            'Late',
            'Attendance Rate',
        ];

        $rows = [];
        foreach ($studentStats as $stat) {
            $student = $stat['student'];
            $rows[] = [
                $student['name'],
                $student['student_id'],
                $student['class'],
                $student['section'],
                $stat['total_days'],
                $stat['present'],
                $stat['absent'],
                $stat['late'],
                $stat['attendance_rate'].'%',
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Display report in JSON format.
     */
    protected function displayJsonReport(array $report): void
    {
        $this->line(json_encode($report, JSON_PRETTY_PRINT));
    }
}
