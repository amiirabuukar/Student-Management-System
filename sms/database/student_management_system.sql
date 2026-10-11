-- Student Management System schema + sample data (import via phpMyAdmin > Import)
CREATE DATABASE IF NOT EXISTS student_management_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_management_system;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS grades, attendance, enrollments, students, classes, courses, teachers, semesters, academic_years, users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users(
 id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(120) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL, role ENUM('admin','teacher','student') NOT NULL DEFAULT 'student',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE academic_years(
 id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(20) NOT NULL UNIQUE, is_current TINYINT(1) DEFAULT 0) ENGINE=InnoDB;
CREATE TABLE semesters(
 id INT AUTO_INCREMENT PRIMARY KEY, academic_year_id INT NOT NULL, name VARCHAR(50) NOT NULL,
 UNIQUE(academic_year_id,name), FOREIGN KEY(academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE) ENGINE=InnoDB;
CREATE TABLE teachers(
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, first_name VARCHAR(60) NOT NULL, last_name VARCHAR(60) NOT NULL,
 email VARCHAR(120) NOT NULL UNIQUE, phone VARCHAR(30), qualification VARCHAR(150), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE courses(
 id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(20) NOT NULL UNIQUE, title VARCHAR(150) NOT NULL,
 credits TINYINT NOT NULL DEFAULT 3, teacher_id INT NULL,
 FOREIGN KEY(teacher_id) REFERENCES teachers(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE classes(
 id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(80) NOT NULL, department VARCHAR(100), academic_year_id INT NULL, teacher_id INT NULL,
 FOREIGN KEY(academic_year_id) REFERENCES academic_years(id) ON DELETE SET NULL,
 FOREIGN KEY(teacher_id) REFERENCES teachers(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE students(
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, student_no VARCHAR(20) NOT NULL UNIQUE,
 first_name VARCHAR(60) NOT NULL, last_name VARCHAR(60) NOT NULL, email VARCHAR(120) NOT NULL UNIQUE, phone VARCHAR(30),
 gender ENUM('Male','Female') NOT NULL, dob DATE NULL, class_id INT NULL, academic_year_id INT NULL, photo VARCHAR(255) NULL,
 status ENUM('Active','Inactive','Graduated') NOT NULL DEFAULT 'Active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX(class_id), INDEX(academic_year_id),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(class_id) REFERENCES classes(id) ON DELETE SET NULL,
 FOREIGN KEY(academic_year_id) REFERENCES academic_years(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE enrollments(
 id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, course_id INT NOT NULL, semester_id INT NOT NULL,
 UNIQUE(student_id,course_id,semester_id),
 FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE CASCADE,
 FOREIGN KEY(course_id) REFERENCES courses(id) ON DELETE CASCADE,
 FOREIGN KEY(semester_id) REFERENCES semesters(id) ON DELETE CASCADE) ENGINE=InnoDB;
CREATE TABLE attendance(
 id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, class_id INT NOT NULL, att_date DATE NOT NULL,
 status ENUM('Present','Absent','Late','Excused') NOT NULL, UNIQUE(student_id,att_date), INDEX(class_id,att_date),
 FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE CASCADE,
 FOREIGN KEY(class_id) REFERENCES classes(id) ON DELETE CASCADE) ENGINE=InnoDB;
CREATE TABLE grades(
 id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, course_id INT NOT NULL, semester_id INT NOT NULL,
 exam_type ENUM('Assignment','Quiz','Midterm','Final') NOT NULL, marks DECIMAL(5,2) NOT NULL, max_marks DECIMAL(5,2) NOT NULL DEFAULT 100,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, CHECK (marks>=0 AND marks<=max_marks),
 FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE CASCADE,
 FOREIGN KEY(course_id) REFERENCES courses(id) ON DELETE CASCADE,
 FOREIGN KEY(semester_id) REFERENCES semesters(id) ON DELETE CASCADE) ENGINE=InnoDB;

-- Sample data. Logins: admin@school.test / Admin@123, teacher@school.test / Teacher@123, student@school.test / Student@123
INSERT INTO users(name,email,password,role) VALUES
('Sarah Jenkins','admin@school.test','$2y$10$5FLIlKAQPcjHHW9hJCjdne6WAZdTRhX5YTObeSahJ8MBFmeY06Fr.','admin'),
('Michael Chen','teacher@school.test','$2y$10$KVegC4pVRw7kMYEtkGVpA.CfUlioFTIEeS.hLWRZ3ZFsZ/H1RMzA2','teacher'),
('Aisha Patel','student@school.test','$2y$10$72NtMTel2KnHpKvagaIEh.XQUhSJRS.CcNvZY6PU6RWsUBkl6lV3e','student');
INSERT INTO academic_years(name,is_current) VALUES('2025/2026',0),('2026/2027',1);
INSERT INTO semesters(academic_year_id,name) VALUES(2,'Semester 1'),(2,'Semester 2');
INSERT INTO teachers(user_id,first_name,last_name,email,phone,qualification) VALUES
(2,'Michael','Chen','teacher@school.test','+1 555 0101','MSc Computer Science'),
(NULL,'Linda','Okafor','linda.okafor@school.test','+1 555 0102','PhD Mathematics');
INSERT INTO courses(code,title,credits,teacher_id) VALUES('CS101','Intro to Programming',3,1),('MA101','Calculus I',4,2),('SC101','General Science',3,2);
INSERT INTO classes(name,department,academic_year_id,teacher_id) VALUES('Computer Science Y1','Computing',2,1),('Applied Science Y1','Science',2,2);
INSERT INTO students(user_id,student_no,first_name,last_name,email,phone,gender,dob,class_id,academic_year_id,status) VALUES
(3,'STU20260001','Aisha','Patel','student@school.test','+1 555 0201','Female','2007-03-14',1,2,'Active'),
(NULL,'STU20260002','Daniel','Moore','daniel.moore@school.test','+1 555 0202','Male','2006-11-02',1,2,'Active'),
(NULL,'STU20260003','Sofia','Garcia','sofia.garcia@school.test','+1 555 0203','Female','2007-07-21',2,2,'Active'),
(NULL,'STU20260004','Omar','Hassan','omar.hassan@school.test','+1 555 0204','Male','2006-05-30',2,2,'Active');
INSERT INTO enrollments(student_id,course_id,semester_id) VALUES(1,1,1),(1,2,1),(2,1,1),(3,3,1),(4,3,1),(4,2,1);
INSERT INTO attendance(student_id,class_id,att_date,status) VALUES
(1,1,CURDATE(),'Present'),(2,1,CURDATE(),'Late'),(3,2,CURDATE(),'Present'),(4,2,CURDATE(),'Absent'),
(1,1,CURDATE()-INTERVAL 1 DAY,'Present'),(2,1,CURDATE()-INTERVAL 1 DAY,'Present'),(3,2,CURDATE()-INTERVAL 1 DAY,'Excused'),(4,2,CURDATE()-INTERVAL 1 DAY,'Present');
INSERT INTO grades(student_id,course_id,semester_id,exam_type,marks,max_marks) VALUES
(1,1,1,'Midterm',88,100),(1,1,1,'Assignment',92,100),(1,2,1,'Midterm',76,100),(2,1,1,'Midterm',64,100),
(3,3,1,'Quiz',45,50),(4,3,1,'Midterm',58,100),(4,2,1,'Midterm',81,100);
