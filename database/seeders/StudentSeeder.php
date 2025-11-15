<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some specific students for testing
        Student::create([
            'name' => 'John Doe',
            'student_id' => 'STU001',
            'class' => '10',
            'section' => 'A',
        ]);

        Student::create([
            'name' => 'Jane Smith',
            'student_id' => 'STU002',
            'class' => '10',
            'section' => 'A',
        ]);

        Student::create([
            'name' => 'Mike Johnson',
            'student_id' => 'STU003',
            'class' => '9',
            'section' => 'B',
        ]);

        Student::create([
            'name' => 'Sarah Wilson',
            'student_id' => 'STU004',
            'class' => '11',
            'section' => 'A',
        ]);

        Student::create([
            'name' => 'David Brown',
            'student_id' => 'STU005',
            'class' => '12',
            'section' => 'C',
        ]);

        // Create additional random students using factory
        Student::factory(45)->create();
    }
}
