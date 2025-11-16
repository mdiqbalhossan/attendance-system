# Testing Guide - Attendance System

## 🚀 Quick Start

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suites
```bash
# Unit tests only
php artisan test --testsuite=Unit

# Feature tests only
php artisan test --testsuite=Feature
```

### Run Specific Test Files
```bash
# Student CRUD tests
php artisan test --filter=StudentCrudTest

# Attendance Service tests
php artisan test --filter=AttendanceServiceTest

# Monthly Report tests
php artisan test --filter=MonthlyReportTest

# Bulk Attendance tests
php artisan test --filter=BulkAttendanceTest
```

### Run Specific Test Methods
```bash
# Run a single test
php artisan test --filter=it_can_create_a_student
```

---

## 🧪 Test Files Overview

### Unit Tests (`tests/Unit/`)
1. **StudentModelTest.php** - Student model behavior (13 tests)
2. **AttendanceModelTest.php** - Attendance model behavior (16 tests)
3. **AttendanceServiceTest.php** - Service layer logic (17 tests)

### Feature Tests (`tests/Feature/`)
1. **StudentCrudTest.php** - Student CRUD operations (21 tests)
2. **BulkAttendanceTest.php** - Bulk attendance recording (19 tests)
3. **MonthlyReportTest.php** - Monthly report generation (14 tests)
4. **GenerateReportCommandTest.php** - Console command (11 tests)

---

## 🔧 Test Database Setup

Tests use SQLite in-memory database by default:

```env
# .env.testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

To run migrations for tests:
```bash
php artisan test --migrate
```

---

## 📊 Coverage

Generate code coverage report (requires Xdebug):
```bash
php artisan test --coverage
```

For detailed HTML coverage report:
```bash
php artisan test --coverage-html coverage-report
```

---

## 🐛 Debugging Tests

### Run tests with output
```bash
php artisan test --testdox
```

### Stop on first failure
```bash
php artisan test --stop-on-failure
```

### Run in parallel (faster)
```bash
php artisan test --parallel
```

---

## ✅ CSRF Fix for Feature Tests

If you encounter 419 errors in feature tests, the CSRF middleware needs to be disabled for testing.

**Already implemented in `tests/TestCase.php`:**
```php
protected function setUp(): void
{
    parent::setUp();
    $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
}
```

Alternatively, you can add it to individual test classes:
```php
protected function setUp(): void
{
    parent::setUp();
    $this->withoutMiddleware();
}
```

---

## 🎯 Test Coverage Summary

| Feature | Test Count | Status |
|---------|------------|--------|
| Student CRUD | 21 | ✅ |
| Student Model | 13 | ✅ |
| Bulk Attendance | 19 | ✅ |
| Attendance Model | 16 | ✅ |
| Attendance Service | 17 | ✅ |
| Monthly Reports | 14 | ✅ |
| Console Commands | 11 | ✅ |
| **Total** | **111+** | **✅** |

---

## 🔍 Code Quality

### Run Linter (Laravel Pint)
```bash
# Check for issues
vendor/bin/pint --test

# Fix issues automatically
vendor/bin/pint
```

### Check for deprecations
```bash
php artisan test --testdox 2>&1 | grep "WARN"
```

---

## 📝 Writing New Tests

### Unit Test Template
```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_example_behavior(): void
    {
        // Arrange
        $data = ['key' => 'value'];

        // Act
        $result = someFunction($data);

        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### Feature Test Template
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/example');

        $response->assertStatus(200);
    }
}
```

---

## 🚨 Common Issues & Solutions

### Issue: "Database file at path [database/database.sqlite] does not exist"
**Solution:**
```bash
touch database/database.sqlite
php artisan migrate --env=testing
```

### Issue: "Class not found"
**Solution:**
```bash
composer dump-autoload
```

### Issue: "419 Page Expired"
**Solution:** CSRF middleware needs to be disabled (see CSRF Fix section above)

### Issue: Tests are slow
**Solution:**
```bash
# Run tests in parallel
php artisan test --parallel

# Use in-memory SQLite
# Check DB_DATABASE=:memory: in .env.testing
```

---

## 📚 Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Pint Documentation](https://laravel.com/docs/pint)

---

## ✨ Best Practices

1. **Always use factories** for creating test data
2. **Use descriptive test names** that explain what is being tested
3. **Follow AAA pattern**: Arrange, Act, Assert
4. **Keep tests independent** - each test should be able to run alone
5. **Use RefreshDatabase** trait to ensure clean database state
6. **Mock external services** to avoid dependencies
7. **Test edge cases** and error conditions, not just happy paths

---

*Last Updated: November 16, 2025*

