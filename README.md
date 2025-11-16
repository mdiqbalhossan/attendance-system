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

## 🐳 Running with Docker

### Prerequisites

- **Docker** >= 20.10
- **Docker Compose** >= 2.0

### Docker Setup

#### 1. Build and Start Containers

```bash
# Build and start all containers in detached mode
docker-compose up -d --build

# Or use the shorthand
docker compose up -d --build
```

This will start:
- 🐘 **PHP-FPM Container** - Laravel application
- 🗄️ **MySQL Container** - Database server
- 🌐 **Nginx Container** - Web server
- ⚡ **Node Container** - Vite dev server (development only)

#### 2. Install Dependencies Inside Container

```bash
# Install PHP dependencies
docker-compose exec app composer install

# Install JavaScript dependencies
docker-compose exec app npm install

# Generate application key
docker-compose exec app php artisan key:generate
```

#### 3. Run Migrations and Seeders

```bash
# Run migrations
docker-compose exec app php artisan migrate

# Run migrations with seeding
docker-compose exec app php artisan migrate:fresh --seed
```

#### 4. Access the Application

Once containers are running, access the application at:
- 🌐 **Application:** `http://localhost:8000`
- 🗄️ **MySQL:** `localhost:3306` (from host machine)

### Common Docker Commands

```bash
# View running containers
docker-compose ps

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f nginx

# Stop containers
docker-compose down

# Stop and remove volumes (WARNING: Deletes database data)
docker-compose down -v

# Restart containers
docker-compose restart

# Execute commands in app container
docker-compose exec app php artisan optimize:clear
docker-compose exec app php artisan route:list

# Access container shell
docker-compose exec app bash
docker-compose exec mysql bash
```

### Docker Development Workflow

```bash
# Start development environment
docker-compose up -d

# Watch frontend assets with hot reload
docker-compose exec app npm run dev

# Run tests inside container
docker-compose exec app ./vendor/bin/pest

# Format code
docker-compose exec app ./vendor/bin/pint
docker-compose exec app npm run format

# Stop everything when done
docker-compose down
```

### Docker Production Build

```bash
# Build production image
docker build -t attendance-system:latest .

# Run production container
docker run -d -p 8000:80 \
  --env-file .env.production \
  --name attendance-system \
  attendance-system:latest
```

### Docker Troubleshooting

#### Issue: Port already in use

```bash
# Change port in docker-compose.yml or use different port
docker-compose up -d --force-recreate

# Or find and stop conflicting service
docker ps
docker stop <container-id>
```

#### Issue: Permission errors

```bash
# Fix storage and cache permissions
docker-compose exec app chmod -R 777 storage bootstrap/cache
```

#### Issue: Database connection refused

```bash
# Ensure MySQL container is running
docker-compose ps

# Check MySQL logs
docker-compose logs mysql

# Restart MySQL container
docker-compose restart mysql
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

