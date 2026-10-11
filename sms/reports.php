<?php
require __DIR__.'/includes/bootstrap.php'; require __DIR__.'/includes/layout.php'; require_login();
$staff=role()!=='student';
function csv_cell($v){ $v=(string)$v; return $v!==''&&strpos("=+-@",$v[0])!==false?"'".$v:$v; } // block CSV formula injection
if (isset($_GET['export'])) { require_login(['admin','teacher']); $t=$_GET['export'];
  $q=['students'=>"SELECT s.student_no,s.first_name,s.last_name,s.email,s.phone,s.gender,s.dob,c.name class,a.name academic_year,s.status FROM students s LEFT JOIN classes c ON c.id=s.class_id LEFT JOIN academic_years a ON a.id=s.academic_year_id",
      'attendance'=>"SELECT s.student_no,CONCAT(s.first_name,' ',s.last_name) student,at.att_date,at.status FROM attendance at JOIN students s ON s.id=at.student_id ORDER BY at.att_date DESC",
      'grades'=>"SELECT s.student_no,CONCAT(s.first_name,' ',s.last_name) student,c.code,g.exam_type,g.marks,g.max_marks FROM grades g JOIN students s ON s.id=g.student_id JOIN courses c ON c.id=g.course_id"][$t]??null;
  if(!$q){http_response_code(404);exit;}
  header('Content-Type: text/csv'); header("Content-Disposition: attachment; filename=$t-".date('Ymd').'.csv'); $o=fopen('php://output','w'); $first=true;
  foreach($pdo->query($q) as $r){ if($first){fputcsv($o,array_keys($r));$first=false;} fputcsv($o,array_map('csv_cell',$r)); } exit; }
page_header('Reports','reports');
$sid=$staff?(int)($_GET['student_id']??0):my_student_id($pdo);
?><div class="row g-3 no-print"><?php if($staff): ?><div class="col-12"><div class="card p-3"><strong>Export data</strong><div class="mt-2 d-flex gap-2 flex-wrap">
 <a class="btn btn-outline-primary btn-sm" href="?export=students"><i class="fa fa-file-csv"></i> Students CSV</a>
 <a class="btn btn-outline-primary btn-sm" href="?export=attendance"><i class="fa fa-file-csv"></i> Attendance CSV</a>
 <a class="btn btn-outline-primary btn-sm" href="?export=grades"><i class="fa fa-file-csv"></i> Grades CSV</a></div></div></div>
<div class="col-12"><div class="card p-3"><strong>Attendance report</strong><form class="row g-2 mt-1" method="get">
 <div class="col-md-3"><select name="class_id" class="form-select"><option value="">All classes</option><?php foreach(opts($pdo,'sql:SELECT id,name FROM classes') as $v=>$l): ?><option value="<?=e($v)?>" <?=($_GET['class_id']??'')==$v?'selected':''?>><?=e($l)?></option><?php endforeach; ?></select></div>
 <div class="col-md-3"><input type="date" name="from" class="form-control" value="<?=e($_GET['from']??date('Y-m-01'))?>"></div>
 <div class="col-md-3"><input type="date" name="to" class="form-control" value="<?=e($_GET['to']??date('Y-m-d'))?>"></div>
 <div class="col-md-3"><button class="btn btn-primary w-100">Generate</button></div></form></div></div><?php endif; ?></div>
<?php
if ($staff && isset($_GET['from'])) {
  $p=[$_GET['from'],$_GET['to']]; $w=''; if(($_GET['class_id']??'')!==''){ $w=' AND s.class_id=?'; $p[]=(int)$_GET['class_id']; }
  $s=$pdo->prepare("SELECT s.student_no,CONCAT(s.first_name,' ',s.last_name) n,SUM(a.status='Present') p,SUM(a.status='Absent') ab,SUM(a.status='Late') l,SUM(a.status='Excused') ex,COUNT(a.id) t
    FROM students s JOIN attendance a ON a.student_id=s.id AND a.att_date BETWEEN ? AND ? WHERE 1=1$w GROUP BY s.id ORDER BY s.first_name"); $s->execute($p); ?>
<div class="card p-3 mt-3"><h6>Attendance <?=e($_GET['from'])?> → <?=e($_GET['to'])?></h6><div class="table-responsive"><table class="table table-sm"><thead><tr><th>ID</th><th>Student</th><th>Present</th><th>Absent</th><th>Late</th><th>Excused</th><th>Attendance %</th></tr></thead><tbody>
<?php foreach($s as $r): $pc=$r['t']?round(($r['p']+$r['l'])*100/$r['t'],1):0; ?><tr><td><?=e($r['student_no'])?></td><td><?=e($r['n'])?></td><td><?=e($r['p'])?></td><td><?=e($r['ab'])?></td><td><?=e($r['l'])?></td><td><?=e($r['ex'])?></td><td><b><?=e($pc)?>%</b></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php } ?>
<div class="card p-3 mt-3"><div class="no-print d-flex gap-2 align-items-center mb-2"><strong>Student report card</strong>
<?php if($staff): ?><form method="get" class="d-flex gap-2"><select name="student_id" class="form-select form-select-sm"><option value="">Select student…</option>
<?php foreach(opts($pdo,"sql:SELECT id,CONCAT(student_no,' - ',first_name,' ',last_name) FROM students ORDER BY first_name") as $v=>$l): ?><option value="<?=e($v)?>" <?=$sid==$v?'selected':''?>><?=e($l)?></option><?php endforeach; ?></select><button class="btn btn-sm btn-primary">View</button></form><?php endif; ?>
<?php if($sid): ?><button class="btn btn-sm btn-outline-secondary ms-auto" onclick="window.print()"><i class="fa fa-print"></i> Print / Save as PDF</button><?php endif; ?></div>
<?php if($sid): $st=$pdo->prepare("SELECT s.*,c.name cn FROM students s LEFT JOIN classes c ON c.id=s.class_id WHERE s.id=?"); $st->execute([$sid]); $stu=$st->fetch();
 if($stu): $g=$pdo->prepare("SELECT c.code,c.title,c.credits,SUM(g.marks) m,SUM(g.max_marks) mx FROM grades g JOIN courses c ON c.id=g.course_id WHERE g.student_id=? GROUP BY c.id"); $g->execute([$sid]); $rows=$g->fetchAll();
 $am=$pdo->prepare("SELECT ROUND(SUM(status IN('Present','Late'))*100/COUNT(*),1) FROM attendance WHERE student_id=?"); $am->execute([$sid]); $ap=$am->fetchColumn(); ?>
 <h5>Report Card — <?=e($stu['first_name'].' '.$stu['last_name'])?></h5><p class="text-muted mb-2"><?=e($stu['student_no'])?> · <?=e($stu['cn']??'No class')?> · Attendance: <?=$ap!==null&&$ap!==false?e($ap).'%':'n/a'?></p>
 <table class="table table-sm"><thead><tr><th>Code</th><th>Course</th><th>Credits</th><th>Total marks</th><th>%</th><th>Grade</th></tr></thead><tbody>
 <?php $sm=0;$sx=0; foreach($rows as $r): $pc=round($r['m']*100/$r['mx'],1); $sm+=$r['m']; $sx+=$r['mx']; ?><tr><td><?=e($r['code'])?></td><td><?=e($r['title'])?></td><td><?=e($r['credits'])?></td><td><?=e($r['m'])?>/<?=e($r['mx'])?></td><td><?=e($pc)?>%</td><td><b><?=letter_grade($pc)?></b></td></tr><?php endforeach; ?>
 <?php if(!$rows): ?><tr><td colspan="6" class="text-muted">No grades recorded.</td></tr><?php else: $o=round($sm*100/$sx,1); ?><tr class="table-light"><td colspan="4"><b>Overall</b></td><td><b><?=e($o)?>%</b></td><td><b><?=letter_grade($o)?></b></td></tr><?php endif; ?></tbody></table>
 <?php endif; else: ?><p class="text-muted mb-0">Select a student to view a report card.</p><?php endif; ?></div>
<?php page_footer();
