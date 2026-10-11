<?php
require __DIR__.'/includes/bootstrap.php';
$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $locked = ($_SESSION['fails'] ?? 0) >= 5 && time() < ($_SESSION['lock'] ?? 0);
  if (!csrf_ok()) $err='Session expired. Please try again.';
  elseif ($locked) $err='Too many failed attempts. Try again in a few minutes.';
  else {
    $st=$pdo->prepare('SELECT * FROM users WHERE email=?'); $st->execute([trim($_POST['email']??'')]); $u=$st->fetch();
    if ($u && password_verify($_POST['password']??'', $u['password'])) {
      session_regenerate_id(true); $_SESSION['user']=['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']];
      unset($_SESSION['fails'],$_SESSION['lock']); header('Location: dashboard.php'); exit;
    }
    $_SESSION['fails']=($_SESSION['fails']??0)+1; $_SESSION['lock']=time()+300; $err='Invalid email or password.';
  }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login | EduCampus SMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"><link href="assets/style.css" rel="stylesheet"></head>
<body class="login-bg d-flex align-items-center justify-content-center min-vh-100"><div class="card login-card shadow-lg p-4">
<h3 class="text-center mb-1"><i class="fa fa-graduation-cap text-primary"></i> Edu<span class="text-accent">Campus</span></h3>
<p class="text-center text-muted">Student Management System</p>
<?php if($err): ?><div class="alert alert-danger py-2"><?=e($err)?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required autofocus></div>
<div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
<button class="btn btn-primary w-100">Sign in</button></form>
<p class="small text-muted mt-3 mb-0">Demo: admin@school.test / Admin@123</p></div></body></html>
