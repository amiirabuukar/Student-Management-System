<?php
function opts($pdo, $o){ // resolve select options into [value=>label]
  if (is_string($o)) { $r=[]; foreach($pdo->query(substr($o,4))->fetchAll(PDO::FETCH_NUM) as $x) $r[$x[0]]=$x[1]; return $r; }
  return array_combine($o,$o);
}
function page_header($title, $active){
  $nav = [['dashboard','Dashboard','fa-gauge','dashboard.php',['admin','teacher','student']],
   ['students','Students','fa-user-graduate','module.php?m=students',['admin','teacher']],
   ['teachers','Teachers','fa-chalkboard-teacher','module.php?m=teachers',['admin']],
   ['courses','Courses','fa-book-open','module.php?m=courses',['admin','teacher']],
   ['classes','Classes','fa-school','module.php?m=classes',['admin','teacher']],
   ['attendance','Attendance','fa-calendar-check','attendance.php',['admin','teacher']],
   ['grades','Grades','fa-award','grades.php',['admin','teacher','student']],
   ['reports','Reports','fa-file-lines','reports.php',['admin','teacher','student']]];
  ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($title)?> | EduCampus SMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet"><meta name="csrf" content="<?=e(csrf_token())?>"></head><body>
<nav class="topbar d-flex align-items-center px-3">
  <button class="btn btn-link text-white me-2" id="toggleSide"><i class="fa fa-bars"></i></button>
  <span class="brand"><i class="fa fa-graduation-cap"></i> Edu<span>Campus</span></span>
  <div class="ms-auto dropdown d-flex align-items-center gap-3">
    <span class="text-white-50"><i class="fa fa-bell"></i></span>
    <a href="#" class="text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
      <span class="avatar"><?=e(strtoupper(substr(user()['name'],0,1)))?></span> <?=e(user()['name'])?> <small class="text-white-50">(<?=e(role())?>)</small></a>
    <ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="profile.php"><i class="fa fa-user me-2"></i>My profile</a></li>
    <li><a class="dropdown-item" href="logout.php"><i class="fa fa-right-from-bracket me-2"></i>Logout</a></li></ul></div></nav>
<div class="d-flex"><aside class="sidebar" id="sidebar"><?php foreach($nav as $n) if(in_array(role(),$n[4])): ?>
  <a href="<?=$n[3]?>" class="<?=$active===$n[0]?'active':''?>"><i class="fa <?=$n[2]?>"></i><span><?=$n[1]?></span></a><?php endif; ?></aside>
<main class="content flex-grow-1 p-4"><h4 class="mb-3 fw-semibold"><?=e($title)?></h4>
<div class="toast-container position-fixed top-0 end-0 p-3" id="toasts"></div><?php
}
function page_footer($extra=''){ ?></main></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="assets/app.js"></script><?=$extra?></body></html><?php }
