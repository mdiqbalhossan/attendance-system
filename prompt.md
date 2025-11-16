Module Name:- Student Management
Model & DB:
Student model: name, student_id, class, section, photo
Migration and seeder for sample data

Endpoints (CRUD):
    GET /students → list students
    GET /students/{id} → view student
    POST /students → create
    PUT /students/{id} → update
    DELETE /students/{id} → delete

Features:
    -Validation using Form Requests
    -Resource classes for API response formatting
    -Optional photo upload handling

Here is my module structure and details. You need to make this module fully functional with backend and frontend.
Follow the rules which I already define laravel-vue.md file.
Must be ensure all are workable and no conflict existing design and functionality.
Must be ensure all are follow the best practices.
Must be ensure design and code structure should be clean and maintainable.
Must be ensure design and code structure should be consistent.

Module Name:- Attendance Module
Model & DB:
Attendance model: student_id, date, status (Present/Absent/Late), note, recorded_by
Relationships: Attendance -> Student

Endpoints (CRUD):
    POST /attendance/bulk → record attendance for multiple students
    GET /attendance/monthly-report → generate monthly report (eager loading for students)

Features:
    -Query optimization (e.g., eager load student with attendance)
    -Redis caching for attendance stats
    -Service Layer: handle attendance business logic (e.g., calculating monthly stats, bulk attendance)
    -Artisan command: php artisan attendance:generate-report {month} {class}
    -Event/Listener: notify admin/teachers when attendance is recorded

Here is my module structure and details. You need to make this module fully functional with backend and frontend.
Follow the rules which I already define laravel-vue.md file.
Must be ensure all are workable and no conflict existing design and functionality.
Must be ensure all are follow the best practices.
Must be ensure design and code structure should be clean and maintainable.
Must be ensure design and code structure should be consistent.
