<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $classes = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
        $sections = ['A', 'B', 'C', 'D'];

        return [
            'name' => $this->faker->name(),
            'student_id' => 'STU' . $this->faker->unique()->numberBetween(1000, 9999),
            'class' => $this->faker->randomElement($classes),
            'section' => $this->faker->randomElement($sections),
            'photo' => null, // We'll handle photo uploads separately
        ];
    }
}
