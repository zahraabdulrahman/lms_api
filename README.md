# LMS API (Laravel)

A Learning Management System (LMS) API built with Laravel, featuring course management, student registration, role-based authentication, task scheduling, and queued email jobs.

---

## Features

- Course Management: CRUD operations with validation.
- Student Registration: Profile management with role-based access.
- Comments: Students can comment on courses with ownership checks.
- Course Registrations: Students can register for courses with restrictions (no duplicates, course availability).
- Authentication: Token-based authentication via Laravel Sanctum.
- Role-Based Authorization: Roles include Admin, Instructor, and Student.
- Task Scheduling: Daily task to sync books from Fake Books API.
- Queued Email Jobs: Email dispatching.
- 50 tests covering all features.

---

## Setup

Prerequisites
- PHP 8.4 or higher
- Composer
- SQLite
- Laravel 11.x

---

## Setup

1. Clone the repository:
   git clone https://github.com/zahraabdulrahman/lms_api.git
   cd lms_api

2. Install dependencies:
   composer install

3. Configure environment:
   cp .env.example .env
   php artisan key:generate
   (Update .env with your database credentials)

4. Run migrations:
   php artisan migrate

5. (Optional) Seed dummy data:
   php artisan db:seed

---

## API Endpoints Table  

| **HTTP Method** | **Endpoint**                              | **Description**                                                                 | **Policy**                                                                                   |
|-----------------|-------------------------------------------|---------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------|
| **POST**        | `/api/register`                           | User registration (no auth required).                                        | None                                                                                        |
| **POST**        | `/api/login`                              | Authenticate user (no auth required).                                           | None                                                                                        |
| **POST**        | `/api/logout`                             | Logout user (requires authentication).                                          | None                                                                                        |
| **POST**        | `/api/courses`                            | Create a course (Admin/Instructor only).                                        | `CoursePolicy::create` - Only `admin` or `instructor` can create.                           |
| **GET**         | `/api/courses`                            | List all courses (requires authentication).                                     | `CoursePolicy::viewAny` - All roles (`admin`, `instructor`, `student`) can view.             |
| **GET**         | `/api/courses/{course}`                   | Get a specific course (requires authentication).                                | `CoursePolicy::view` - All roles (`admin`, `instructor`, `student`) can view.               |
| **PUT**         | `/api/courses/{course}`                   | Update a course (Admin/Instructor only).                                        | `CoursePolicy::update` - Only `admin` or `instructor` can update.                           |
| **DELETE**      | `/api/courses/{course}`                   | Delete a course (Admin only).                                                   | `CoursePolicy::delete` - Only `admin` can delete.                                           |
| **GET**         | `/api/courses/{course}/comments`          | List comments for a course (requires authentication).                           | `CommentPolicy::viewAny` - All roles (`admin`, `instructor`, `student`) can view.           |
| **POST**        | `/api/courses/{course}/comments`          | Add a comment to a course (Students only).                                      | `CommentPolicy::create` - Only `student` can create.                                        |
| **PUT**         | `/api/courses/{course}/comments/{comment}`| Update a comment (requires ownership).                                          | `CommentPolicy::update` - Only `admin` or the comment owner can update.                     |
| **DELETE**      | `/api/courses/{course}/comments/{comment}`| Delete a comment (requires ownership).                                          | `CommentPolicy::delete` - Only `admin` or the comment owner can delete.                     |
| **GET**         | `/api/registrations`                      | List all registrations (Admin/Instructor only).                                 | `RegistrationPolicy::viewAny` - `admin` or `instructor` can view all; `student` can view own.|
| **GET**         | `/api/registrations/{registration}`       | View a specific registration (Admin/Instructor or owner).                       | `RegistrationPolicy::view` - `admin`, `instructor`, or the registration owner can view.     |
| **POST**        | `/api/courses/{course}/registrations`     | Register for a course (Students only).                                          | `RegistrationPolicy::create` - Only `student` can create.                                   |
| **PUT**         | `/api/registrations/{registration}`       | Update a registration (Admin/Instructor or owner).                              | `RegistrationPolicy::update` - Only the registration owner can update.                      |
| **DELETE**      | `/api/registrations/{registration}`       | Cancel a registration (Admin/Instructor or owner).                              | `RegistrationPolicy::delete` - Only the registration owner can delete.                      |
| **POST**        | `/api/students`                           | Create a student account (Admin/Instructor only).                               | `UserPolicy::createStudent` - Only `admin` can create.                                      |
| **GET**         | `/api/students/{user}`                    | View a student’s profile (Admin/Instructor or owner).                           | `UserPolicy::view` - `admin` or the user themselves can view.                               |
| **PUT**         | `/api/students/{user}`                    | Update a student’s profile (Admin/Instructor or owner).                         | `UserPolicy::update` - `admin` or the user themselves can update.                           |
| **DELETE**      | `/api/students/{user}`                    | Delete a student (Admin only).                                                  | `UserPolicy::delete` - Only `admin` can delete.                                             |
| **GET**         | `/api/send_email`                         | Dispatch an email job (requires authentication).                                | None                                                                                        |

---

## Task Scheduling

To fetch books daily:
php artisan app:fetch-books

---

## Queues

Start the queue worker:
php artisan queue:work

---

## Testing

Run all tests:
php artisan test

---
