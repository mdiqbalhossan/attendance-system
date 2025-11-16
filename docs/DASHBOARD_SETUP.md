# Dashboard Module - Quick Setup Guide

## Installation Steps

### 1. Install Dependencies

```bash
npm install chart.js @iconify/vue
```

### 2. Database Setup

Ensure your attendance table has proper indexes for performance:

```bash
php artisan migrate
```

If you need to add indexes manually:

```sql
CREATE INDEX idx_attendance_date ON attendances(date);
CREATE INDEX idx_attendance_status ON attendances(status);
CREATE INDEX idx_attendance_student_date ON attendances(student_id, date);
```

### 3. Seed Test Data (Optional)

Create some test attendance records:

```bash
php artisan db:seed --class=AttendanceSeeder
```

Or create manually:

```php
// In tinker or seeder
$students = Student::all();
$today = now();

foreach ($students->take(20) as $student) {
    Attendance::create([
        'student_id' => $student->id,
        'date' => $today,
        'status' => ['present', 'absent', 'late', 'excused'][rand(0, 3)],
        'recorded_by' => 1,
    ]);
}
```

### 4. Build Assets

```bash
npm run build
# or for development
npm run dev
```

### 5. Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## File Structure

All files have been created in the following structure:

```
attendance-system/
├── app/
│   └── Http/
│       └── Controllers/
│           └── DashboardController.php          ✅ Created
├── resources/
│   └── js/
│       ├── pages/
│       │   └── Dashboard.vue                    ✅ Updated
│       ├── Components/
│       │   └── Dashboard/
│       │       ├── StatCard.vue                 ✅ Created
│       │       ├── AttendanceSummaryCard.vue    ✅ Created
│       │       ├── AttendanceChart.vue          ✅ Created
│       │       ├── RecentActivity.vue           ✅ Created
│       │       └── index.ts                     ✅ Created
│       └── composables/
│           └── useDashboard.ts                  ✅ Created
├── routes/
│   └── web.php                                  ✅ Updated
└── docs/
    ├── DASHBOARD_MODULE.md                      ✅ Created
    └── DASHBOARD_SETUP.md                       ✅ Created
```

## Verification

### 1. Check Routes

```bash
php artisan route:list | grep dashboard
```

Expected output:
```
GET|HEAD  dashboard .... dashboard › DashboardController@index
```

### 2. Access Dashboard

Navigate to: `http://your-app.test/dashboard`

You should see:
- ✅ Today's attendance statistics (4 cards at top)
- ✅ Today's detailed summary with progress bar
- ✅ Monthly attendance chart (bar/line toggle)
- ✅ Overall statistics cards (right sidebar)
- ✅ Recent activity feed (right sidebar)

### 3. Test Features

- [ ] Click bar/line chart toggle buttons
- [ ] Check if percentages calculate correctly
- [ ] Verify attendance rate badge color
- [ ] Toggle dark mode (chart should update colors)
- [ ] Check responsive layout on mobile
- [ ] Verify recent activity shows latest records

## Common Issues & Solutions

### Issue: "Controller not found"

**Solution:**
```bash
composer dump-autoload
php artisan route:clear
```

### Issue: "Chart not rendering"

**Solution:**
```bash
# Reinstall dependencies
npm install chart.js @iconify/vue
npm run build
```

### Issue: "No data showing"

**Solution:**
1. Check if you have attendance records in database
2. Create test data (see step 3 above)
3. Check Laravel logs: `tail -f storage/logs/laravel.log`

### Issue: "Component not found"

**Solution:**
```bash
# Clear Vite cache
rm -rf node_modules/.vite
npm run build
```

### Issue: "Dark mode colors not working"

**Solution:**
- Ensure your app layout has dark mode toggle
- Check if `<html class="dark">` is applied
- Chart component watches for class changes

## Configuration

### Attendance Rate Thresholds

To change what's considered "good" or "warning" attendance:

Edit `resources/js/composables/useDashboard.ts`:

```typescript
const getAttendanceRateStatus = (rate: number) => {
    if (rate >= 90) return 'good';      // Change: 90 to your threshold
    if (rate >= 75) return 'warning';   // Change: 75 to your threshold
    return 'bad';
};
```

### Chart Colors

To customize chart colors:

Edit `app/Http/Controllers/DashboardController.php` in `getMonthlyChartData()`:

```php
'datasets' => [
    [
        'label' => 'Present',
        'backgroundColor' => 'rgba(34, 197, 94, 0.8)',   // Change color
        'borderColor' => 'rgba(34, 197, 94, 1)',
        // ...
    ],
    // ... other datasets
],
```

### Recent Activity Limit

To show more/fewer recent records:

Edit `app/Http/Controllers/DashboardController.php` in `getRecentActivity()`:

```php
->limit(5)  // Change: 5 to your desired number
```

## Performance Optimization

### 1. Database Indexes

```sql
-- Run these if not already present
CREATE INDEX idx_attendance_date ON attendances(date);
CREATE INDEX idx_attendance_status ON attendances(status);
CREATE INDEX idx_attendance_created_at ON attendances(created_at);
```

### 2. Laravel Optimization

```bash
# In production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Frontend Optimization

```bash
# Production build
npm run build

# Enable gzip compression in your web server (nginx/apache)
```

## Testing Checklist

After installation, verify these features:

### Data Display
- [ ] Today's stats show correct numbers
- [ ] Monthly chart displays all days
- [ ] Chart totals match database records
- [ ] Recent activity shows latest 5 records
- [ ] All percentages calculate correctly

### Interactions
- [ ] Bar/Line chart toggle works
- [ ] Chart is responsive (resize browser)
- [ ] Dark mode toggle updates all components
- [ ] Hover tooltips work on chart
- [ ] No console errors

### Edge Cases
- [ ] Dashboard works with no attendance data
- [ ] Dashboard works with only 1 student
- [ ] Dashboard works on first day of month
- [ ] Empty state shows in recent activity

## Next Steps

After successful setup:

1. **Customize Colors**: Match your brand colors
2. **Add Filters**: Implement class/section filters
3. **Export Features**: Add PDF/Excel export
4. **Real-time Updates**: Implement WebSocket for live data
5. **Mobile App**: Use the same API for mobile apps

## Support & Documentation

- **Full Documentation**: See `docs/DASHBOARD_MODULE.md`
- **Laravel Logs**: `storage/logs/laravel.log`
- **Browser Console**: Check for JavaScript errors
- **Network Tab**: Verify API responses

## Success Criteria

Your dashboard is working correctly when:

1. ✅ All statistics display without errors
2. ✅ Chart renders with correct data
3. ✅ Dark mode works smoothly
4. ✅ Responsive on all screen sizes
5. ✅ No console errors
6. ✅ Fast load times (< 2 seconds)

---

**Setup Time:** ~5-10 minutes  
**Difficulty:** Intermediate  
**Last Updated:** November 16, 2025

