<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'date',
        'status',
        'note',
        'recorded_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the student that owns the attendance.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who recorded the attendance.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Scope to filter by date.
     */
    public function scopeByDate($query, string $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by month and year.
     */
    public function scopeByMonth($query, int $month, int $year)
    {
        return $query->whereMonth('date', $month)
            ->whereYear('date', $year);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by student.
     */
    public function scopeByStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to filter by class through student relationship.
     */
    public function scopeByClass($query, string $class)
    {
        return $query->whereHas('student', function ($q) use ($class) {
            $q->where('class', $class);
        });
    }

    /**
     * Scope to filter by section through student relationship.
     */
    public function scopeBySection($query, string $section)
    {
        return $query->whereHas('student', function ($q) use ($section) {
            $q->where('section', $section);
        });
    }

    /**
     * Scope to eager load student and recorder.
     */
    public function scopeWithRelations($query)
    {
        return $query->with(['student', 'recordedBy']);
    }
}
