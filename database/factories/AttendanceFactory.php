<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'date' => fake()->dateTimeBetween('-30 days', 'now'),
            'status' => fake()->randomElement(['Present', 'Absent', 'Late']),
            'note' => fake()->optional(0.3)->sentence(),
            'recorded_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the attendance is marked as present.
     */
    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Present',
        ]);
    }

    /**
     * Indicate that the attendance is marked as absent.
     */
    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Absent',
            'note' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the attendance is marked as late.
     */
    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Late',
            'note' => fake()->optional()->sentence(),
        ]);
    }

    /**
     * Set a specific date for the attendance.
     */
    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }
}
