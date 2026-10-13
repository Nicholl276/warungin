<?php
require __DIR__.'/config.php';
if(!empty($_SESSION['admin_id'])){header('Location: admin.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $username=trim($_POST['username']??'');$password=$_POST['password']??'';
  $stmt=db()->prepare("SELECT * FROM admins WHERE username=? LIMIT 1");$stmt->execute([$username]);$admin=$stmt->fetch();
  
  // Ubah bagian ini menggunakan perbandingan langsung karena password di database plain text
  if($admin && $password === $admin['password']){
    session_regenerate_id(true);
    $_SESSION['admin_id']=$admin['id'];
    $_SESSION['admin_username']=$admin['username'];
    header('Location: admin.php');
    exit;
  }
  
  $error='Username atau password salah.';
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login Admin — WARUNGIN</title><link rel="stylesheet" href="assets/style.css"></head><body class="login-page"><form method="post" class="login-card"><div class="login-icon"><i data-lucide="chef-hat"></i></div><span class="eyebrow">PANEL PENGELOLA</span><h1>Admin WARUNGIN</h1><p>Masuk untuk memantau dan mengelola pesanan.</p><?php if($error):?><div class="alert"><?=e($error)?></div><?php endif;?><label>Username<input name="username" required autocomplete="username"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button class="primary-button full">Masuk ke dashboard</button><a href="index.php" class="back-link">Kembali ke menu pelanggan</a></form><script src="https://unpkg.com/lucide@latest"></script><script>lucide.createIcons()</script></body></html>