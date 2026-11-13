# Task Tracker Web Application

## 1. Project Overview

Task Tracker is a web-based application developed using PHP and MySQL. It allows users to create, view, edit, delete and manage their daily tasks.

The application includes user authentication, task management, search, status filtering, priority levels, due dates and Role-Based Access Control (RBAC).

## 2. Technologies Used

- Frontend: HTML, CSS
- Backend: PHP
- Database: MySQL
- Database management: phpMyAdmin
- Local server: XAMPP
- Code editor: Visual Studio Code

## 3. Features Implemented

### Level 1 – Basic Task Management
- Create new tasks.
- View task lists.
- Edit existing tasks.
- Delete tasks.
- Display success and error messages.

### Level 2 – Task Management Improvements
- Task statuses: Pending, In Progress and Completed.
- Search tasks by title.
- Filter tasks by status.
- Task priority: Low, Medium and High.
- Task due dates.
- Frontend and backend input validation.

### Level 3 – Authentication and Security
- User registration and login.
- Secure password hashing using `password_hash()`.
- Password verification using `password_verify()`.
- Session-based authentication.
- Prepared SQL statements using PDO.
- CSRF protection.
- Output escaping to help prevent XSS.
- User-specific task ownership authorization.

### Additional Feature – Role-Based Access Control (RBAC)

Two roles are implemented:

**User**
- Register and log in.
- Create tasks.
- View only their own tasks.
- Edit and delete only their own tasks.
- Update the status of their own tasks.

**Admin**
- Log in as an administrator.
- View tasks belonging to all users.
- Create tasks.
- Edit and delete tasks belonging to any user.
- Update the status of any user's task.

All permissions are checked on the server side. Users cannot gain access to another user's tasks by changing task IDs in URLs or request data.

## 4. Software Requirements

- Windows operating system (or another compatible PHP environment).
- XAMPP with Apache and MySQL.
- PHP 8.x recommended.
- MySQL.
- Visual Studio Code or another code editor.
- A modern web browser.

## 5. Installation and Setup Instructions

### Step 1: Install XAMPP

1. Install XAMPP.
2. Open the XAMPP Control Panel.
3. Start Apache.
4. Start MySQL.

### Step 2: Copy the project

1. Created and Implemented the Task Tracker project folder.
2. Copy the project into the XAMPP `htdocs` directory.
3. Example location:

   `C:\xampp\htdocs\Task_Tracker`

### Step 3: Create the database

1. Open a browser.
2. Visit `http://localhost/phpmyadmin`.
3. Open the Import tab.
4. Select the supplied `database.sql` file.
5. Click Import.
6. Confirm that the `task_tracker` database is created with the `users` and `tasks` tables.

### Step 4: Configure the database connection

Open `config/database.php`.

Check the following default local database settings:

- Host: `127.0.0.1`
- Database name: `task_tracker`
- Username: `root`
- Password: Empty (default XAMPP configuration)

If your MySQL installation uses a different password or configuration, update the connection settings accordingly.

### Step 5: Run the application

Open:

`http://localhost/Task_Tracker/register.php`

Register a new account, log in and start creating tasks.

## 6. Admin Account Setup

New registrations receive the `user` role by default.

To promote an existing account to Admin:

1. Open phpMyAdmin.
2. Select the `task_tracker` database.
3. Open the SQL tab.
4. Run the following query, replacing the email with the account's actual registered email:

```sql
UPDATE users
SET role = 'admin'
WHERE email = 'admin@example.com';
```

5. Log out and log in again so the updated role is loaded into the session.

The application does not provide public Admin self-registration.

## 7. Test Login Details

For evaluation, create separate demonstration accounts before submitting.

| Account | Role | Login details |
|---|---|---|
| Account A | Admin | Add your prepared demo email |
| Account B | User | Add your prepared demo email |
| Account C | User | Add your prepared demo email |

Provide demonstration passwords separately and only if required by the evaluator. Do not include your personal account password in the project files.

## 8. Manual Testing and Results

**Testing method:** Manual testing using a web browser and phpMyAdmin.

**Postman/PHPUnit:** Not used.

The following test categories were performed:

| Test | Result |
|---|---|
| User registration and login | PASS |
| Task creation, editing and deletion | PASS |
| User can access only their own tasks | PASS |
| Unauthorized task access through URL manipulation | PASS |
| Unauthorized task deletion | PASS |
| Admin can view all users' tasks | PASS |
| Admin can edit and delete other users' tasks | PASS |
| Search and status filtering | PASS |
| Required-field validation | PASS |
| Access protection after logout | PASS |

**Final testing result:** All performed manual test categories passed successfully.

## 9. Bugs Found and Fixed

- Corrected role-based task visibility in the Admin and User dashboards.
- Corrected SQL conditions for search and status filtering.
- Added Admin permissions for editing and deleting other users' tasks.
- Corrected the PHP `strict_types` declaration issue.

## 10. Known Limitations

- No email verification during registration.
- No forgot-password or password-reset email workflow.
- No audit log for tracking administrator actions.
- No dedicated interface for administrators to manage user accounts and assign roles.
- The application is designed as a local learning/project application and requires additional production deployment security hardening before public use.

## 11. Database Backup and Restore

### Backup using phpMyAdmin

1. Open `http://localhost/phpmyadmin`.
2. Select the `task_tracker` database.
3. Click Export.
4. Choose SQL format.
5. Click Export.
6. Save the downloaded SQL file in a safe location.

### Restore using phpMyAdmin

1. Open phpMyAdmin.
2. Create or select the target database.
3. Open the Import tab.
4. Select the previously exported SQL backup.
5. Click Import.
6. Verify that the tables and records have been restored.



## 12. AI Coding Assistant Disclosure

ChatGPT was used as an AI coding assistant during development to help understand PHP, MySQL, authentication, validation and authorization concepts.

The generated code was reviewed, tested and modified during implementation.

**Example of a change made after reviewing AI-generated code:**

The task queries were modified to enforce role-based permissions using the logged-in user's ID. Normal users can access only their own tasks, while administrators can manage tasks belonging to all users. Search and status-filter SQL conditions were also adjusted to work correctly for both roles.

All major application features were manually tested before submission.

## 13. Conclusion

The Task Tracker application successfully implements task management, authentication, input validation and Role-Based Access Control.

The application was manually tested, and all performed test categories passed successfully.