<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes(): void
    {
        $attendance = new Attendance;

        $expected = [
            'student_id',
            'date',
            'status',
            'note',
            'recorded_by',
        ];

        $this->assertEquals($expected, $attendance->getFillable());
    }

    /** @test */
    public function it_casts_dates_properly(): void
    {
        $attendance = Attendance::factory()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $attendance->date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $attendance->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $attendance->updated_at);
    }

    /** @test */
    public function it_belongs_to_a_student(): void
    {
        $student = Student::factory()->create();
        $attendance = Attendance::factory()->create(['student_id' => $student->id]);

        $this->assertInstanceOf(Student::class, $attendance->student);
        $this->assertEquals($student->id, $attendance->student->id);
    }

    /** @test */
    public function it_belongs_to_recorded_by_user(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['recorded_by' => $user->id]);

        $this->assertInstanceOf(User::class, $attendance->recordedBy);
        $this->assertEquals($user->id, $attendance->recordedBy->id);
    }

    /** @test */
    public function by_date_scope_filters_by_date(): void
    {
        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        Attendance::factory()->create(['date' => $today]);
        Attendance::factory()->create(['date' => $today]);
        Attendance::factory()->create(['date' => $yesterday]);

        $results = Attendance::byDate($today)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($a) => $a->date->format('Y-m-d') === $today));
    }

    /** @test */
    public function date_range_scope_filters_by_date_range(): void
    {
        $startDate = Carbon::today()->subDays(5)->format('Y-m-d');
        $endDate = Carbon::today()->format('Y-m-d');
        $outsideDate = Carbon::today()->subDays(10)->format('Y-m-d');

        Attendance::factory()->create(['date' => $startDate]);
        Attendance::factory()->create(['date' => Carbon::today()->subDays(3)->format('Y-m-d')]);
        Attendance::factory()->create(['date' => $endDate]);
        Attendance::factory()->create(['date' => $outsideDate]);

        $results = Attendance::dateRange($startDate, $endDate)->get();

        $this->assertCount(3, $results);
    }

    /** @test */
    public function by_month_scope_filters_by_month_and_year(): void
    {
        $thisMonth = now()->month;
        $thisYear = now()->year;
        $lastMonth = now()->subMonth()->month;
        $lastMonthYear = now()->subMonth()->year;

        Attendance::factory()->create(['date' => Carbon::create($thisYear, $thisMonth, 1)]);
        Attendance::factory()->create(['date' => Carbon::create($thisYear, $thisMonth, 15)]);
        Attendance::factory()->create(['date' => Carbon::create($lastMonthYear, $lastMonth, 15)]);

        $results = Attendance::byMonth($thisMonth, $thisYear)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($a) => $a->date->month === $thisMonth && $a->date->year === $thisYear));
    }

    /** @test */
    public function by_status_scope_filters_by_status(): void
    {
        Attendance::factory()->create(['status' => 'Present']);
        Attendance::factory()->create(['status' => 'Present']);
        Attendance::factory()->create(['status' => 'Absent']);
        Attendance::factory()->create(['status' => 'Late']);

        $results = Attendance::byStatus('Present')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($a) => $a->status === 'Present'));
    }

    /** @test */
    public function by_student_scope_filters_by_student_id(): void
    {
        $student1 = Student::factory()->create();
        $student2 = Student::factory()->create();

        Attendance::factory()->count(3)->create(['student_id' => $student1->id]);
        Attendance::factory()->count(2)->create(['student_id' => $student2->id]);

        $results = Attendance::byStudent($student1->id)->get();

        $this->assertCount(3, $results);
        $this->assertTrue($results->every(fn ($a) => $a->student_id === $student1->id));
    }

    /** @test */
    public function by_class_scope_filters_by_student_class(): void
    {
        $class10Students = Student::factory()->count(2)->create(['class' => '10']);
        $class11Students = Student::factory()->count(2)->create(['class' => '11']);

        foreach ($class10Students as $student) {
            Attendance::factory()->create(['student_id' => $student->id]);
        }

        foreach ($class11Students as $student) {
            Attendance::factory()->create(['student_id' => $student->id]);
        }

        $results = Attendance::byClass('10')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($a) => $a->student->class === '10'));
    }

    /** @test */
    public function by_section_scope_filters_by_student_section(): void
    {
        $sectionAStudents = Student::factory()->count(2)->create(['section' => 'A']);
        $sectionBStudents = Student::factory()->count(2)->create(['section' => 'B']);

        foreach ($sectionAStudents as $student) {
            Attendance::factory()->create(['student_id' => $student->id]);
        }

        foreach ($sectionBStudents as $student) {
            Attendance::factory()->create(['student_id' => $student->id]);
        }

        $results = Attendance::bySection('A')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($a) => $a->student->section === 'A'));
    }

    /** @test */
    public function with_relations_scope_eager_loads_relationships(): void
    {
        $student = Student::factory()->create();
        $user = User::factory()->create();

        Attendance::factory()->create([
            'student_id' => $student->id,
            'recorded_by' => $user->id,
        ]);

        $attendance = Attendance::withRelations()->first();

        $this->assertTrue($attendance->relationLoaded('student'));
        $this->assertTrue($attendance->relationLoaded('recordedBy'));
    }

    /** @test */
    public function scopes_can_be_chained(): void
    {
        $student1 = Student::factory()->create(['class' => '10', 'section' => 'A']);
        $student2 = Student::factory()->create(['class' => '10', 'section' => 'B']);
        $student3 = Student::factory()->create(['class' => '11', 'section' => 'A']);

        $date = Carbon::today()->format('Y-m-d');

        Attendance::factory()->create(['student_id' => $student1->id, 'date' => $date, 'status' => 'Present']);
        Attendance::factory()->create(['student_id' => $student2->id, 'date' => $date, 'status' => 'Absent']);
        Attendance::factory()->create(['student_id' => $student3->id, 'date' => $date, 'status' => 'Present']);

        $results = Attendance::byDate($date)
            ->byClass('10')
            ->bySection('A')
            ->byStatus('Present')
            ->get();

        $this->assertCount(1, $results);
        $this->assertEquals($student1->id, $results->first()->student_id);
    }

    /** @test */
    public function factory_can_create_present_status(): void
    {
        $attendance = Attendance::factory()->present()->create();

        $this->assertEquals('Present', $attendance->status);
    }

    /** @test */
    public function factory_can_create_absent_status(): void
    {
        $attendance = Attendance::factory()->absent()->create();

        $this->assertEquals('Absent', $attendance->status);
        $this->assertNotNull($attendance->note);
    }

    /** @test */
    public function factory_can_create_late_status(): void
    {
        $attendance = Attendance::factory()->late()->create();

        $this->assertEquals('Late', $attendance->status);
    }

    /** @test */
    public function factory_can_create_for_specific_date(): void
    {
        $specificDate = '2024-06-15';

        $attendance = Attendance::factory()->forDate($specificDate)->create();

        $this->assertEquals($specificDate, $attendance->date->format('Y-m-d'));
    }

    /** @test */
    public function can_combine_multiple_scopes_for_complex_queries(): void
    {
        $student = Student::factory()->create(['class' => '10', 'section' => 'A']);

        $month = now()->month;
        $year = now()->year;

        // Create attendance for different dates in the same month
        for ($day = 1; $day <= 5; $day++) {
            $date = Carbon::create($year, $month, $day)->format('Y-m-d');
            Attendance::factory()->create([
                'student_id' => $student->id,
                'date' => $date,
                'status' => $day <= 3 ? 'Present' : 'Absent',
            ]);
        }

        // Create attendance for different student
        $otherStudent = Student::factory()->create(['class' => '11']);
        Attendance::factory()->create([
            'student_id' => $otherStudent->id,
            'date' => Carbon::create($year, $month, 1)->format('Y-m-d'),
            'status' => 'Present',
        ]);

        $results = Attendance::byMonth($month, $year)
            ->byClass('10')
            ->byStudent($student->id)
            ->get();

        $this->assertCount(5, $results);
        $this->assertTrue($results->every(fn ($a) => $a->student_id === $student->id));
    }
}
