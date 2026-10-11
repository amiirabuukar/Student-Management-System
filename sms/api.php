<?php
// JSON API for AJAX: generic CRUD (a=list|get|save|delete), attendance (att_*) and grades (g_*).
require __DIR__.'/includes/bootstrap.php'; require __DIR__.'/includes/layout.php';
if (!user()) json_out(['ok'=>false,'error'=>'Please log in again.'],401);
$mods=require __DIR__.'/includes/modules.php'; $a=$_GET['a']??''; $m=$_GET['m']??'';
$isPost=$_SERVER['REQUEST_METHOD']==='POST';
if ($isPost && !csrf_ok()) json_out(['ok'=>false,'error'=>'Invalid security token. Reload the page.'],419);
function need(array $roles){ if(!in_array(role(),$roles,true)) json_out(['ok'=>false,'error'=>'Not authorized.'],403); }

try {
 if (isset($mods[$m])) {
  $M=$mods[$m]; need($M['read']);
  if ($a==='list') {
    $where=[];$p=[];
    if (($q=trim($_GET['q']??''))!=='') { $w=[]; foreach($M['search'] as $c){ $w[]="$c LIKE ?"; $p[]="%$q%"; } $where[]='('.implode(' OR ',$w).')'; }
    foreach($M['filters'] as $k=>$f) if(($_GET[$k]??'')!=='') { $where[]=$f[0].'=?'; $p[]=(int)$_GET[$k]; }
    $W=$where?' WHERE '.implode(' AND ',$where):'';
    $per=$CFG['per_page']; $page=max(1,(int)($_GET['page']??1));
    $c=$pdo->prepare("SELECT COUNT(*) FROM ({$M['select']}$W) x"); $c->execute($p); $total=(int)$c->fetchColumn();
    $s=$pdo->prepare("{$M['select']}$W ORDER BY {$M['order']} LIMIT $per OFFSET ".(($page-1)*$per)); $s->execute($p);
    json_out(['ok'=>true,'rows'=>$s->fetchAll(),'total'=>$total,'pages'=>max(1,(int)ceil($total/$per)),'page'=>$page]);
  }
  if ($a==='get') { $s=$pdo->prepare("SELECT * FROM {$M['table']} WHERE id=?"); $s->execute([(int)($_GET['id']??0)]); $r=$s->fetch();
    $r? json_out(['ok'=>true,'row'=>$r]) : json_out(['ok'=>false,'error'=>'Record not found.'],404); }
  need($M['write']); if(!$isPost) json_out(['ok'=>false,'error'=>'POST required.'],405);
  $id=(int)($_POST['id']??0);
  if ($a==='delete') { $pdo->prepare("DELETE FROM {$M['table']} WHERE id=?")->execute([$id]); json_out(['ok'=>true,'message'=>'Record deleted.']); }
  if ($a==='save') {
    $vals=[]; $errs=[];
    foreach($M['fields'] as $f){ [$n,$l,$t,$req]=$f; if($t==='file') continue; $v=trim($_POST[$n]??'');
      if($v===''){ if($req && !($n==='student_no')) $errs[]="$l is required."; $vals[$n]=null; continue; }
      if($t==='email' && !filter_var($v,FILTER_VALIDATE_EMAIL)) $errs[]="$l is not a valid email.";
      elseif($t==='number' && (!ctype_digit($v) || $v<1 || $v>20)) $errs[]="$l must be a number from 1 to 20.";
      elseif($t==='date' && !(($d=DateTime::createFromFormat('Y-m-d',$v)) && $d->format('Y-m-d')===$v)) $errs[]="$l is not a valid date.";
      elseif($t==='select' && !array_key_exists($v,opts($pdo,$f[4]))) $errs[]="$l has an invalid value.";
      elseif(in_array($t,['text']) && mb_strlen($v)>150) $errs[]="$l is too long.";
      $vals[$n]=$v; }
    if($m==='students' && !empty($_FILES['photo']['name'])) { // validate + store profile picture
      $fl=$_FILES['photo']; $mime=$fl['error']===0?(new finfo(FILEINFO_MIME_TYPE))->file($fl['tmp_name']):'';
      $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;
      if(!$ext) $errs[]='Photo must be a JPG, PNG or WEBP image.'; elseif($fl['size']>2*1024*1024) $errs[]='Photo must be under 2 MB.';
      else { $fn=bin2hex(random_bytes(8)).".$ext"; if(move_uploaded_file($fl['tmp_name'],__DIR__."/uploads/$fn")) $vals['photo']=$fn; else $errs[]='Could not save photo.'; } }
    if($errs) json_out(['ok'=>false,'error'=>implode(' ',$errs)],422);
    $autoNo = $m==='students' && ($vals['student_no']??null)===null;
    if($autoNo) $vals['student_no']='TMP'.bin2hex(random_bytes(6));
    $cols=array_keys($vals);
    if($id){ if($autoNo) unset($vals['student_no']); $cols=array_keys($vals);
      $pdo->prepare("UPDATE {$M['table']} SET ".implode(',',array_map(fn($c)=>"$c=?",$cols))." WHERE id=?")->execute([...array_values($vals),$id]); }
    else { $pdo->prepare("INSERT INTO {$M['table']} (".implode(',',$cols).") VALUES (".implode(',',array_fill(0,count($cols),'?')).")")->execute(array_values($vals));
      $nid=$pdo->lastInsertId(); if($autoNo) $pdo->prepare('UPDATE students SET student_no=? WHERE id=?')->execute(['STU'.date('Y').str_pad($nid,4,'0',STR_PAD_LEFT),$nid]); }
    json_out(['ok'=>true,'message'=>$id?'Record updated.':'Record created.']);
  }
 }
 // ---- Attendance ----
 if ($a==='att_load') { need(['admin','teacher']); $cid=(int)$_GET['class_id']; $d=$_GET['date']??date('Y-m-d');
   $s=$pdo->prepare("SELECT s.id,s.student_no,CONCAT(s.first_name,' ',s.last_name) name,a.status FROM students s
     LEFT JOIN attendance a ON a.student_id=s.id AND a.att_date=? WHERE s.class_id=? AND s.status='Active' ORDER BY s.first_name"); $s->execute([$d,$cid]);
   json_out(['ok'=>true,'rows'=>$s->fetchAll()]); }
 if ($a==='att_save' && $isPost) { need(['admin','teacher']); $cid=(int)$_POST['class_id']; $d=$_POST['date']??'';
   if(!(($dt=DateTime::createFromFormat('Y-m-d',$d)) && $dt->format('Y-m-d')===$d)) json_out(['ok'=>false,'error'=>'Invalid date.'],422);
   $ids=$pdo->prepare('SELECT id FROM students WHERE class_id=?'); $ids->execute([$cid]); $valid=$ids->fetchAll(PDO::FETCH_COLUMN);
   $ins=$pdo->prepare('INSERT INTO attendance(student_id,class_id,att_date,status) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status),class_id=VALUES(class_id)');
   $n=0; foreach(($_POST['status']??[]) as $sid=>$st) if(in_array($sid,$valid) && in_array($st,['Present','Absent','Late','Excused'],true)){ $ins->execute([$sid,$cid,$d,$st]); $n++; }
   json_out(['ok'=>true,'message'=>"Attendance saved for $n students."]); }
 // ---- Grades ----
 if ($a==='g_list') { $w=[];$p=[];
   if(role()==='student'){ $w[]='g.student_id=?'; $p[]=my_student_id($pdo); }
   foreach(['course_id','semester_id','student_id'] as $k) if(role()!=='student' && ($_GET[$k]??'')!==''){ $w[]="g.$k=?"; $p[]=(int)$_GET[$k]; }
   $s=$pdo->prepare("SELECT g.id,s.student_no,CONCAT(s.first_name,' ',s.last_name) student,c.code,c.title,CONCAT(a.name,' ',sm.name) semester,g.exam_type,g.marks,g.max_marks
     FROM grades g JOIN students s ON s.id=g.student_id JOIN courses c ON c.id=g.course_id JOIN semesters sm ON sm.id=g.semester_id JOIN academic_years a ON a.id=sm.academic_year_id".
     ($w?' WHERE '.implode(' AND ',$w):'').' ORDER BY g.id DESC LIMIT 200'); $s->execute($p);
   $rows=array_map(function($r){ $r['pct']=round($r['marks']*100/$r['max_marks'],1); $r['grade']=letter_grade($r['pct']); return $r; },$s->fetchAll());
   json_out(['ok'=>true,'rows'=>$rows,'canWrite'=>role()!=='student']); }
 if ($a==='g_save' && $isPost) { need(['admin','teacher']); $e=[];
   $sid=(int)$_POST['student_id'];$cid=(int)$_POST['course_id'];$sem=(int)$_POST['semester_id'];$type=$_POST['exam_type']??'';
   $marks=$_POST['marks']??''; $max=$_POST['max_marks']??'100';
   if(!is_numeric($marks)||!is_numeric($max)||$max<=0||$marks<0||$marks>$max) json_out(['ok'=>false,'error'=>'Marks must be between 0 and the maximum marks.'],422);
   if(!in_array($type,['Assignment','Quiz','Midterm','Final'],true)) json_out(['ok'=>false,'error'=>'Invalid exam type.'],422);
   $pdo->prepare('INSERT IGNORE INTO enrollments(student_id,course_id,semester_id) VALUES(?,?,?)')->execute([$sid,$cid,$sem]);
   $pdo->prepare('INSERT INTO grades(student_id,course_id,semester_id,exam_type,marks,max_marks) VALUES(?,?,?,?,?,?)')->execute([$sid,$cid,$sem,$type,$marks,$max]);
   json_out(['ok'=>true,'message'=>'Grade recorded.']); }
 if ($a==='g_delete' && $isPost) { need(['admin','teacher']); $pdo->prepare('DELETE FROM grades WHERE id=?')->execute([(int)$_POST['id']]); json_out(['ok'=>true,'message'=>'Grade deleted.']); }
 json_out(['ok'=>false,'error'=>'Unknown action.'],400);
} catch (PDOException $ex) {
  error_log($ex->getMessage());
  json_out(['ok'=>false,'error'=>$ex->getCode()==23000?'Duplicate value or record is in use (e.g. student ID or email already exists).':'A database error occurred.'],409);
} catch (Throwable $ex) { error_log($ex->getMessage()); json_out(['ok'=>false,'error'=>'Unexpected server error.'],500); }
