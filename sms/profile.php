<?php
require __DIR__.'/includes/bootstrap.php'; require __DIR__.'/includes/layout.php'; require_login();
$msg=''; $ok=true;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!csrf_ok()) { $msg='Invalid session token.'; $ok=false; }
  else {
    $name=trim($_POST['name']??''); $new=$_POST['new_password']??'';
    if ($name==='') { $msg='Name is required.'; $ok=false; }
    else {
      if ($new!=='') {
        $row=$pdo->prepare('SELECT password FROM users WHERE id=?'); $row->execute([user()['id']]);
        if (!password_verify($_POST['current_password']??'', $row->fetchColumn())) { $msg='Current password is incorrect.'; $ok=false; }
        elseif (strlen($new)<8) { $msg='New password must be at least 8 characters.'; $ok=false; }
        else $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),user()['id']]);
      }
      if ($ok) { $pdo->prepare('UPDATE users SET name=? WHERE id=?')->execute([$name,user()['id']]); $_SESSION['user']['name']=$name; $msg='Profile updated.'; }
    }
  }
}
page_header('My Profile','');
?><div class="card p-4" style="max-width:520px"><?php if($msg): ?><div class="alert alert-<?=$ok?'success':'danger'?>"><?=e($msg)?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<div class="mb-3"><label class="form-label">Email</label><input class="form-control" value="<?=e(user()['email'])?>" disabled></div>
<div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="<?=e(user()['name'])?>" required></div>
<hr><p class="text-muted small">Leave blank to keep your current password.</p>
<div class="mb-3"><label class="form-label">Current password</label><input type="password" name="current_password" class="form-control"></div>
<div class="mb-3"><label class="form-label">New password (min 8)</label><input type="password" name="new_password" class="form-control"></div>
<button class="btn btn-primary">Save changes</button></form></div><?php page_footer();
