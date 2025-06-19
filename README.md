# Laravel Project Setup Guide

## **Table of Contents**
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [Running the Project](#running-the-project)
- [API Documentation](#api-documentation)

---

## **Prerequisites**
Before you begin, ensure you have the following installed on your system:
- [PHP 8.1+](https://www.php.net/downloads.php)
- [Composer](https://getcomposer.org/download/)
- [Laravel 10+](https://laravel.com/docs/10.x)
- [MySQL 8+](https://dev.mysql.com/downloads/)
- [Node.js & npm](https://nodejs.org/)
- [Git](https://git-scm.com/)
- A web server like [Apache](https://httpd.apache.org/) or [Nginx](https://www.nginx.com/)

---

## **Installation**
Clone the repository and navigate to the project folder:

```bash
git clone https://github.com/your-username/your-repository.git
cd your-repository

composer install
npm install

cp .env.example .env

php artisan key:generate

php artisan migrate --seed

php artisan passport:keys --force

** php artisan passport:install --force   # this will create migration files which are not needed **

```

## **API Usage Examples**

### **User Registration**
```http
POST /api/register
Content-Type: application/json

{
  "first_name": "John",
  "last_name": "Doe",
  "email": "johndoe3@example.com",
  "password": "password123"
}
```

### **User Login**
```http
POST /api/login
Content-Type: application/json

{
  "email": "johndoe3@example.com",
  "password": "password123"
}
```

### **User Profile**
```http
GET /api/user
Content-Type: application/json

```

### **User Logout**
```http
POST /api/logout
Content-Type: application/json

```

### **Attributes Management**

#### **Create an Attribute**
```http
POST /api/attributes
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json

{
  "name": "end_date",
  "type": "date"
}
```

#### **Retrieve an Attribute**
```http
GET /api/attributes/1
Authorization: Bearer YOUR_ACCESS_TOKEN
```

#### **Update an Attribute**
```http
PUT /api/attributes/2
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json

{
  "name": "end_date",
  "type": "date"
}
```

#### **Delete an Attribute**
```http
DELETE /api/attributes/4
Authorization: Bearer YOUR_ACCESS_TOKEN
```

### **Projects Management**

#### **Create a Project**
```http
POST /api/projects
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json

{
  "name": "Project C",
  "status": "active",
  "users": [1, 2],
  "attributes": {
    "department": "Finance",
    "start_date": "2025-01-01",
    "end_date": "2025-02-02"
  }
}
```

#### **Update a Project**
```http
PUT /api/projects/1
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json

{
  "name": "Project A Updated",
  "attributes": {
    "department": "HR",
    "end_date": "2025-12-31"
  }
}
```

#### **Retrieve Projects**
```http
GET /api/projects
Authorization: Bearer YOUR_ACCESS_TOKEN
```

#### **Retrieve a Specific Project**
```http
GET /api/projects/4
Authorization: Bearer YOUR_ACCESS_TOKEN
```

#### **Filter Projects**
```http
GET /api/projects/filter?department=Finance&start_date=2025-01-01
Authorization: Bearer YOUR_ACCESS_TOKEN
```

#### **Delete a Project**
```http
DELETE /api/projects/2
Authorization: Bearer YOUR_ACCESS_TOKEN
```

### **Timesheets Management**

#### **Create a Timesheet Entry**
```http
POST /api/timesheets
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json

{
  "task_name": "Fix Backend Bug",
  "date": "2025-03-05",
  "hours": 3.5,
  "user_id": 1,
  "project_id": 2
}
```

#### **Retrieve Timesheets**
```http
GET /api/timesheets
Authorization: Bearer YOUR_ACCESS_TOKEN
```

#### **Retrieve a Specific Timesheet Entry**
```http
GET /api/timesheets/2
Authorization: Bearer YOUR_ACCESS_TOKEN
```

#### **Update a Timesheet Entry**
```http
PUT /api/timesheets/2
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json

{
  "hours": 5
}
```

#### **Delete a Timesheet Entry**
```http
DELETE /api/timesheets/2
Authorization: Bearer YOUR_ACCESS_TOKEN
```

---

## **Notes**
- Replace `YOUR_ACCESS_TOKEN` with a valid token obtained from the login API.
- Ensure proper authentication is included for protected routes.
- Use `GET`, `POST`, `PUT`, or `DELETE` as appropriate.
- Modify ID parameters (`/api/attributes/1`, `/api/projects/4`, etc.) based on actual data.
- Raw DB is added to the repo
- Postman json is added, import it and use the apis

