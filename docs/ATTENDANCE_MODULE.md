# Attendance Module Documentation

## Overview

The Attendance Module is a comprehensive system for managing student attendance records in a Laravel + Vue 3 + Inertia.js application. It includes features for bulk attendance recording, monthly reports, Redis caching, event-driven notifications, and an Artisan command for generating reports.

## Features

✅ **Bulk Attendance Recording** - Record attendance for multiple students simultaneously  
✅ **Monthly Reports** - Generate detailed monthly attendance statistics  
✅ **Query Optimization** - Eager loading of relationships for improved performance  
✅ **Redis Caching** - Cache attendance statistics for faster retrieval  
✅ **Service Layer** - Clean separation of business logic  
✅ **Event/Listener** - Notify admins/teachers when attendance is recorded  
✅ **Artisan Command** - CLI tool for generating attendance reports  
✅ **Modern Vue 3 UI** - Beautiful and responsive user interface

## Database Schema

### Attendances Table

```sql
- id (primary key)
- student_id (foreign key -> students.id)
- date (date)
- status (enum: Present, Absent, Late)
- note (text, nullable)
- recorded_by (foreign key -> users.id)
- timestamps (created_at, updated_at)

Indexes:
- [student_id, date] (for fast queries)
- [date] (for daily reports)
- [status] (for filtering)

Constraints:
- Unique constraint on [student_id, date] (one attendance record per student per day)
```

## Backend Structure

### Models

**`App\Models\Attendance`**
- Relationships: `student()`, `recordedBy()`
- Scopes: `byDate()`, `dateRange()`, `byMonth()`, `byStatus()`, `byStudent()`, `byClass()`, `bySection()`, `withRelations()`

**`App\Models\Student`** (updated)
- Added relationship: `attendances()`

### Service Layer

**`App\Services\AttendanceService`**

Key methods:
- `recordBulkAttendance()` - Records attendance for multiple students with transaction support
- `getMonthlyReport()` - Generates cached monthly attendance reports
- `getDailyStats()` - Gets cached daily attendance statistics
- `getStudentAttendanceHistory()` - Retrieves attendance history for a specific student

### Controllers

**`App\Http\Controllers\AttendanceController`**

Endpoints:
- `GET /attendance` - List attendance records with filters
- `GET /attendance/create` - Show bulk attendance form
- `POST /attendance` - Store single attendance record
- `POST /attendance/bulk` - Store bulk attendance records
- `GET /attendance/reports/monthly` - Display monthly report
- `GET /attendance/{id}` - Show specific attendance record
- `GET /attendance/{id}/edit` - Edit attendance record
- `PUT /attendance/{id}` - Update attendance record
- `DELETE /attendance/{id}` - Delete attendance record

### Form Requests

**`App\Http\Requests\Attendance\StoreAttendanceRequest`**
- Validates single attendance record
- Rules: student_id, date, status, note

**`App\Http\Requests\Attendance\BulkAttendanceRequest`**
- Validates bulk attendance data
- Automatically merges date into each attendance record

### Resources

**`App\Http\Resources\AttendanceResource`**
- Formats attendance data for API responses
- Includes student and recorder information

### Events & Listeners

**`App\Events\AttendanceRecorded`**
- Fired when attendance is recorded
- Carries the Attendance model instance

**`App\Listeners\NotifyAttendanceRecorded`**
- Logs attendance records
- Can be extended to send notifications (email, SMS, push notifications)

### Artisan Commands

**`php artisan attendance:generate-report {month?} {year?}`**

Options:
- `--class=` - Filter by class
- `--section=` - Filter by section
- `--format=` - Output format (table or json)

Examples:
```bash
# Generate report for current month
php artisan attendance:generate-report

# Generate report for specific month and year
php artisan attendance:generate-report 11 2024

# Generate report for a specific class
php artisan attendance:generate-report 11 2024 --class=10

# Generate JSON output
php artisan attendance:generate-report --format=json
```

## Frontend Structure

### Pages

**`resources/js/pages/attendance/Index.vue`**
- Lists all attendance records
- Filters: date, date range, status, class, section
- Displays daily statistics when date filter is applied
- Pagination support

**`resources/js/pages/attendance/BulkRecord.vue`**
- Record attendance for multiple students
- Quick actions: Mark all present/absent/late
- Filter students by class and section
- Real-time summary statistics
- Individual note support for each student

**`resources/js/pages/attendance/MonthlyReport.vue`**
- Displays comprehensive monthly attendance statistics
- Overall statistics (total records, attendance rate, etc.)
- Status distribution charts
- Student-wise detailed breakdown
- Export functionality (placeholder for CSV/PDF)

### Composables

**`resources/js/composables/useAttendance.ts`**

Functions:
- `recordBulkAttendance()` - Submits bulk attendance data
- `updateAttendance()` - Updates an attendance record
- `deleteAttendance()` - Deletes an attendance record
- `markAllPresent()` - Helper to mark all students as present
- `getStatusColor()` - Returns appropriate color class for status badge

**`resources/js/composables/useAttendanceFilters.ts`**

Features:
- Reactive filter state management
- Debounced URL updates
- Preserves state and scroll position
- Clear all filters functionality

## Caching Strategy

The module uses Redis for caching attendance statistics:

### Cache Keys

- `attendance:daily:{date}` - Daily statistics
- `attendance:daily:{date}:{class}` - Daily stats filtered by class
- `attendance:daily:{date}:{class}:{section}` - Daily stats filtered by class and section
- `attendance:monthly:{year}:{month}` - Monthly report
- `attendance:monthly:{year}:{month}:{class}` - Monthly report by class
- `attendance:monthly:{year}:{month}:{class}:{section}` - Monthly report by class and section

### Cache Duration

- Daily stats: 12 hours
- Monthly reports: 24 hours

### Cache Invalidation

Cache is automatically cleared when:
- New attendance is recorded
- Attendance is updated
- Attendance is deleted

## Testing

### Factory

```php
// Create attendance with default values
Attendance::factory()->create();

// Create present attendance
Attendance::factory()->present()->create();

// Create absent attendance with note
Attendance::factory()->absent()->create();

// Create late attendance
Attendance::factory()->late()->create();

// Create attendance for specific date
Attendance::factory()->forDate('2024-11-15')->create();
```

### Seeder

```bash
# Seed sample attendance data for last 30 days
php artisan db:seed --class=AttendanceSeeder
```

The seeder creates realistic attendance data:
- 85% present
- 10% absent (with reasons)
- 5% late
- Skips weekends
- Includes sample notes

## Usage Examples

### Recording Bulk Attendance (Controller)

```php
$results = $this->attendanceService->recordBulkAttendance([
    ['student_id' => 1, 'status' => 'Present', 'date' => '2024-11-15'],
    ['student_id' => 2, 'status' => 'Absent', 'date' => '2024-11-15', 'note' => 'Sick'],
], auth()->id());
```

### Getting Monthly Report

```php
$report = $this->attendanceService->getMonthlyReport(
    month: 11,
    year: 2024,
    class: '10',
    section: 'A'
);
```

### Getting Daily Statistics

```php
$stats = $this->attendanceService->getDailyStats(
    date: '2024-11-15',
    class: '10',
    section: 'A'
);
```

### Querying Attendance (Using Scopes)

```php
// Get attendance for a specific date
$attendances = Attendance::byDate('2024-11-15')->get();

// Get attendance for a month
$attendances = Attendance::byMonth(11, 2024)->get();

// Get attendance by class with eager loading
$attendances = Attendance::byClass('10')
    ->withRelations()
    ->get();

// Complex query with multiple filters
$attendances = Attendance::query()
    ->byMonth(11, 2024)
    ->byClass('10')
    ->bySection('A')
    ->byStatus('Present')
    ->withRelations()
    ->orderBy('date', 'desc')
    ->get();
```

## API Integration

If you want to add API endpoints, here's an example:

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('attendance', AttendanceController::class);
    
    Route::post('attendance/bulk', [AttendanceController::class, 'bulkStore']);
    Route::get('attendance/stats/daily', [AttendanceController::class, 'dailyStats']);
    Route::get('attendance/reports/monthly', [AttendanceController::class, 'monthlyReport']);
});
```

## Best Practices

### 1. Always Use Service Layer for Business Logic

```php
// Good
$this->attendanceService->recordBulkAttendance($data, auth()->id());

// Avoid
Attendance::create($data); // Direct model manipulation
```

### 2. Use Query Scopes for Filtering

```php
// Good
Attendance::byMonth(11, 2024)->byClass('10')->get();

// Avoid
Attendance::whereMonth('date', 11)->whereYear('date', 2024)
    ->whereHas('student', fn($q) => $q->where('class', '10'))
    ->get();
```

### 3. Eager Load Relationships

```php
// Good
Attendance::withRelations()->get();

// Avoid
Attendance::all(); // Will cause N+1 queries
```

### 4. Use Form Requests for Validation

```php
// Good
public function store(StoreAttendanceRequest $request) { }

// Avoid
public function store(Request $request) {
    $request->validate([...]); // Validation in controller
}
```

## Performance Considerations

1. **Database Indexes**: The migration includes indexes on frequently queried columns
2. **Eager Loading**: Always use `withRelations()` scope when you need related data
3. **Caching**: Statistics are cached to reduce database queries
4. **Batch Operations**: Use `recordBulkAttendance()` instead of individual inserts
5. **Query Optimization**: Use scopes instead of raw queries

## Future Enhancements

Potential features to add:

1. **Export to PDF/Excel** - Generate downloadable reports
2. **SMS/Email Notifications** - Notify parents when student is absent
3. **Attendance Dashboard Widgets** - Display key metrics on main dashboard
4. **Biometric Integration** - Integrate with fingerprint/RFID systems
5. **Mobile App API** - RESTful API for mobile attendance marking
6. **Attendance Analytics** - Trends, predictions, and insights
7. **Parent Portal** - Allow parents to view their child's attendance
8. **Leave Management** - Handle planned absences and leave requests
9. **Automatic Reminders** - Remind teachers to mark attendance
10. **QR Code Check-in** - Students scan QR codes to mark attendance

## Troubleshooting

### Cache Not Clearing

If you notice cached data isn't updating:

```bash
# Clear all cache
php artisan cache:clear

# Clear specific Redis keys
redis-cli KEYS "attendance:*" | xargs redis-cli DEL
```

### Migration Issues

If migration fails:

```bash
# Rollback
php artisan migrate:rollback

# Fresh migration
php artisan migrate:fresh --seed
```

### Event Not Firing

Ensure EventServiceProvider is registered in `bootstrap/providers.php`:

```php
return [
    App\Providers\EventServiceProvider::class,
    // ...
];
```

## Contributing

When contributing to this module:

1. Follow Laravel and Vue 3 best practices
2. Write tests for new features
3. Update this documentation
4. Use meaningful commit messages
5. Ensure code passes linting

## License

This module is part of the attendance-system project and follows the same license.

