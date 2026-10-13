<?php
require __DIR__.'/config.php';
/* Run once locally, then DELETE this file before public deployment. */
$done='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $u=trim($_POST['username']??'admin');$p=$_POST['password']??'';
 if(strlen($p)<10)$done='Password minimal 10 karakter.';
 else{$s=db()->prepare("INSERT INTO admins(username,password_hash) VALUES(?,?)");try{$s->execute([$u,password_hash($p,PASSWORD_DEFAULT)]);$done='Admin dibuat. Hapus file setup-admin.php sekarang.';}catch(Throwable $e){$done='Gagal membuat admin. Username mungkin sudah digunakan.';}}
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Setup Admin</title><link rel="stylesheet" href="assets/style.css"></head><body class="login-page"><form method="post" class="login-card"><span class="eyebrow">SETUP AWAL</span><h1>Buat akun admin</h1><p>Jalankan satu kali setelah mengimpor schema.sql.</p><?php if($done):?><div class="alert"><?=e($done)?></div><?php endif;?><label>Username<input name="username" value="admin" required></label><label>Password (min. 10 karakter)<input type="password" name="password" minlength="10" required></label><button class="primary-button full">Buat admin</button></form></body></html>
