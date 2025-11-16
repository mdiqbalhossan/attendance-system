# Attendance Module - Quick Start Guide

## 🚀 Get Started in 5 Minutes

### 1. Database Setup (Already Done! ✅)

The migration has already been run. If you need to reset:

```bash
php artisan migrate:fresh --seed
```

### 2. Seed Sample Data (Optional)

Generate realistic attendance data for the last 30 days:

```bash
php artisan db:seed --class=AttendanceSeeder
```

This creates:
- Attendance records for all students
- 85% present, 10% absent, 5% late
- Skips weekends
- Includes sample notes

### 3. Access the Module

Open your browser and navigate to:

```
http://your-app-url/attendance
```

## 📋 Common Tasks

### Record Attendance for Today

1. Click **"Record Attendance"** button
2. Select today's date (default)
3. Filter by class/section if needed
4. Click status buttons for each student (Present/Absent/Late)
5. Add optional notes
6. Click **"Save Attendance"**

**Quick Tip**: Use "Mark All Present" button first, then change status for absent/late students only!

### View Attendance Records

1. Go to `/attendance`
2. Use filters:
   - **Single Date**: View specific day
   - **Date Range**: View multiple days
   - **Status**: Filter by Present/Absent/Late
   - **Class/Section**: Filter by class

### Generate Monthly Report

1. Click **"Monthly Report"** button or go to `/attendance/reports/monthly`
2. Select month and year
3. Optionally filter by class/section
4. Click **"Generate Report"**

You'll see:
- Overall statistics (total, working days, attendance rate)
- Status distribution
- Student-wise breakdown with attendance rates

### CLI Report Generation

```bash
# Current month
php artisan attendance:generate-report

# Specific month/year
php artisan attendance:generate-report 11 2024

# Filter by class
php artisan attendance:generate-report 11 2024 --class=10

# Filter by class and section
php artisan attendance:generate-report 11 2024 --class=10 --section=A

# JSON format
php artisan attendance:generate-report --format=json
```

## 🎯 Usage Examples

### Example 1: Record Attendance for Class 10-A

1. Navigate to `/attendance/create`
2. Set date: Today
3. Select Class: 10
4. Select Section: A
5. Click "Apply Filters"
6. Review student list
7. Click "Mark All Present"
8. Change status for absent students
9. Add notes for absences
10. Click "Save Attendance"

### Example 2: Check Yesterday's Attendance

1. Navigate to `/attendance`
2. Set Date filter to yesterday
3. Click outside the date field to apply
4. View attendance records
5. See daily statistics card at the top

### Example 3: Find All Absences This Week

1. Navigate to `/attendance`
2. Set Start Date: Monday of this week
3. Set End Date: Today
4. Set Status: Absent
5. View filtered results

### Example 4: Monthly Report for Class 10

1. Navigate to `/attendance/reports/monthly`
2. Select current month and year
3. Select Class: 10
4. Leave Section: All Sections
5. Click "Generate Report"
6. Review statistics and student breakdown

## 💡 Pro Tips

### For Teachers

1. **Daily Routine**: 
   - Use `/attendance/create` every morning
   - "Mark All Present" first
   - Only change status for exceptions
   - Add notes for absences

2. **Weekly Review**:
   - Check attendance records with date range filter
   - Identify patterns of absences
   - Follow up with students

3. **Monthly Analysis**:
   - Generate monthly report
   - Review attendance rates
   - Identify at-risk students (low attendance)

### For Administrators

1. **CLI Reports**:
   ```bash
   # Generate reports for all classes
   for class in 9 10 11 12; do
       php artisan attendance:generate-report 11 2024 --class=$class
   done
   ```

2. **Data Analysis**:
   - Use monthly reports to track trends
   - Compare attendance rates across classes
   - Identify improvement areas

3. **Cache Management**:
   ```bash
   # If stats seem outdated
   php artisan cache:clear
   ```

## 🔧 Customization

### Add Custom Notification

Edit `app/Listeners/NotifyAttendanceRecorded.php`:

```php
public function handle(AttendanceRecorded $event): void
{
    $attendance = $event->attendance;
    
    if ($attendance->status === 'Absent') {
        // Send email to parent
        Mail::to($attendance->student->parent_email)
            ->send(new StudentAbsentMail($attendance));
    }
}
```

### Modify Attendance Statuses

Edit migration to add new status:

```php
$table->enum('status', ['Present', 'Absent', 'Late', 'Excused'])->default('Present');
```

Then update:
- Form requests validation
- Frontend status buttons
- Color coding in composables

### Add Export Functionality

In `MonthlyReport.vue`, implement `exportReport()`:

```typescript
const exportReport = () => {
    window.open(`/attendance/reports/monthly/export?month=${selectedMonth.value}&year=${selectedYear.value}&format=pdf`);
};
```

Then create controller method to generate PDF/Excel.

## 🐛 Troubleshooting

### "No students found"

**Solution**: Run student seeder first
```bash
php artisan db:seed --class=StudentSeeder
```

### "Attendance already recorded"

**Reason**: Unique constraint (one record per student per day)

**Solution**: Edit existing record instead of creating new one

### Cache not updating

**Solution**: Clear cache
```bash
php artisan cache:clear
```

### Event not firing

**Solution**: Check EventServiceProvider is registered in `bootstrap/providers.php`

### Migration fails

**Solution**: Check database connection and try:
```bash
php artisan migrate:fresh
```

## 📱 Mobile Usage

The interface is responsive and works on mobile devices:

1. **Portrait Mode**: Tables scroll horizontally
2. **Quick Actions**: Easy tap targets
3. **Filters**: Dropdown-based for touch devices

## 🔐 Permissions (Future Enhancement)

Currently, all authenticated users can access attendance. To add role-based access:

```php
// In routes/attendance.php
Route::middleware(['auth', 'role:teacher,admin'])->group(function () {
    // ... attendance routes
});
```

## 📊 Performance Tips

1. **Use Filters**: Reduce query size with class/section filters
2. **Date Ranges**: Limit date ranges to avoid slow queries
3. **Cache**: Reports are cached for 12-24 hours
4. **Pagination**: Automatically applied to large datasets

## 🎓 Learning Resources

- **Full Documentation**: `docs/ATTENDANCE_MODULE.md`
- **Implementation Summary**: `ATTENDANCE_MODULE_SUMMARY.md`
- **Code Examples**: Check controller and service layer
- **Vue Components**: Study composables for patterns

## 💬 Support

For issues or questions:
1. Check documentation files
2. Review code comments
3. Check Laravel and Vue 3 documentation
4. Inspect browser console for errors

## 🎉 Success Checklist

After setup, verify:
- ✅ Can access `/attendance`
- ✅ Navigation shows "Attendance" menu item
- ✅ Can record bulk attendance
- ✅ Can view attendance records
- ✅ Can generate monthly report
- ✅ CLI command works
- ✅ Filters work correctly
- ✅ Cache is working (fast subsequent loads)

## 🚦 Status Indicators

- **Green** (Present): Good attendance
- **Red** (Absent): Needs attention
- **Yellow** (Late): Minor issue

## 📈 Metrics to Track

1. **Overall Attendance Rate**: Target 90%+
2. **Chronic Absenteeism**: <10% attendance rate
3. **Tardiness Rate**: Late/Total ratio
4. **Class Comparison**: Identify best/worst performing classes

---

**Ready to go!** Start recording attendance and generating insights. 🎓✨

For detailed documentation, see `docs/ATTENDANCE_MODULE.md`

