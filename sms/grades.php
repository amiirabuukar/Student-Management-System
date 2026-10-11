<?php
require __DIR__.'/includes/bootstrap.php'; require __DIR__.'/includes/layout.php'; require_login();
$staff=role()!=='student'; page_header($staff?'Grades & Results':'My Results','grades');
$sel=fn($n,$sql,$ph)=>'<select name="'.$n.'" class="form-select" '.($ph?'':'required').'><option value="">'.($ph?:'— select —').'</option>'.implode('',array_map(fn($v,$l)=>'<option value="'.e($v).'">'.e($l).'</option>',array_keys($o=opts($pdo,$sql)),$o)).'</select>';
?><?php if($staff): ?><div class="card p-3 mb-3"><form id="gForm" class="row g-2">
 <div class="col-md-3"><?=$sel('student_id',"sql:SELECT id,CONCAT(student_no,' - ',first_name,' ',last_name) FROM students ORDER BY first_name",'')?></div>
 <div class="col-md-2"><?=$sel('course_id','sql:SELECT id,code FROM courses','')?></div>
 <div class="col-md-2"><?=$sel('semester_id',"sql:SELECT s.id,CONCAT(a.name,' ',s.name) FROM semesters s JOIN academic_years a ON a.id=s.academic_year_id",'')?></div>
 <div class="col-md-1"><select name="exam_type" class="form-select"><option>Assignment</option><option>Quiz</option><option selected>Midterm</option><option>Final</option></select></div>
 <div class="col-md-1"><input name="marks" type="number" step="0.01" min="0" class="form-control" placeholder="Marks" required></div>
 <div class="col-md-1"><input name="max_marks" type="number" step="0.01" min="1" class="form-control" value="100" required></div>
 <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fa fa-plus"></i> Add grade</button></div></form></div><?php endif; ?>
<div class="card p-3"><?php if($staff): ?><div class="d-flex gap-2 mb-3"><div style="max-width:220px"><?=$sel('f_course',"sql:SELECT id,code FROM courses",'All courses')?></div></div><?php endif; ?>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Student</th><th>Course</th><th>Semester</th><th>Type</th><th>Marks</th><th>%</th><th>Grade</th><th></th></tr></thead><tbody id="gBody"></tbody></table></div></div>
<?php page_footer('<script>
function load(){ $("#gBody").html("<tr><td colspan=8 class=\"text-center py-4\"><div class=\"spinner-border spinner-border-sm\"></div></td></tr>");
 $.getJSON("api.php",{a:"g_list",course_id:$("[name=f_course]").val()||""},d=>{ const b=$("#gBody").empty();
  if(!d.rows.length) return b.html("<tr><td colspan=8 class=\"text-center text-muted py-4\">No grades recorded yet.</td></tr>");
  d.rows.forEach(r=>{ const tr=$("<tr>").append($("<td>").text(r.student+" ("+r.student_no+")"),$("<td>").text(r.code+" "+r.title),$("<td>").text(r.semester),$("<td>").text(r.exam_type),
   $("<td>").text(r.marks+" / "+r.max_marks),$("<td>").text(r.pct+"%"),$("<td>").append($("<span class=\"badge bg-"+(r.grade=="F"?"danger":"success")+"\">").text(r.grade)));
   tr.append($("<td>").append(d.canWrite?$("<button class=\"btn btn-sm btn-outline-danger\"><i class=\"fa fa-trash\"></i></button>").on("click",()=>{ if(confirm("Delete this grade?")) $.post("api.php?a=g_delete",{csrf:csrf,id:r.id}).done(x=>{toast(x.message);load();}); }):"")); b.append(tr); }); }); }
$("[name=f_course]").on("change",load);
$("#gForm").on("submit",function(e){ e.preventDefault(); const p=$(this).serializeArray().reduce((o,x)=>(o[x.name]=x.value,o),{csrf:csrf});
 $.post("api.php?a=g_save",p).done(d=>{toast(d.message);load();this.reset();}).fail(x=>toast(x.responseJSON?.error||"Save failed",false)); });
load();</script>');
