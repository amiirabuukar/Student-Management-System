<?php
require __DIR__.'/includes/bootstrap.php'; require __DIR__.'/includes/layout.php'; require_login(['admin','teacher']);
page_header('Attendance','attendance'); $classes=opts($pdo,'sql:SELECT id,name FROM classes');
?><div class="card p-3"><div class="row g-2 mb-3"><div class="col-md-4"><select id="class_id" class="form-select"><option value="">Select class…</option>
<?php foreach($classes as $v=>$l): ?><option value="<?=e($v)?>"><?=e($l)?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><input type="date" id="att_date" class="form-control" value="<?=date('Y-m-d')?>" max="<?=date('Y-m-d')?>"></div>
<div class="col-md-5 text-md-end"><button class="btn btn-outline-secondary btn-sm" id="markAll">Mark all Present</button> <button class="btn btn-primary" id="saveAtt"><i class="fa fa-save"></i> Save attendance</button></div></div>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Student ID</th><th>Name</th><th>Status</th></tr></thead><tbody id="attBody"><tr><td colspan="3" class="text-muted text-center py-4">Choose a class to begin.</td></tr></tbody></table></div></div>
<?php page_footer('<script>
const S=["Present","Absent","Late","Excused"];
function load(){ const c=$("#class_id").val(); if(!c) return;
 $("#attBody").html("<tr><td colspan=3 class=\"text-center py-4\"><div class=\"spinner-border spinner-border-sm\"></div></td></tr>");
 $.getJSON("api.php",{a:"att_load",class_id:c,date:$("#att_date").val()},d=>{ const b=$("#attBody").empty();
  if(!d.rows.length) return b.html("<tr><td colspan=3 class=\"text-muted text-center py-4\">No active students in this class.</td></tr>");
  d.rows.forEach(r=>{ const tr=$("<tr>"); tr.append($("<td>").text(r.student_no),$("<td>").text(r.name)); const td=$("<td>");
   S.forEach(s=>{ const id="s"+r.id+s; td.append($("<input type=radio class=btn-check>").attr({name:"st"+r.id,id:id,value:s,checked:(r.status||"Present")===s}),
    $("<label class=\"btn btn-sm btn-outline-primary me-1\">").attr("for",id).text(s)); }); tr.append(td); b.append(tr); }); }); }
$("#class_id,#att_date").on("change",load);
$("#markAll").on("click",()=>$("#attBody input[value=Present]").prop("checked",true));
$("#saveAtt").on("click",()=>{ const c=$("#class_id").val(); if(!c) return toast("Select a class first.",false);
 const p={csrf:csrf,class_id:c,date:$("#att_date").val(),status:{}}; $("#attBody tr").each(function(){ const r=$(this).find("input:checked"); if(r.length) p.status[r.attr("name").slice(2)]=r.val(); });
 $.post("api.php?a=att_save",p).done(d=>toast(d.message)).fail(x=>toast(x.responseJSON?.error||"Save failed",false)); });
</script>');
