# API Documentation

## School Attendance Management System API

**Base URL:** `http://localhost:8000/api`  
**Version:** 1.0  
**Authentication:** Laravel Sanctum (Bearer Token)

---

## Table of Contents

- [Authentication](#authentication)
- [Students](#students)
- [Attendance](#attendance)
- [Reports](#reports)
- [Classes & Sections](#classes--sections)
- [Holidays](#holidays)
- [Notifications](#notifications)
- [Error Responses](#error-responses)

---

## Authentication

### Login

Authenticate a user and receive an access token.

**Endpoint:** `POST /login`

**Request Body:**
```json
{
  "email": "admin@school.com",
  "password": "password"
}
```

**Response:** `200 OK`
```json
{
  "access_token": "5|OZqI0xjOrkcxfUE5agZeBORQ8IpqMRzNXKyMhT0cbf907ac9",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@school.com",
    "role": "admin",
    "created_at": "2024-11-17T00:00:00.000000Z"
  }
}
```

**Error Response:** `401 Unauthorized`
```json
{
  "message": "Invalid credentials"
}
```

---

### Register

Register a new user account.

**Endpoint:** `POST /register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:** `201 Created`
```json
{
  "access_token": "6|token_string_here",
  "token_type": "Bearer",
  "user": {
    "id": 2,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-11-17T00:00:00.000000Z"
  }
}
```

---

### Logout

Revoke the current access token.

**Endpoint:** `POST /logout`  
**Authentication:** Required

**Response:** `200 OK`
```json
{
  "message": "Logged out successfully"
}
```

---

## Students

All student endpoints require authentication.

### List Students

Get a paginated list of students with optional filtering.

**Endpoint:** `GET /students`  
**Authentication:** Required

**Query Parameters:**
- `page` (integer, optional) - Page number (default: 1)
- `per_page` (integer, optional) - Items per page (default: 15)
- `search` (string, optional) - Search by name or student ID
- `class` (string, optional) - Filter by class
- `section` (string, optional) - Filter by section

**Example Request:**
```
GET /students?page=1&per_page=15&class=10&section=A
```

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "student_id": "STU0001",
      "name": "John Doe",
      "class": "10",
      "section": "A",
      "photo": "http://localhost:8000/storage/photos/student1.jpg",
      "created_at": "2024-11-17T00:00:00.000000Z",
      "updated_at": "2024-11-17T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

---

### Get Student

Get details of a specific student.

**Endpoint:** `GET /students/{id}`  
**Authentication:** Required

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "student_id": "STU0001",
    "name": "John Doe",
    "email": "john.doe@school.com",
    "phone": "+1234567890",
    "date_of_birth": "2010-05-15",
    "gender": "male",
    "address": "123 Main St, City",
    "guardian_name": "Jane Doe",
    "guardian_phone": "+1234567891",
    "class": "10",
    "section": "A",
    "photo": "http://localhost:8000/storage/photos/student1.jpg",
    "status": "active",
    "created_at": "2024-11-17T00:00:00.000000Z",
    "updated_at": "2024-11-17T00:00:00.000000Z"
  }
}
```

---

### Create Student

Create a new student record.

**Endpoint:** `POST /students`  
**Authentication:** Required

**Request Body:**
```json
{
  "student_id": "STU0001",
  "name": "John Doe",
  "email": "john.doe@school.com",
  "phone": "+1234567890",
  "date_of_birth": "2010-05-15",
  "gender": "male",
  "address": "123 Main St, City",
  "guardian_name": "Jane Doe",
  "guardian_phone": "+1234567891",
  "class": "10",
  "section": "A",
  "photo": "base64_encoded_image_or_file"
}
```

**Required Fields:**
- `student_id` (string, unique)
- `name` (string)
- `class` (string)
- `section` (string)

**Response:** `201 Created`
```json
{
  "message": "Student created successfully",
  "data": {
    "id": 1,
    "student_id": "STU0001",
    "name": "John Doe",
    "class": "10",
    "section": "A",
    "created_at": "2024-11-17T00:00:00.000000Z"
  }
}
```

---

### Update Student

Update an existing student record.

**Endpoint:** `PUT /students/{id}`  
**Authentication:** Required

**Request Body:** (all fields optional)
```json
{
  "name": "John Smith",
  "email": "john.smith@school.com",
  "class": "11",
  "section": "B"
}
```

**Response:** `200 OK`
```json
{
  "message": "Student updated successfully",
  "data": {
    "id": 1,
    "student_id": "STU0001",
    "name": "John Smith",
    "class": "11",
    "section": "B",
    "updated_at": "2024-11-17T01:00:00.000000Z"
  }
}
```

---

### Delete Student

Delete a student record.

**Endpoint:** `DELETE /students/{id}`  
**Authentication:** Required

**Response:** `200 OK`
```json
{
  "message": "Student deleted successfully"
}
```

---

### Get Student Attendance History

Get attendance history for a specific student.

**Endpoint:** `GET /students/{id}/attendance-history`  
**Authentication:** Required

**Query Parameters:**
- `start_date` (date, optional) - Start date (Y-m-d)
- `end_date` (date, optional) - End date (Y-m-d)

**Response:** `200 OK`
```json
{
  "data": {
    "student": {
      "id": 1,
      "student_id": "STU0001",
      "name": "John Doe",
      "class": "10",
      "section": "A"
    },
    "period": {
      "start_date": "2024-11-01",
      "end_date": "2024-11-17"
    },
    "summary": {
      "total_days": 17,
      "present_days": 15,
      "absent_days": 2,
      "late_days": 0,
      "attendance_percentage": 88.24
    },
    "attendances": [
      {
        "date": "2024-11-17",
        "status": "present",
        "note": null,
        "recorded_by": "Admin User"
      }
    ]
  }
}
```

---

## Attendance

### Record Bulk Attendance

Record attendance for multiple students at once.

**Endpoint:** `POST /attendances/bulk`  
**Authentication:** Required

**Request Body:**
```json
{
  "date": "2024-11-17",
  "students": [
    {
      "student_id": 1,
      "status": "present",
      "note": null
    },
    {
      "student_id": 2,
      "status": "absent",
      "note": "Sick"
    },
    {
      "student_id": 3,
      "status": "late",
      "note": "Traffic"
    }
  ]
}
```

**Status Values:**
- `present` - Student is present
- `absent` - Student is absent
- `late` - Student arrived late
- `excused` - Excused absence

**Response:** `201 Created`
```json
{
  "message": "Attendance recorded successfully",
  "data": {
    "success": true,
    "recorded": 3,
    "date": "2024-11-17",
    "summary": {
      "present": 1,
      "absent": 1,
      "late": 1,
      "total": 3
    }
  }
}
```

---

### Get Attendance Statistics

Get attendance statistics for a specific date.

**Endpoint:** `GET /attendances/statistics`  
**Authentication:** Required

**Query Parameters:**
- `date` (date, optional) - Date (Y-m-d), defaults to today

**Response:** `200 OK`
```json
{
  "data": {
    "date": "2024-11-17",
    "total_students": 150,
    "present": 140,
    "absent": 8,
    "late": 2,
    "excused": 0,
    "recorded": 150,
    "attendance_percentage": 93.33
  }
}
```

---

### Get Dashboard Overview

Get comprehensive dashboard statistics.

**Endpoint:** `GET /attendance/dashboard`  
**Authentication:** Required

**Query Parameters:**
- `date` (date, optional) - Date (Y-m-d), defaults to today

**Response:** `200 OK`
```json
{
  "data": {
    "today": {
      "date": "2024-11-17",
      "total_students": 150,
      "present": 140,
      "absent": 8,
      "late": 2,
      "attendance_percentage": 93.33
    },
    "weekly": {
      "summary": {
        "total_students": 150,
        "average_attendance": 91.5
      },
      "daily_statistics": [
        {
          "date": "2024-11-11",
          "present": 138,
          "absent": 10,
          "late": 2
        }
      ]
    },
    "monthly": {
      "summary": {
        "total_students": 150,
        "average_attendance": 92.3
      }
    },
    "recent_trends": [
      {
        "date": "2024-11-17",
        "present": 140,
        "absent": 8,
        "late": 2,
        "attendance_percentage": 93.33
      }
    ],
    "low_attendance_alerts": {
      "count": 5,
      "students": [
        {
          "student_id": "STU0050",
          "name": "Jane Smith",
          "attendance_percentage": 65.5
        }
      ]
    },
    "class_summary": [
      {
        "class_id": 1,
        "class_name": "10",
        "total_students": 30,
        "present_today": 28
      }
    ]
  }
}
```

---

## Reports

### Daily Report

Get daily attendance report.

**Endpoint:** `GET /reports/daily`  
**Authentication:** Required

**Query Parameters:**
- `date` (date, optional) - Date (Y-m-d), defaults to today
- `class` (string, optional) - Filter by class
- `section` (string, optional) - Filter by section

**Response:** `200 OK`
```json
{
  "data": {
    "date": "2024-11-17",
    "class": "10",
    "section": "A",
    "total_students": 30,
    "summary": {
      "present": 28,
      "absent": 2,
      "late": 0,
      "attendance_percentage": 93.33
    },
    "students": [
      {
        "student_id": "STU0001",
        "name": "John Doe",
        "status": "present",
        "note": null
      }
    ]
  }
}
```

---

### Weekly Report

Get weekly attendance report.

**Endpoint:** `GET /reports/weekly`  
**Authentication:** Required

**Query Parameters:**
- `start_date` (date, optional) - Week start date (Y-m-d)
- `class` (string, optional) - Filter by class
- `section` (string, optional) - Filter by section

**Response:** `200 OK`
```json
{
  "data": {
    "start_date": "2024-11-11",
    "end_date": "2024-11-17",
    "class": "10",
    "section": "A",
    "total_students": 30,
    "daily_statistics": [
      {
        "date": "2024-11-11",
        "present": 28,
        "absent": 2,
        "late": 0,
        "attendance_percentage": 93.33
      }
    ],
    "students": [
      {
        "student_id": "STU0001",
        "name": "John Doe",
        "present_days": 5,
        "absent_days": 0,
        "late_days": 0,
        "attendance_percentage": 100
      }
    ]
  }
}
```

---

### Monthly Report

Get monthly attendance report.

**Endpoint:** `GET /reports/monthly`  
**Authentication:** Required

**Query Parameters:**
- `month` (string, optional) - Month (Y-m), defaults to current month
- `class` (string, optional) - Filter by class
- `section` (string, optional) - Filter by section

**Response:** `200 OK`
```json
{
  "data": {
    "month": "2024-11",
    "class": "10",
    "section": "A",
    "total_students": 30,
    "summary": {
      "total_students": 30,
      "total_present": 420,
      "total_absent": 30,
      "total_late": 10
    },
    "students": [
      {
        "student_id": "STU0001",
        "name": "John Doe",
        "class": "10",
        "section": "A",
        "total_days": 17,
        "recorded_days": 17,
        "present_days": 15,
        "absent_days": 2,
        "late_days": 0,
        "attendance_percentage": 88.24
      }
    ]
  }
}
```

---

### Class Comparison

Get class-wise attendance comparison.

**Endpoint:** `GET /reports/class-comparison`  
**Authentication:** Required

**Query Parameters:**
- `month` (string, optional) - Month (Y-m), defaults to current month

**Response:** `200 OK`
```json
{
  "month": "2024-11",
  "data": [
    {
      "class_id": 1,
      "class_name": "10",
      "total_students": 30,
      "total_present": 420,
      "total_absent": 30,
      "total_days": 17,
      "average_attendance_percentage": 93.33
    },
    {
      "class_id": 2,
      "class_name": "9",
      "total_students": 28,
      "total_present": 390,
      "total_absent": 35,
      "total_days": 17,
      "average_attendance_percentage": 91.76
    }
  ]
}
```

---

### Low Attendance Students

Get students with attendance below threshold.

**Endpoint:** `GET /reports/low-attendance`  
**Authentication:** Required

**Query Parameters:**
- `threshold` (float, optional) - Percentage threshold (default: 75.0)
- `month` (string, optional) - Month (Y-m), defaults to current month

**Response:** `200 OK`
```json
{
  "data": {
    "threshold": 75.0,
    "month": "2024-11",
    "count": 5,
    "students": [
      {
        "student_id": "STU0050",
        "name": "Jane Smith",
        "class": "10",
        "section": "A",
        "present_days": 10,
        "absent_days": 7,
        "total_days": 17,
        "attendance_percentage": 58.82
      }
    ]
  }
}
```

---

### Attendance Trends

Get attendance trends over time.

**Endpoint:** `GET /reports/trends`  
**Authentication:** Required

**Query Parameters:**
- `start_date` (date, optional) - Start date (Y-m-d)
- `end_date` (date, optional) - End date (Y-m-d)
- `class_id` (integer, optional) - Filter by class ID

**Response:** `200 OK`
```json
{
  "data": [
    {
      "month": "2024-09",
      "total_students": 150,
      "average_attendance_percentage": 91.5
    },
    {
      "month": "2024-10",
      "total_students": 150,
      "average_attendance_percentage": 92.3
    },
    {
      "month": "2024-11",
      "total_students": 150,
      "average_attendance_percentage": 93.1
    }
  ]
}
```

---

## Classes & Sections

### List Classes

Get all classes.

**Endpoint:** `GET /classes`  
**Authentication:** Required

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "name": "10",
      "code": "CLASS-10",
      "description": "Class 10",
      "is_active": true,
      "created_at": "2024-11-17T00:00:00.000000Z"
    }
  ]
}
```

---

### Create Class

Create a new class.

**Endpoint:** `POST /classes`  
**Authentication:** Required

**Request Body:**
```json
{
  "name": "11",
  "code": "CLASS-11",
  "description": "Class 11",
  "is_active": true
}
```

**Response:** `201 Created`
```json
{
  "message": "Class created successfully",
  "data": {
    "id": 2,
    "name": "11",
    "code": "CLASS-11",
    "created_at": "2024-11-17T00:00:00.000000Z"
  }
}
```

---

### List Sections

Get all sections.

**Endpoint:** `GET /sections`  
**Authentication:** Required

**Query Parameters:**
- `class_id` (integer, optional) - Filter by class ID

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "class_id": 1,
      "name": "A",
      "capacity": 30,
      "is_active": true,
      "class": {
        "id": 1,
        "name": "10"
      }
    }
  ]
}
```

---

### Create Section

Create a new section.

**Endpoint:** `POST /sections`  
**Authentication:** Required

**Request Body:**
```json
{
  "class_id": 1,
  "name": "C",
  "capacity": 30,
  "is_active": true
}
```

**Response:** `201 Created`
```json
{
  "message": "Section created successfully",
  "data": {
    "id": 3,
    "class_id": 1,
    "name": "C",
    "capacity": 30,
    "created_at": "2024-11-17T00:00:00.000000Z"
  }
}
```

---

## Holidays

### List Holidays

Get all holidays.

**Endpoint:** `GET /holidays`  
**Authentication:** Required

**Query Parameters:**
- `year` (integer, optional) - Filter by year

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "name": "New Year's Day",
      "date": "2024-01-01",
      "type": "public",
      "description": "New Year celebration",
      "is_recurring": true
    }
  ]
}
```

---

### Create Holiday

Create a new holiday.

**Endpoint:** `POST /holidays`  
**Authentication:** Required

**Request Body:**
```json
{
  "name": "Independence Day",
  "date": "2024-07-04",
  "type": "public",
  "description": "National holiday",
  "is_recurring": true
}
```

**Response:** `201 Created`
```json
{
  "message": "Holiday created successfully",
  "data": {
    "id": 2,
    "name": "Independence Day",
    "date": "2024-07-04",
    "created_at": "2024-11-17T00:00:00.000000Z"
  }
}
```

---

## Notifications

### List Notifications

Get user notifications.

**Endpoint:** `GET /notifications`  
**Authentication:** Required

**Query Parameters:**
- `unread` (boolean, optional) - Filter unread notifications

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "type": "attendance_recorded",
      "title": "Attendance Recorded",
      "message": "Attendance recorded for Class 10 Section A",
      "data": {
        "date": "2024-11-17",
        "class": "10",
        "section": "A"
      },
      "icon": "✓",
      "color": "green",
      "priority": "normal",
      "is_read": false,
      "created_at": "2024-11-17T10:00:00.000000Z"
    }
  ]
}
```

---

### Mark Notification as Read

Mark a notification as read.

**Endpoint:** `POST /notifications/{id}/read`  
**Authentication:** Required

**Response:** `200 OK`
```json
{
  "message": "Notification marked as read"
}
```

---

### Mark All Notifications as Read

Mark all notifications as read.

**Endpoint:** `POST /notifications/read-all`  
**Authentication:** Required

**Response:** `200 OK`
```json
{
  "message": "All notifications marked as read"
}
```

---

### Delete Notification

Delete a notification.

**Endpoint:** `DELETE /notifications/{id}`  
**Authentication:** Required

**Response:** `200 OK`
```json
{
  "message": "Notification deleted successfully"
}
```

---

## Error Responses

### Validation Error

**Status Code:** `422 Unprocessable Entity`

```json
{
  "message": "Validation failed",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

---

### Unauthorized

**Status Code:** `401 Unauthorized`

```json
{
  "message": "Unauthenticated."
}
```

---

### Forbidden

**Status Code:** `403 Forbidden`

```json
{
  "message": "This action is unauthorized."
}
```

---

### Not Found

**Status Code:** `404 Not Found`

```json
{
  "message": "Resource not found."
}
```

---

### Server Error

**Status Code:** `500 Internal Server Error`

```json
{
  "message": "Server Error",
  "error": "An unexpected error occurred."
}
```

---

## Rate Limiting

API requests are rate-limited to prevent abuse:

- **Authenticated requests:** 60 requests per minute
- **Unauthenticated requests:** 10 requests per minute

**Rate Limit Headers:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1700000000
```

When rate limit is exceeded:

**Status Code:** `429 Too Many Requests`
```json
{
  "message": "Too many requests. Please try again later."
}
```

---

## Pagination

List endpoints return paginated results with the following structure:

```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "http://localhost:8000/api/students?page=1",
    "last": "http://localhost:8000/api/students?page=10",
    "prev": null,
    "next": "http://localhost:8000/api/students?page=2"
  }
}
```

---

## Testing the API

### Using cURL

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@school.com","password":"password"}'

# Get students (with token)
curl -X GET http://localhost:8000/api/students \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Record attendance
curl -X POST http://localhost:8000/api/attendances/bulk \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "date": "2024-11-17",
    "students": [
      {"student_id": 1, "status": "present"},
      {"student_id": 2, "status": "absent"}
    ]
  }'
```

### Using Postman

1. Import the API collection
2. Set environment variable `base_url` to `http://localhost:8000/api`
3. Login to get token
4. Set `token` environment variable
5. Use `{{token}}` in Authorization header

---

## Changelog

### Version 1.0 (2024-11-17)
- Initial API release
- Student management endpoints
- Attendance recording and reporting
- Authentication with Sanctum
- Notification system
- Class and section management
- Holiday management

---

## Support

For API support or questions:
- **Email:** support@example.com
- **Documentation:** http://localhost:8000/api/documentation
- **GitHub Issues:** [Repository Issues](https://github.com/yourusername/repo/issues)

---

**Last Updated:** November 17, 2024  
**API Version:** 1.0  
**Documentation Version:** 1.0
