# Database Seeding Guide

This guide explains how to seed the database with sample data for the School Attendance System.

## What Gets Seeded

1. **Admin User**
   - Email: `admin@example.com`
   - Password: `password`

2. **Classes** (10 classes)
   - Class 1 through Class 10
   - Each with capacity of 35-40 students

3. **Sections** (30 sections)
   - 3 sections (A, B, C) per class
   - Each section has capacity of 30 students

4. **Students** (~750-900 students)
   - 25-30 students per section
   - Realistic names and student IDs
   - Distributed across all classes and sections

5. **Holidays** (20+ entries)
   - National holidays (New Year, Independence Day, etc.)
   - School events (Science Fair, Sports Day, etc.)
   - Exam periods (Mid-terms, Finals, etc.)

## How to Seed

### Fresh Database (Recommended)

This will drop all tables, recreate them, and seed with fresh data:

```bash
cd backend
php artisan migrate:fresh --seed
```

### Seed Only (Keep Existing Data)

If you want to add seed data without dropping tables:

```bash
cd backend
php artisan db:seed
```

### Seed Specific Seeders

You can run individual seeders:

```bash
# Seed only classes
php artisan db:seed --class=ClassSeeder

# Seed only sections
php artisan db:seed --class=SectionSeeder

# Seed only students
php artisan db:seed --class=StudentSeeder

# Seed only holidays
php artisan db:seed --class=HolidaySeeder
```

## Order of Seeding

**Important:** Seeders must be run in this order due to dependencies:

1. `ClassSeeder` - Must run first
2. `SectionSeeder` - Depends on classes
3. `StudentSeeder` - Depends on classes
4. `HolidaySeeder` - Independent

The `DatabaseSeeder` automatically runs them in the correct order.

## Sample Data Details

### Classes
- Class 1 (CLS-1) - First Grade
- Class 2 (CLS-2) - Second Grade
- ... up to Class 10

### Sections per Class
- Section A (e.g., CLS-1-A)
- Section B (e.g., CLS-1-B)
- Section C (e.g., CLS-1-C)

### Student IDs
- Format: STU0001, STU0002, etc.
- Sequential numbering

### Holiday Types
- **holiday** - School holidays and breaks
- **exam** - Examination periods
- **event** - School events and activities

## Testing the Seeded Data

After seeding, you can test:

1. **Login**
   ```
   Email: admin@example.com
   Password: password
   ```

2. **View Students**
   - Navigate to Students page
   - Filter by Class 5, Section A
   - Should see 25-30 students

3. **Record Attendance**
   - Go to Attendance page
   - Select Class 5, Section A
   - Click "Load Students"
   - Mark attendance for all students

4. **View Holidays**
   - Navigate to Holidays page
   - See all holidays, exams, and events
   - Filter by type or year

## Troubleshooting

### Error: "No classes found"
Run seeders in order:
```bash
php artisan db:seed --class=ClassSeeder
php artisan db:seed --class=SectionSeeder
php artisan db:seed --class=StudentSeeder
```

### Error: "Duplicate entry"
The database already has data. Use `migrate:fresh --seed` to start clean:
```bash
php artisan migrate:fresh --seed
```

### Want to reset and reseed?
```bash
php artisan migrate:fresh --seed
```

This will:
1. Drop all tables
2. Run all migrations
3. Seed all data

## Customizing Seed Data

You can modify the seeders in `database/seeders/`:
- `ClassSeeder.php` - Add/modify classes
- `SectionSeeder.php` - Change section names or count
- `StudentSeeder.php` - Adjust student names or count
- `HolidaySeeder.php` - Add/modify holidays

After modifying, run:
```bash
php artisan migrate:fresh --seed
```
