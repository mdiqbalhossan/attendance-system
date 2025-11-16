# Dashboard Module Documentation

## Overview

The Dashboard module provides a comprehensive, dynamic overview of the attendance system with real-time statistics, charts, and activity feeds.

## Features

### 1. **Today's Attendance Summary**
- Real-time statistics for Present, Absent, Late, and Excused students
- Color-coded status indicators
- Percentage calculations
- Progress tracking (recorded vs. not recorded)
- Attendance rate with status badge (Excellent/Good/Needs Attention)

### 2. **Monthly Attendance Chart**
- Interactive Chart.js visualization
- Toggle between Bar and Line chart types
- Daily attendance breakdown for the current month
- Color-coded by status (Present, Absent, Late, Excused)
- Monthly totals summary
- Dark mode support

### 3. **Overall Statistics**
- Total students enrolled
- Weekly attendance rate
- Monthly attendance rate
- Color-coded status indicators based on performance thresholds

### 4. **Recent Activity Feed**
- Latest 5 attendance records
- Student information with avatars
- Status badges with icons
- Timestamp and recorded by information
- Real-time updates

## Architecture

### Controller Layer

**File:** `app/Http/Controllers/DashboardController.php`

The controller handles all data fetching and processing for the dashboard:

```php
public function index(): Response
{
    // Fetches today's stats, monthly chart data, overall stats, and recent activity
}
```

**Private Methods:**
- `getTodayAttendanceStats()` - Calculates today's attendance metrics
- `getMonthlyChartData()` - Prepares chart data for the entire month
- `getOverallStats()` - Computes weekly and monthly statistics
- `getRecentActivity()` - Fetches latest attendance records

### Frontend Structure

```
resources/js/
├── pages/
│   └── Dashboard.vue                    # Main dashboard page
├── Components/
│   └── Dashboard/
│       ├── StatCard.vue                 # Reusable stat card component
│       ├── AttendanceSummaryCard.vue    # Today's summary with breakdown
│       ├── AttendanceChart.vue          # Chart.js monthly visualization
│       ├── RecentActivity.vue           # Activity feed component
│       └── index.ts                     # Component exports
└── composables/
    └── useDashboard.ts                  # Dashboard logic and utilities
```

## Component Details

### StatCard Component

**File:** `resources/js/Components/Dashboard/StatCard.vue`

A versatile card component for displaying statistics.

**Props:**
- `title` (string) - Card title
- `value` (string | number) - Main value to display
- `icon` (string) - Iconify icon name
- `description` (string, optional) - Additional description
- `color` (string, optional) - Color classes for styling
- `percentage` (number, optional) - Percentage value
- `trend` ('up' | 'down' | 'neutral', optional) - Trend indicator
- `loading` (boolean, optional) - Loading state

**Usage:**
```vue
<StatCard
    title="Present"
    :value="42"
    icon="lucide:check-circle"
    color="text-green-600 bg-green-50"
    :percentage="85"
    trend="up"
/>
```

### AttendanceSummaryCard Component

**File:** `resources/js/Components/Dashboard/AttendanceSummaryCard.vue`

Displays comprehensive today's attendance summary.

**Props:**
- `todayStats` (TodayStats) - Today's attendance data

**Features:**
- Overall progress bar
- Attendance rate badge with color coding
- Breakdown by status with percentages
- Hover effects and transitions

### AttendanceChart Component

**File:** `resources/js/Components/Dashboard/AttendanceChart.vue`

Interactive monthly attendance chart using Chart.js.

**Props:**
- `monthlyChartData` (MonthlyChartData) - Chart data structure
- `currentMonth` (string) - Month name
- `currentYear` (number) - Year
- `chartType` ('bar' | 'line', optional) - Initial chart type

**Features:**
- Toggle between bar and line charts
- Dark mode support (auto-detects theme changes)
- Responsive design
- Interactive tooltips with totals
- Monthly statistics summary below chart
- Color-coded datasets:
  - Present: Green
  - Absent: Red
  - Late: Amber
  - Excused: Blue

### RecentActivity Component

**File:** `resources/js/Components/Dashboard/RecentActivity.vue`

Displays recent attendance records.

**Props:**
- `activities` (RecentActivity[]) - Array of recent activities

**Features:**
- Student avatars with initials
- Status badges with icons
- Timestamp information
- Empty state handling

## Composable: useDashboard

**File:** `resources/js/composables/useDashboard.ts`

Provides reusable logic and utilities for dashboard functionality.

**Functions:**
- `getStatusConfig(status)` - Returns color, icon, and label for status
- `getPercentage(value, total)` - Calculates percentage
- `formatNumber(num)` - Formats numbers with commas
- `getTrendIndicator(current, previous)` - Determines trend direction
- `getAttendanceRateStatus(rate)` - Categorizes attendance rate

**Computed Properties:**
- `todayStatsCards` - Array of cards for today's stats
- `overallStatsCards` - Array of cards for overall stats

## Data Types

### TodayStats
```typescript
interface TodayStats {
    date: string;
    dateFormatted: string;
    totalStudents: number;
    present: number;
    absent: number;
    late: number;
    excused: number;
    notRecorded: number;
    totalRecorded: number;
    attendanceRate: number;
}
```

### MonthlyChartData
```typescript
interface MonthlyChartData {
    labels: string[];
    datasets: ChartDataset[];
    monthlyTotals: {
        present: number;
        absent: number;
        late: number;
        excused: number;
    };
}
```

### OverallStats
```typescript
interface OverallStats {
    totalStudents: number;
    totalRecords: number;
    weeklyAttendanceRate: number;
    monthlyAttendanceRate: number;
    weeklyStats: {
        present: number;
        absent: number;
        late: number;
        excused: number;
    };
}
```

### RecentActivity
```typescript
interface RecentActivity {
    id: number;
    student_name: string;
    student_id: string;
    status: string;
    date: string;
    recorded_by: string;
    recorded_at: string;
}
```

## Styling & Theme

### Color Scheme

The dashboard uses a color-coded system for attendance statuses:

| Status | Color | Light Mode | Dark Mode |
|--------|-------|------------|-----------|
| Present | Green | `#22c55e` | `#4ade80` |
| Absent | Red | `#ef4444` | `#f87171` |
| Late | Amber | `#fbbf24` | `#fcd34d` |
| Excused | Blue | `#3b82f6` | `#60a5fa` |

### Responsive Design

- **Mobile (< 640px)**: Single column layout
- **Tablet (640px - 1024px)**: 2-column grid for stats
- **Desktop (> 1024px)**: Full layout with 3-column grid

### Dark Mode

All components support dark mode with automatic theme detection:
- Charts automatically adjust colors
- Background and text colors adapt
- Borders and shadows adjust for contrast

## Performance Considerations

### Database Optimization

1. **Scopes**: Uses Eloquent scopes for efficient queries
2. **Eager Loading**: Loads relationships with `with()`
3. **Grouping**: Uses `groupBy()` for aggregations
4. **Indexing**: Ensure `date` and `status` columns are indexed

Recommended indexes:
```sql
CREATE INDEX idx_attendance_date ON attendances(date);
CREATE INDEX idx_attendance_status ON attendances(status);
CREATE INDEX idx_attendance_student_date ON attendances(student_id, date);
```

### Frontend Optimization

1. **Lazy Loading**: Chart.js loaded only when needed
2. **Computed Properties**: Minimizes recalculations
3. **Reactive Updates**: Uses Vue 3 reactivity system
4. **Component Splitting**: Modular components for better tree-shaking

## Customization

### Changing Attendance Rate Thresholds

In `resources/js/composables/useDashboard.ts`:

```typescript
const getAttendanceRateStatus = (rate: number): 'good' | 'warning' | 'bad' => {
    if (rate >= 90) return 'good';      // Change threshold
    if (rate >= 75) return 'warning';   // Change threshold
    return 'bad';
};
```

### Adding New Statistics

1. Add data to controller:
```php
// In DashboardController.php
private function getCustomStats(): array
{
    // Your logic
}
```

2. Pass to view:
```php
return Inertia::render('Dashboard', [
    'customStats' => $this->getCustomStats(),
]);
```

3. Create component and display in Dashboard.vue

### Customizing Chart Colors

In `resources/js/Components/Dashboard/AttendanceChart.vue`:

```typescript
datasets: [
    {
        label: 'Present',
        backgroundColor: 'rgba(34, 197, 94, 0.8)', // Change color
        borderColor: 'rgba(34, 197, 94, 1)',
    },
    // ... other datasets
]
```

## Routes

```php
// routes/web.php
Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
```

## Testing

### Manual Testing Checklist

- [ ] Dashboard loads without errors
- [ ] Today's stats display correctly
- [ ] Chart renders with correct data
- [ ] Chart type toggle works (bar/line)
- [ ] Dark mode toggle updates chart colors
- [ ] Recent activity displays correctly
- [ ] Empty states show when no data
- [ ] Responsive layout works on mobile
- [ ] Statistics calculate correctly
- [ ] Percentages are accurate

### Sample Test Data

Use seeders to create test data:

```bash
php artisan db:seed --class=AttendanceSeeder
```

## Troubleshooting

### Chart Not Rendering

**Problem:** Chart canvas is blank or shows error

**Solutions:**
1. Ensure Chart.js is installed: `npm install chart.js`
2. Check console for JavaScript errors
3. Verify `monthlyChartData` prop has valid data structure
4. Check if canvas element exists in DOM

### Statistics Not Updating

**Problem:** Dashboard shows old data

**Solutions:**
1. Clear Laravel cache: `php artisan cache:clear`
2. Check if controller is fetching fresh data
3. Verify Inertia is passing data correctly
4. Check browser console for errors

### Dark Mode Colors Wrong

**Problem:** Chart colors don't match theme

**Solutions:**
1. Ensure MutationObserver is watching for theme changes
2. Check if `initChart()` is called on theme change
3. Verify color values for both light and dark modes

## Future Enhancements

Potential improvements for the dashboard:

1. **Real-time Updates**: WebSocket integration for live updates
2. **Export Features**: PDF/Excel export of reports
3. **Date Range Selector**: Custom date range for charts
4. **Drill-down**: Click chart elements to see details
5. **Comparison View**: Compare current vs. previous month
6. **Alerts**: Notifications for low attendance rates
7. **Class/Section Filter**: Filter dashboard by class or section
8. **Customizable Dashboard**: User-configurable widgets

## Dependencies

### PHP
- Laravel 11.x
- Inertia.js Server Adapter

### JavaScript
- Vue 3
- Chart.js
- @iconify/vue
- Inertia.js Client
- TailwindCSS

## Support

For issues or questions:
1. Check this documentation
2. Review component props and types
3. Check Laravel logs: `storage/logs/laravel.log`
4. Check browser console for frontend errors

---

**Last Updated:** November 16, 2025
**Module Version:** 1.0.0

