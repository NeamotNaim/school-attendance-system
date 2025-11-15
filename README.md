# School Attendance System

A comprehensive Mini School Attendance System built with Laravel 12 backend and Vue.js 3 frontend, featuring AI-assisted development workflow documentation.

## Features

### Backend (Laravel)
- ✅ Student Management (CRUD operations)
- ✅ Attendance Module with bulk recording
- ✅ Monthly attendance reports with query optimization
- ✅ Service Layer for business logic
- ✅ Custom Artisan command for report generation
- ✅ Event/Listener system for notifications
- ✅ Redis caching for attendance statistics
- ✅ Laravel Sanctum authentication

### Frontend (Vue.js 3)
- ✅ Student List with search/filter and pagination
- ✅ Attendance Recording Interface with bulk actions
- ✅ Real-time attendance percentage
- ✅ Dashboard with today's summary
- ✅ Monthly attendance chart (Chart.js)

## Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL >= 8.0 or PostgreSQL >= 13
- Redis (optional, for caching)

## Installation

### Backend Setup

1. Clone the repository:
```bash
git clone <repository-url>
cd School-attendenc-system
```

2. Navigate to backend directory:
```bash
cd backend
```

3. Install dependencies:
```bash
composer install
```

4. Copy environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_attendance
DB_USERNAME=root
DB_PASSWORD=your_password
```

7. Run migrations:
```bash
php artisan migrate
```

8. (Optional) Seed database:
```bash
php artisan db:seed
```

9. Create storage link:
```bash
php artisan storage:link
```

10. Start the server:
```bash
php artisan serve
```

### Frontend Setup

1. Navigate to frontend directory:
```bash
cd frontend
```

2. Install dependencies:
```bash
npm install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Configure API URL in `.env`:
```env
VITE_API_URL=http://localhost:8000/api
```

5. Start development server:
```bash
npm run dev
```

## Docker Setup (Optional)

1. Build and start containers:
```bash
docker-compose up -d
```

2. Run migrations:
```bash
docker-compose exec app php artisan migrate
```

3. Access the application:
- Backend: http://localhost:8000
- Frontend: http://localhost:3000

## Database Setup

### Manual Setup

1. Create database:
```sql
CREATE DATABASE school_attendance;
```

2. Run migrations:
```bash
php artisan migrate
```

### Using Artisan

```bash
php artisan migrate:fresh --seed
```

## API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user
- `GET /api/user` - Get authenticated user

### Students
- `GET /api/students` - List students (with pagination, search, filter)
- `POST /api/students` - Create student
- `GET /api/students/{id}` - Get student
- `PUT /api/students/{id}` - Update student
- `DELETE /api/students/{id}` - Delete student

### Attendance
- `GET /api/attendances` - List attendances
- `POST /api/attendances` - Create single attendance
- `POST /api/attendances/bulk` - Bulk attendance recording
- `GET /api/attendances/report` - Monthly attendance report
- `GET /api/attendances/statistics` - Today's statistics
- `GET /api/attendances/{id}` - Get attendance
- `PUT /api/attendances/{id}` - Update attendance
- `DELETE /api/attendances/{id}` - Delete attendance

## Artisan Commands

### Generate Attendance Report
```bash
php artisan attendance:generate-report {month} {class?}
```

Example:
```bash
php artisan attendance:generate-report 2024-01
php artisan attendance:generate-report 2024-01 "10"
```

## Testing

Run tests:
```bash
php artisan test
```

Run specific test:
```bash
php artisan test --filter StudentTest
```

## Test Credentials

Default test user (if seeded):
- Email: `admin@example.com`
- Password: `password`

## Project Structure

```
School-attendenc-system/
├── backend/
│   ├── app/
│   │   ├── Console/Commands/
│   │   ├── Events/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   └── Resources/
│   │   ├── Listeners/
│   │   ├── Models/
│   │   └── Services/
│   ├── database/
│   │   ├── factories/
│   │   ├── migrations/
│   │   └── seeders/
│   └── tests/
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   ├── router/
│   │   ├── services/
│   │   └── views/
│   └── public/
└── docker-compose.yml
```

## Technologies Used

- **Backend**: Laravel 12, PHP 8.2, MySQL/PostgreSQL, Redis
- **Frontend**: Vue.js 3, Vue Router, Axios, Chart.js
- **Authentication**: Laravel Sanctum
- **Testing**: PHPUnit

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](LICENSE.md).

## Author

Built with AI assistance (Claude Code/Cursor)

