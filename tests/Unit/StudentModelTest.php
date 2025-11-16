<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes(): void
    {
        $student = new Student;

        $expected = [
            'name',
            'student_id',
            'class',
            'section',
            'photo',
        ];

        $this->assertEquals($expected, $student->getFillable());
    }

    /** @test */
    public function it_uses_soft_deletes(): void
    {
        $student = Student::factory()->create();

        $student->delete();

        $this->assertSoftDeleted('students', ['id' => $student->id]);
        $this->assertNotNull($student->fresh()->deleted_at);
    }

    /** @test */
    public function it_casts_dates_properly(): void
    {
        $student = Student::factory()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $student->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $student->updated_at);
    }

    /** @test */
    public function it_returns_photo_url_when_photo_exists(): void
    {
        $student = Student::factory()->create(['photo' => 'students/test.jpg']);

        $expectedUrl = asset('storage/students/test.jpg');

        $this->assertEquals($expectedUrl, $student->photo_url);
    }

    /** @test */
    public function it_returns_null_photo_url_when_no_photo(): void
    {
        $student = Student::factory()->create(['photo' => null]);

        $this->assertNull($student->photo_url);
    }

    /** @test */
    public function it_has_many_attendances(): void
    {
        $student = Student::factory()->create();
        Attendance::factory()->count(3)->create(['student_id' => $student->id]);

        $this->assertCount(3, $student->attendances);
        $this->assertInstanceOf(Attendance::class, $student->attendances->first());
    }

    /** @test */
    public function search_scope_filters_by_name(): void
    {
        Student::factory()->create(['name' => 'John Doe', 'student_id' => 'STU001']);
        Student::factory()->create(['name' => 'Jane Smith', 'student_id' => 'STU002']);
        Student::factory()->create(['name' => 'Bob Johnson', 'student_id' => 'STU003']);

        $results = Student::search('John')->get();

        $this->assertCount(2, $results); // John Doe and Bob Johnson
        $this->assertTrue($results->contains('name', 'John Doe'));
        $this->assertTrue($results->contains('name', 'Bob Johnson'));
    }

    /** @test */
    public function search_scope_filters_by_student_id(): void
    {
        Student::factory()->create(['name' => 'John Doe', 'student_id' => 'STU001']);
        Student::factory()->create(['name' => 'Jane Smith', 'student_id' => 'STU002']);
        Student::factory()->create(['name' => 'Bob Johnson', 'student_id' => 'STU003']);

        $results = Student::search('STU001')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->name);
    }

    /** @test */
    public function search_scope_is_case_insensitive(): void
    {
        Student::factory()->create(['name' => 'John Doe', 'student_id' => 'STU001']);

        $results = Student::search('john')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->name);
    }

    /** @test */
    public function by_class_scope_filters_by_class(): void
    {
        Student::factory()->create(['class' => '10', 'section' => 'A']);
        Student::factory()->create(['class' => '10', 'section' => 'B']);
        Student::factory()->create(['class' => '11', 'section' => 'A']);

        $results = Student::byClass('10')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($s) => $s->class === '10'));
    }

    /** @test */
    public function by_section_scope_filters_by_section(): void
    {
        Student::factory()->create(['class' => '10', 'section' => 'A']);
        Student::factory()->create(['class' => '10', 'section' => 'B']);
        Student::factory()->create(['class' => '11', 'section' => 'A']);

        $results = Student::bySection('A')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($s) => $s->section === 'A'));
    }

    /** @test */
    public function scopes_can_be_chained(): void
    {
        Student::factory()->create(['name' => 'John Doe', 'class' => '10', 'section' => 'A']);
        Student::factory()->create(['name' => 'Jane Smith', 'class' => '10', 'section' => 'B']);
        Student::factory()->create(['name' => 'Bob Johnson', 'class' => '11', 'section' => 'A']);

        $results = Student::byClass('10')->bySection('A')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->name);
    }
}
