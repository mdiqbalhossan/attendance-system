# 🎓 Attendance Management System

A modern, full-featured attendance management system built with **Laravel 12**, **Vue 3**, **Inertia.js**, and **TypeScript**. This system provides an intuitive interface for managing student attendance records with real-time updates and comprehensive reporting.

## ✨ Features

- 📊 **Dashboard** - Real-time attendance statistics and insights
- 👥 **Student Management** - Add, edit, and manage student records
- ✅ **Attendance Tracking** - Record daily attendance with status (Present/Absent/Late)
- 📈 **Reports & Analytics** - Generate attendance reports with visual charts
- 🔐 **Authentication** - Secure login with Laravel Fortify
- 🎨 **Modern UI** - Beautiful interface built with Tailwind CSS 4 and Reka UI
- 📱 **Responsive Design** - Works seamlessly on all devices
- 🚀 **Fast Performance** - Optimized with Vite and SSR support

## 🛠️ Tech Stack

### Backend
- **Laravel 12** - PHP Framework
- **Laravel Fortify** - Authentication
- **Laravel Wayfinder** - Routing
- **MySQL** - Database (easily switchable to SQLite/PostgreSQL)

### Frontend
- **Vue 3** - Progressive JavaScript Framework
- **Inertia.js** - Modern monolith approach
- **TypeScript** - Type-safe JavaScript
- **Tailwind CSS 4** - Utility-first CSS framework
- **Reka UI** - Vue component library
- **Chart.js** - Data visualization
- **Vite** - Next-generation frontend tooling

### Development Tools
- **Pest PHP** - Testing framework
- **ESLint** - JavaScript linter
- **Prettier** - Code formatter
- **Laravel Pint** - PHP code style fixer

## 📋 Requirements

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.x
- **NPM** >= 9.x

## 🚀 Installation & Setup

### 1. Clone the Repository

```bash
git clone <repository-url>
cd attendance-system
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

The project is configured to use **MySQL** by default. 

#### Option A: MySQL/PostgreSQL
If you prefer to use MySQL or PostgreSQL, update your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=attendance_system
DB_USERNAME=root
DB_PASSWORD=your_password
```

## 💾 Database Commands

### Run Migrations

```bash
# Run all migrations
php artisan migrate

# Run migrations with fresh database (WARNING: Deletes all data)
php artisan migrate:fresh

# Rollback last migration
php artisan migrate:rollback
```

### Seed Database

```bash
# Seed with test data (recommended for first-time setup)
php artisan db:seed

# Seed specific seeder
php artisan db:seed --class=StudentSeeder
php artisan db:seed --class=AttendanceSeeder

# Fresh migration with seeding (WARNING: Deletes all data)
php artisan migrate:fresh --seed
```

### Database Reset (Complete Fresh Start)

```bash
# Drop all tables, migrate, and seed
php artisan migrate:fresh --seed
```

## 🏃 Running the Project

### Quick Start (Recommended)

```bash
# Run everything concurrently (server + queue + vite)
composer run dev
```

This will start:
- 🌐 **Laravel Server** on `http://localhost:8000`
- 📬 **Queue Worker** for background jobs
- ⚡ **Vite Dev Server** with hot module replacement

### Manual Start (Alternative)

If you prefer to run services separately:

```bash
# Terminal 1 - Laravel Server
php artisan serve

# Terminal 2 - Queue Worker
php artisan queue:listen

# Terminal 3 - Vite Dev Server
npm run dev
```

### Production Build

```bash
# Build frontend assets for production
npm run build

# Build with SSR support
npm run build:ssr
```

### Server-Side Rendering (SSR)

```bash
# Development with SSR
composer run dev:ssr

# Start SSR server only
php artisan inertia:start-ssr
```

## 🔐 Test Credentials

### Admin Account
- **Email:** `admin@gmail.com`
- **Password:** `password`

### Sample Students
The database seeder creates 50 test students including:
- **John Doe** (STU001) - Class 10, Section A
- **Jane Smith** (STU002) - Class 10, Section A
- **Mike Johnson** (STU003) - Class 9, Section B
- **Sarah Wilson** (STU004) - Class 11, Section A
- **David Brown** (STU005) - Class 12, Section C
- Plus 45 randomly generated students

### Sample Attendance Data
The seeder generates 30 days of attendance records with realistic patterns:
- 85% Present
- 10% Absent (with reasons)
- 5% Late (with reasons)

## 🧪 Testing

```bash
# Run all tests
composer test

# Or use Pest directly
./vendor/bin/pest

# Run specific test file
./vendor/bin/pest tests/Unit/AttendanceModelTest.php

# Run with coverage
./vendor/bin/pest --coverage
```

## 📁 Project Structure

```
attendance-system/
├── app/
│   ├── Actions/          # Fortify actions
│   ├── Events/           # Event classes
│   ├── Http/
│   │   ├── Controllers/  # Application controllers
│   │   ├── Middleware/   # Custom middleware
│   │   ├── Requests/     # Form requests
│   │   └── Resources/    # API resources
│   ├── Listeners/        # Event listeners
│   ├── Models/           # Eloquent models
│   └── Services/         # Business logic services
├── database/
│   ├── factories/        # Model factories
│   ├── migrations/       # Database migrations
│   └── seeders/          # Database seeders
├── resources/
│   ├── css/              # Stylesheets
│   └── js/
│       ├── components/   # Vue components
│       ├── composables/  # Vue composables
│       ├── layouts/      # Page layouts
│       ├── pages/        # Inertia pages
│       └── types/        # TypeScript types
├── routes/
│   ├── web.php           # Web routes
│   ├── students.php      # Student routes
│   ├── attendance.php    # Attendance routes
│   └── settings.php      # Settings routes
└── tests/                # Test files
```

## 🛠️ Development Commands

### Code Quality

```bash
# Format code with Prettier
npm run format

# Check formatting
npm run format:check

# Lint JavaScript/TypeScript
npm run lint

# Fix PHP code style
./vendor/bin/pint
```

### Laravel Artisan

```bash
# Clear all caches
php artisan optimize:clear

# View logs in real-time
php artisan pail

# Generate IDE helper files
php artisan ide-helper:generate

# List all routes
php artisan route:list
```

## 🐛 Troubleshooting

### Issue: Port 8000 is already in use
```bash
# Use a different port
php artisan serve --port=8080
```



### Issue: Vite not connecting
```bash
# Clear Vite cache
rm -rf node_modules/.vite

# Restart dev server
npm run dev
```

### Issue: Queue jobs not processing
```bash
# Make sure queue worker is running
php artisan queue:listen --tries=1

# Or restart queue
php artisan queue:restart
```


---

**Made with ❤️ using Laravel, Vue, and Inertia.**
**This Document Made by Md Iqbal Hossen**

