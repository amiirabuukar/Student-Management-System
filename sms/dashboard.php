<?php
require __DIR__.'/includes/bootstrap.php'; require __DIR__.'/includes/layout.php'; require_login();
$one=fn($sql,$p=[])=>(function() use($pdo,$sql,$p){ $s=$pdo->prepare($sql); $s->execute($p); return $s->fetchColumn(); })();
page_header('Overview','dashboard');
if (role()==='student') {
  $sid=my_student_id($pdo);
  $att=$one("SELECT ROUND(SUM(status IN('Present','Late'))*100/COUNT(*),1) FROM attendance WHERE student_id=?",[$sid]);
  $avg=$one("SELECT ROUND(SUM(marks)*100/SUM(max_marks),1) FROM grades WHERE student_id=?",[$sid]);
  ?><div class="row g-3"><div class="col-md-4"><div class="card stat p-3"><small>My attendance</small><h2><?=$att!==null&&$att!==false?e($att).'%':'—'?></h2></div></div>
  <div class="col-md-4"><div class="card stat p-3"><small>Average score</small><h2><?=$avg?e($avg).'% ('.letter_grade($avg).')':'—'?></h2></div></div>
  <div class="col-md-4"><a class="btn btn-primary" href="grades.php">View my results</a> <a class="btn btn-outline-primary" href="reports.php">My report card</a></div></div><?php
  page_footer(); exit;
}
$cnt=$pdo->query("SELECT (SELECT COUNT(*) FROM students) s,(SELECT COUNT(*) FROM teachers) t,(SELECT COUNT(*) FROM courses) c,(SELECT COUNT(*) FROM classes) k,
 (SELECT COUNT(*) FROM students WHERE status='Active') active,(SELECT COUNT(*) FROM students WHERE created_at>=DATE_FORMAT(CURDATE(),'%Y-%m-01')) new_m")->fetch();
$att=$pdo->query("SELECT status,COUNT(*) n FROM attendance WHERE att_date>=CURDATE()-INTERVAL 30 DAY GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$tot=array_sum($att); $pres=($att['Present']??0)+($att['Late']??0); $pct=$tot?round($pres*100/$tot,1):0;
$trend=$pdo->query("SELECT att_date d,ROUND(SUM(status IN('Present','Late'))*100/COUNT(*),1) p FROM attendance WHERE att_date>=CURDATE()-INTERVAL 6 DAY GROUP BY att_date ORDER BY att_date")->fetchAll();
$perf=$pdo->query("SELECT c.code,ROUND(SUM(g.marks)*100/SUM(g.max_marks),1) p FROM grades g JOIN courses c ON c.id=g.course_id GROUP BY c.id ORDER BY c.code")->fetchAll();
$recent=$pdo->query("SELECT student_no,CONCAT(first_name,' ',last_name) n,created_at FROM students ORDER BY id DESC LIMIT 5")->fetchAll();
$cards=[['Total Students',$cnt['s'],'fa-user-graduate','blue'],['Total Teachers',$cnt['t'],'fa-chalkboard-teacher','green'],['Total Courses',$cnt['c'],'fa-book-open','orange'],['Total Classes',$cnt['k'],'fa-school','purple']];
?><div class="row g-3 mb-3">
 <div class="col-lg-3"><div class="card welcome p-3 h-100"><small>Welcome</small><h5>Good day, <?=e(explode(' ',user()['name'])[0])?>!</h5><small class="mb-2">Quick Access</small>
  <?php if(role()==='admin'): ?><a href="module.php?m=students" class="btn btn-navy btn-sm mb-2"><i class="fa fa-user-plus"></i> Add Student</a><?php endif; ?>
  <a href="grades.php" class="btn btn-navy btn-sm mb-2"><i class="fa fa-award"></i> Process Grades</a>
  <a href="attendance.php" class="btn btn-navy btn-sm mb-2"><i class="fa fa-calendar-check"></i> Take Attendance</a>
  <a href="reports.php" class="btn btn-navy btn-sm"><i class="fa fa-file-lines"></i> Create Report</a></div></div>
 <div class="col-lg-9"><div class="row g-3"><?php foreach($cards as $c): ?><div class="col-6 col-md-3"><div class="card stat p-3 h-100"><span class="ico <?=$c[3]?>"><i class="fa <?=$c[2]?>"></i></span>
  <small><?=$c[0]?></small><h2><?=e($c[1])?></h2></div></div><?php endforeach; ?>
  <div class="col-12"><div class="card p-3"><div class="d-flex justify-content-between"><strong>Attendance (last 30 days)</strong><span class="badge bg-primary"><?=e($pct)?>% attended</span></div>
   <div class="d-flex gap-3 mt-2 small flex-wrap"><?php foreach(['Present','Absent','Late','Excused'] as $s): ?><span><?=$s?>: <b><?=e($att[$s]??0)?></b></span><?php endforeach; ?>
   <span class="ms-auto text-muted">Active students: <?=e($cnt['active'])?> · New this month: <?=e($cnt['new_m'])?></span></div></div></div></div></div></div>
<div class="row g-3"><div class="col-lg-4"><div class="card p-3"><strong>Attendance trend (7 days, %)</strong><canvas id="c1" height="200"></canvas></div></div>
 <div class="col-lg-4"><div class="card p-3"><strong>Academic performance (avg % by course)</strong><canvas id="c2" height="200"></canvas></div></div>
 <div class="col-lg-4"><div class="card p-3"><strong>Recent registrations</strong><ul class="list-group list-group-flush mt-2">
  <?php foreach($recent as $r): ?><li class="list-group-item px-0 d-flex justify-content-between"><span><?=e($r['n'])?> <small class="text-muted"><?=e($r['student_no'])?></small></span><small class="text-muted"><?=e(substr($r['created_at'],0,10))?></small></li><?php endforeach; ?>
  <?php if(!$recent): ?><li class="list-group-item text-muted">No students yet.</li><?php endif; ?></ul></div></div></div>
<?php page_footer('<script>const T='.json_encode($trend).',P='.json_encode($perf).';
new Chart(c1,{type:"line",data:{labels:T.map(x=>x.d),datasets:[{data:T.map(x=>+x.p),borderColor:"#2563eb",backgroundColor:"rgba(37,99,235,.15)",fill:true,tension:.35}]},options:{plugins:{legend:{display:false}},scales:{y:{min:0,max:100}}}});
new Chart(c2,{type:"bar",data:{labels:P.map(x=>x.code),datasets:[{data:P.map(x=>+x.p),backgroundColor:"#f59e0b"}]},options:{plugins:{legend:{display:false}},scales:{y:{min:0,max:100}}}});</script>');
