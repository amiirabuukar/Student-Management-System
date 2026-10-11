<?php
// Generic list + modal form page for students / teachers / courses / classes.
require __DIR__.'/includes/bootstrap.php'; require __DIR__.'/includes/layout.php';
$mods=require __DIR__.'/includes/modules.php'; $m=$_GET['m']??''; if(!isset($mods[$m])){http_response_code(404);exit('Unknown module');}
$M=$mods[$m]; require_login($M['read']); $canWrite=in_array(role(),$M['write']);
page_header($M['title'],$m);
$js=['m'=>$m,'write'=>$canWrite,'columns'=>$M['columns'],'csrf'=>csrf_token()];
?>
<div class="card p-3"><div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
  <div class="input-group" style="max-width:280px"><span class="input-group-text"><i class="fa fa-search"></i></span><input id="q" class="form-control" placeholder="Search…"></div>
  <?php foreach($M['filters'] as $name=>$f): ?><select name="<?=e($name)?>" class="form-select flt" style="max-width:190px"><option value=""><?=e($f[1])?></option>
    <?php foreach(opts($pdo,$f[2]) as $v=>$l): ?><option value="<?=e($v)?>"><?=e($l)?></option><?php endforeach; ?></select><?php endforeach; ?>
  <div class="ms-auto d-flex gap-2">
    <?php if($m==='students'): ?><a class="btn btn-outline-secondary" href="reports.php?export=students"><i class="fa fa-file-csv"></i> Export CSV</a><?php endif; ?>
    <?php if($canWrite): ?><button class="btn btn-primary" id="addBtn"><i class="fa fa-plus"></i> Add <?=e(rtrim($M['title'],'s'))?></button><?php endif; ?></div></div>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr>
    <?php foreach($M['columns'] as $l): ?><th><?=e($l)?></th><?php endforeach; ?><th style="width:130px"></th></tr></thead><tbody id="tbody"></tbody></table></div>
  <nav><ul class="pagination pagination-sm mb-0 justify-content-end" id="pager"></ul></nav></div>
<?php if($canWrite): ?>
<div class="modal fade" id="formModal" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content needs-validation" id="recForm" novalidate enctype="multipart/form-data">
  <div class="modal-header"><h5 class="modal-title" id="formTitle"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body"><div class="alert alert-danger d-none" id="formErr"></div><input type="hidden" name="id" id="rec_id"><div class="row g-3">
  <?php foreach($M['fields'] as $f): [$n,$l,$t,$req]=$f; ?><div class="col-md-6"><label class="form-label"><?=e($l)?><?=$req?' *':''?></label>
    <?php if($t==='select'): ?><select name="<?=e($n)?>" class="form-select" <?=$req?'required':''?>><option value="">— select —</option>
      <?php foreach(opts($pdo,$f[4]) as $v=>$o): ?><option value="<?=e($v)?>"><?=e($o)?></option><?php endforeach; ?></select>
    <?php else: ?><input name="<?=e($n)?>" type="<?=$t==='file'?'file':e($t)?>" class="form-control" <?=$req?'required':''?> <?=$t==='file'?'accept="image/jpeg,image/png,image/webp"':''?> <?=$t==='number'?'min="1" max="20"':''?>><?php endif; ?>
    <div class="invalid-feedback">This field is required / invalid.</div></div><?php endforeach; ?></div></div>
  <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div></form></div></div>
<?php endif; page_footer('<script>window.MOD='.json_encode($js).';</script><script src="assets/crud.js"></script>');
