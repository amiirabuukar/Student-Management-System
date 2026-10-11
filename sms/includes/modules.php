<?php
// Config-driven CRUD modules. Field = [name,label,type,required,options]
// type: text|email|date|number|select|file ; options: array or "sql:SELECT id,label FROM ..."
return [
 'students' => ['title'=>'Students','icon'=>'fa-user-graduate','table'=>'students','read'=>['admin','teacher'],'write'=>['admin'],
  'select'=>"SELECT s.*, CONCAT(s.first_name,' ',s.last_name) name, c.name class_name, a.name year_name FROM students s
             LEFT JOIN classes c ON c.id=s.class_id LEFT JOIN academic_years a ON a.id=s.academic_year_id",
  'search'=>['s.student_no','s.first_name','s.last_name','s.email'], 'order'=>'s.id DESC',
  'filters'=>['class_id'=>['s.class_id','All classes','sql:SELECT id,name FROM classes'],'academic_year_id'=>['s.academic_year_id','All years','sql:SELECT id,name FROM academic_years']],
  'columns'=>['student_no'=>'Student ID','name'=>'Name','class_name'=>'Class','year_name'=>'Year','status'=>'Status'],
  'fields'=>[['student_no','Student ID (auto if blank)','text',false],['first_name','First name','text',true],['last_name','Last name','text',true],
   ['email','Email','email',true],['phone','Phone','text',false],['gender','Gender','select',true,['Male','Female']],['dob','Date of birth','date',false],
   ['class_id','Class','select',false,'sql:SELECT id,name FROM classes'],['academic_year_id','Academic year','select',false,'sql:SELECT id,name FROM academic_years'],
   ['status','Status','select',true,['Active','Inactive','Graduated']],['photo','Profile picture','file',false]]],
 'teachers' => ['title'=>'Teachers','icon'=>'fa-chalkboard-teacher','table'=>'teachers','read'=>['admin'],'write'=>['admin'],
  'select'=>"SELECT t.*, CONCAT(t.first_name,' ',t.last_name) name,
             (SELECT GROUP_CONCAT(code SEPARATOR ', ') FROM courses WHERE teacher_id=t.id) course_list,
             (SELECT GROUP_CONCAT(name SEPARATOR ', ') FROM classes WHERE teacher_id=t.id) class_list FROM teachers t",
  'search'=>['t.first_name','t.last_name','t.email'], 'order'=>'t.id DESC', 'filters'=>[],
  'columns'=>['name'=>'Name','email'=>'Email','phone'=>'Phone','qualification'=>'Qualification','course_list'=>'Courses','class_list'=>'Classes'],
  'fields'=>[['first_name','First name','text',true],['last_name','Last name','text',true],['email','Email','email',true],['phone','Phone','text',false],['qualification','Qualification','text',false]]],
 'courses' => ['title'=>'Courses','icon'=>'fa-book-open','table'=>'courses','read'=>['admin','teacher'],'write'=>['admin'],
  'select'=>"SELECT c.*, CONCAT(t.first_name,' ',t.last_name) teacher_name,
             (SELECT COUNT(*) FROM enrollments e WHERE e.course_id=c.id) enrolled FROM courses c LEFT JOIN teachers t ON t.id=c.teacher_id",
  'search'=>['c.code','c.title'], 'order'=>'c.id DESC', 'filters'=>[],
  'columns'=>['code'=>'Code','title'=>'Title','credits'=>'Credits','teacher_name'=>'Teacher','enrolled'=>'Enrolled'],
  'fields'=>[['code','Course code','text',true],['title','Title','text',true],['credits','Credits','number',true],['teacher_id','Teacher','select',false,"sql:SELECT id,CONCAT(first_name,' ',last_name) FROM teachers"]]],
 'classes' => ['title'=>'Classes','icon'=>'fa-school','table'=>'classes','read'=>['admin','teacher'],'write'=>['admin'],
  'select'=>"SELECT c.*, CONCAT(t.first_name,' ',t.last_name) teacher_name, a.name year_name,
             (SELECT COUNT(*) FROM students s WHERE s.class_id=c.id) students_count FROM classes c
             LEFT JOIN teachers t ON t.id=c.teacher_id LEFT JOIN academic_years a ON a.id=c.academic_year_id",
  'search'=>['c.name','c.department'], 'order'=>'c.id DESC', 'filters'=>[],
  'columns'=>['name'=>'Class','department'=>'Department','year_name'=>'Year','teacher_name'=>'Class teacher','students_count'=>'Students'],
  'fields'=>[['name','Class name','text',true],['department','Department','text',false],
   ['academic_year_id','Academic year','select',false,'sql:SELECT id,name FROM academic_years'],['teacher_id','Class teacher','select',false,"sql:SELECT id,CONCAT(first_name,' ',last_name) FROM teachers"]]],
];
