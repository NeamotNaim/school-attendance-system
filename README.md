# School Attendance Management System

A comprehensive full-stack attendance management system built with Laravel 12 and Vue 3, featuring real-time statistics, automated reporting, and Redis caching for optimal performance.

## 📋 Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [Running the Project](#running-the-project)
- [Test Credentials](#test-credentials)
- [Docker Setup](#docker-setup)
- [Testing](#testing)
- [API Documentation](#api-documentation)
- [Project Structure](#project-structure)
- [Contributing](#contributing)

---

## ✨ Features

### Core Features
- ✅ **Student Management** - Complete CRUD operations with photo upload
- ✅ **Bulk Attendance Recording** - Mark attendance for entire class at once
- ✅ **Real-time Statistics** - Live attendance percentage calculations
- ✅ **Comprehensive Reports** - Daily, Weekly, and Monthly reports
- ✅ **Class Comparison** - Compare attendance across different classes
- ✅ **Low Attendance Alerts** - Automatic notifications for students below threshold
- ✅ **Export Functionality** - Export reports to CSV, JSON, and PDF

### Advanced Features
- ✅ **Redis Caching** - 10-20x performance improvement on reports
- ✅ **Event-Driven Architecture** - Automated notifications and logging
- ✅ **Service Layer** - Clean separation of business logic
- ✅ **Chart.js Integration** - Visual attendance analytics
- ✅ **Sanctum Authentication** - Secure token-based API authentication
- ✅ **Docker Support** - One-command deployment
- ✅ **Artisan Commands** - CLI tools for report generation and cache management

---

## 🛠 Tech Stack

### Backend
- **Framework:** Laravel 12.38.1
- **PHP:** 8.2+
- **Database:** SQLite (dev) / MySQL 8.0 (production)
- **Cache:** Redis 7.0
- **Authentication:** Laravel Sanctum
- **Testing:** PHPUnit 11.5

### Frontend
- **Framework:** Vue 3.5.24 (Composition API)
- **Build Tool:** Vite 6.0
- **HTTP Client:** Axios
- **Charts:** Chart.js 4.5
- **Routing:** Vue Router 4.5

### DevOps
- **Containerization:** Docker & Docker Compose
- **Web Server:** PHP Built-in / Nginx (production)
- **Process Manager:** PM2 (optional)

---

## 📦 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.0
- **npm** >= 9.0
- **Redis** >= 7.0 (optional but recommended)
- **MySQL** >= 8.0 (for production) or SQLite (for development)
- **Docker** & **Docker Compose** (optional)

### Check Versions

```bash
php --version        # Should be 8.2 or higher
composer --version   # Should be 2.0 or higher
node --version       # Should be 18.0 or higher
npm --version        # Should be 9.0 or higher
redis-cli --version  # Should be 7.0 or higher (optional)
```

---

## 🚀 Installation

### Option 1: Manual Setup (Recommended for Development)

#### 1. Clone the Repository

```bash
git clone <repository-url>
cd school-attendance-system
```

#### 2. Backend Setup

```bash
# Navigate to backend directory
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database file (for development)
touch database/database.sqlite

# Or configure MySQL in .env (for production)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=school_attendance
# DB_USERNAME=root
# DB_PASSWORD=your_password
```

#### 3. Frontend Setup

```bash
# Navigate to frontend directory
cd ../frontend

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Update API URL in .env if needed
# VITE_API_URL=http://localhost:8000
```

#### 4. Redis Setup (Optional but Recommended)

**macOS:**
```bash
brew install redis
brew services start redis
```

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install redis-server
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

**Windows:**
Download from [Redis Windows](https://github.com/microsoftarchive/redis/releases)

**Verify Redis:**
```bash
redis-cli ping
# Should return: PONG
```

**Configure Laravel to use Redis:**
```bash
# In backend/.env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

---

## 💾 Database Setup

### Development (SQLite)

```bash
cd backend

# Create database file
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed

# Or run migrations and seed together
php artisan migrate:fresh --seed
```

### Production (MySQL)

```bash
cd backend

# Update .env with MySQL credentials
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=school_attendance
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Create database
mysql -u root -p
CREATE DATABASE school_attendance;
EXIT;

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed
```

### Database Commands

```bash
# Fresh migration (drops all tables and recreates)
php artisan migrate:fresh

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status

# Seed specific seeder
php artisan db:seed --class=StudentSeeder
```

---

## ▶️ Running the Project

### Development Mode

#### Terminal 1: Backend Server

```bash
cd backend

# Start Laravel development server
php artisan serve

# Server will start at http://localhost:8000
```

#### Terminal 2: Frontend Server

```bash
cd frontend

# Start Vite development server
npm run dev

# Server will start at http://localhost:5173
```

#### Terminal 3: Redis (if using caching)

```bash
# Redis should already be running as a service
# Check status
redis-cli ping

# Or start manually
redis-server
```

### Access the Application

- **Frontend:** http://localhost:5173
- **Backend API:** http://localhost:8000/api
- **API Documentation:** http://localhost:8000/api/documentation (if configured)

---

## 🔐 Test Credentials

After running `php artisan db:seed`, you can use these credentials:

### Admin User
```
Email: admin@example.com
Password: password
```

### Teacher Users
```
Email: teacher1@school.com
Password: password

Email: teacher2@school.com
Password: password
```

### Sample Data Seeded

- **Users:** 3 (1 admin, 2 teachers)
- **Classes:** 10 (Class 1 to Class 10)
- **Sections:** 3 per class (A, B, C)
- **Students:** 300 (10 per section)
- **Attendance Records:** Last 30 days for all students
- **Holidays:** Common holidays for current year

---

## 🐳 Docker Setup

### Quick Start with Docker

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

### Docker Services

The Docker setup includes:
- **app** - Laravel backend (PHP 8.2-FPM)
- **db** - MySQL 8.0 database
- **redis** - Redis 7 cache server
- **frontend** - Vue.js frontend (Node 18)

### Docker Commands

```bash
# Build and start
docker-compose up -d --build

# Run migrations in container
docker-compose exec app php artisan migrate

# Seed database in container
docker-compose exec app php artisan db:seed

# Access backend container
docker-compose exec app bash

# Access database
docker-compose exec db mysql -u root -p

# View logs
docker-compose logs -f app
docker-compose logs -f frontend

# Stop all services
docker-compose down

# Remove volumes (clean slate)
docker-compose down -v
```

### Docker URLs

- **Frontend:** http://localhost:3000
- **Backend:** http://localhost:8000
- **MySQL:** localhost:3306
- **Redis:** localhost:6379

---

## 🧪 Testing

### Run All Tests

```bash
cd backend

# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/AttendanceTest.php

# Run specific test method
php artisan test --filter test_can_record_bulk_attendance
```

### Available Tests

```bash
# List all tests
php artisan test --list-tests

# Tests included:
# - AttendanceTest (3 tests)
# - AttendanceServiceTest (3 tests)
# - StudentTest (5 tests)
# Total: 13 tests
```

### Test Database

Tests use an in-memory SQLite database and are automatically cleaned up after each test using the `RefreshDatabase` trait.

---

## 📚 API Documentation

### Authentication Endpoints

```http
POST /api/login
POST /api/logout
POST /api/register
```

### Student Endpoints

```http
GET    /api/students              # List all students (paginated)
POST   /api/students              # Create student
GET    /api/students/{id}         # Get student details
PUT    /api/students/{id}         # Update student
DELETE /api/students/{id}         # Delete student
GET    /api/students/{id}/attendance-history  # Student attendance history
```

### Attendance Endpoints

```http
POST   /api/attendances/bulk      # Record bulk attendance
GET    /api/attendances/statistics # Get attendance statistics
GET    /api/attendance/dashboard  # Dashboard overview
```

### Report Endpoints

```http
GET    /api/reports/daily         # Daily report
GET    /api/reports/weekly        # Weekly report
GET    /api/reports/monthly       # Monthly report
GET    /api/reports/class-comparison  # Class comparison
GET    /api/reports/low-attendance    # Low attendance students
```

### Example API Request

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Get students (with token)
curl -X GET http://localhost:8000/api/students \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Record bulk attendance
curl -X POST http://localhost:8000/api/attendances/bulk \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "date": "2024-11-17",
    "students": [
      {"student_id": 1, "status": "present"},
      {"student_id": 2, "status": "absent", "note": "Sick"}
    ]
  }'
```

---

## 📁 Project Structure

```
school-attendance-system/
├── backend/                    # Laravel backend
│   ├── app/
│   │   ├── Console/
│   │   │   └── Commands/      # Artisan commands
│   │   ├── Events/            # Event classes
│   │   ├── Listeners/         # Event listeners
│   │   ├── Http/
│   │   │   ├── Controllers/   # API controllers
│   │   │   └── Resources/     # API resources
│   │   ├── Models/            # Eloquent models
│   │   └── Services/          # Business logic services
│   ├── database/
│   │   ├── migrations/        # Database migrations
│   │   ├── seeders/           # Database seeders
│   │   └── factories/         # Model factories
│   ├── routes/
│   │   └── api.php            # API routes
│   ├── tests/                 # PHPUnit tests
│   └── storage/               # File storage
│
├── frontend/                   # Vue.js frontend
│   ├── src/
│   │   ├── components/        # Vue components
│   │   ├── views/             # Page components
│   │   ├── composables/       # Composition API composables
│   │   ├── services/          # API services
│   │   └── router/            # Vue Router
│   └── public/                # Static assets
│
├── docker-compose.yml         # Docker configuration
└── README.md                  # This file
```

---

## 🔧 Useful Commands

### Backend Commands

```bash
# Clear all caches
php artisan optimize:clear

# Cache configuration
php artisan config:cache

# Generate IDE helper files
php artisan ide-helper:generate

# List all routes
php artisan route:list

# List all Artisan commands
php artisan list

# Generate attendance report
php artisan attendance:generate-report 2024-11 "10"

# Manage Redis cache
php artisan attendance:cache stats
php artisan attendance:cache clear
php artisan attendance:cache warm
```

### Frontend Commands

```bash
# Development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Lint code
npm run lint

# Format code
npm run format
```

### Redis Commands

```bash
# Check Redis connection
redis-cli ping

# View all keys
redis-cli KEYS "*"

# View attendance cache keys
redis-cli -n 1 KEYS "*attendance*"

# Flush all cache
redis-cli FLUSHALL

# Monitor Redis in real-time
redis-cli MONITOR
```

---

## 🎯 Quick Start Guide

### For First-Time Setup

```bash
# 1. Clone and navigate
git clone <repository-url>
cd school-attendance-system

# 2. Backend setup
cd backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed

# 3. Frontend setup
cd ../frontend
npm install
cp .env.example .env

# 4. Start Redis (optional)
brew services start redis  # macOS
# or
sudo systemctl start redis-server  # Linux

# 5. Start servers (in separate terminals)
cd backend && php artisan serve
cd frontend && npm run dev

# 6. Access application
# Frontend: http://localhost:5173
# Login: admin@example.com / password
```

### For Docker Setup

```bash
# 1. Clone and navigate
git clone <repository-url>
cd school-attendance-system

# 2. Start all services
docker-compose up -d

# 3. Run migrations and seed
docker-compose exec app php artisan migrate:fresh --seed

# 4. Access application
# Frontend: http://localhost:3000
# Backend: http://localhost:8000
# Login: admin@example.com / password
```

---

## 🐛 Troubleshooting

### Common Issues

#### 1. Port Already in Use

```bash
# Backend (port 8000)
php artisan serve --port=8001

# Frontend (port 5173)
npm run dev -- --port=5174
```

#### 2. Database Connection Error

```bash
# Check .env configuration
cat backend/.env | grep DB_

# For SQLite, ensure file exists
touch backend/database/database.sqlite

# For MySQL, test connection
mysql -u root -p -e "SHOW DATABASES;"
```

#### 3. Redis Connection Error

```bash
# Check if Redis is running
redis-cli ping

# Start Redis
brew services start redis  # macOS
sudo systemctl start redis-server  # Linux

# Check Redis configuration in .env
cat backend/.env | grep REDIS_
```

#### 4. Permission Errors

```bash
# Fix storage permissions
cd backend
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

#### 5. Composer/NPM Errors

```bash
# Clear Composer cache
composer clear-cache
composer install

# Clear NPM cache
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

---

## 📖 Additional Documentation

- **[API Documentation](backend/API_DOCUMENTATION.md)** - Complete API reference
- **[Redis Setup](backend/REDIS_README.md)** - Redis configuration guide
- **[Artisan Commands](backend/ARTISAN_COMMANDS.md)** - Custom command reference
- **[Events Documentation](backend/EVENTS_DOCUMENTATION.md)** - Event system guide
- **[AI Workflow](AI_WORKFLOW.md)** - AI development process
- **[Requirements Verification](REQUIREMENTS_VERIFICATION.md)** - Feature checklist
- **[Technical Requirements](TECHNICAL_REQUIREMENTS_VERIFICATION.md)** - Tech stack verification

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 for PHP code
- Use ESLint configuration for JavaScript/Vue
- Write tests for new features
- Update documentation as needed

---


## 👥 Authors

- **Neamotullah Naim** - *Initial work* - [Your GitHub](https://github.com/NeamotNaim)

---

## 🙏 Acknowledgments

- Laravel Framework
- Vue.js Team
- Chart.js Contributors
- Redis Team
- All open-source contributors

---


**Made with ❤️ using Laravel and Vue.js by Neamotullah Naim**
