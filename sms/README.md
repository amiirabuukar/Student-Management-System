# EduCampus – Student Management System (PHP 8 + MySQL)

Role-based school/university admin app: students, teachers, courses, classes, attendance, grades, reports, dashboard (Chart.js).
Stack: PHP (plain, PDO), MySQL, Bootstrap 5, Font Awesome, jQuery + AJAX/JSON. Bootstrap/jQuery/Chart.js load from CDNs, so internet access is needed in the browser.

## Run locally with XAMPP
1. Install XAMPP (PHP 8.0+). In the XAMPP Control Panel, start **Apache** and **MySQL**.
2. Copy the `sms` folder to `C:\xampp\htdocs\sms` (macOS: `/Applications/XAMPP/htdocs/sms`).
3. Open http://localhost/phpmyadmin → **Import** → choose `database/student_management_system.sql` → **Go**
   (this creates the `student_management_system` database, tables, and sample data).
4. If your MySQL root user has a password, edit `config/config.php` (or set env vars `SMS_DB_USER`, `SMS_DB_PASS`, `SMS_DB_NAME`, `SMS_DB_HOST`).
5. Open http://localhost/sms/ and sign in.

## Sample logins (development only – change them!)
| Role | Email | Password |
|---|---|---|
| Admin | admin@school.test | Admin@123 |
| Teacher | teacher@school.test | Teacher@123 |
| Student | student@school.test | Student@123 |

## Modules
- `login.php / logout.php / profile.php` – authentication, session hardening, password hashing (bcrypt), profile + password change.
- `dashboard.php` – stats, attendance trend, performance chart, recent registrations, quick actions.
- `module.php` + `includes/modules.php` + `assets/crud.js` – config-driven AJAX CRUD with search, filters, pagination for Students, Teachers, Courses, Classes (add a module by adding one config entry).
- `attendance.php` – daily attendance per class (Present/Absent/Late/Excused).
- `grades.php` – record marks; percentage and letter grade (A ≥90, B ≥80, C ≥70, D ≥60, else F) computed automatically.
- `reports.php` – attendance report, printable report card (use Print → Save as PDF), CSV export of students/attendance/grades.
- `api.php` – all JSON endpoints, with CSRF + role checks and prepared statements.
- `includes/` – bootstrap (DB, helpers), layout (sidebar/topbar), module definitions. `database/` – schema + seed. `uploads/` – profile pictures (script execution disabled).

## Design decisions (defaults chosen where the brief was open)
- Plain PHP/PDO instead of Laravel so it runs on stock XAMPP with no Composer step.
- Attendance % = (Present + Late) / all records. Teachers can view all students/classes and record attendance and grades; only admins manage records. Students see only their own results.
- Security: prepared statements everywhere, CSRF token on every POST, output escaped, uploads checked by real MIME type and 2 MB limit, login lockout after 5 failures, authorization on every page and API action.
- Not included: assigning students/teachers to classes beyond the class and class-teacher fields, a separate enrollment screen (enrollments are created when a grade is recorded), and a teacher timetable.
