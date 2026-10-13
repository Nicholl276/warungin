<?php
require __DIR__ . '/../config.php'; require_admin();
if($_SERVER['REQUEST_METHOD']!=='POST')json_response(['error'=>'Method not allowed'],405);
$in=json_decode(file_get_contents('php://input'),true)??[];$id=(int)($in['id']??0);$status=(string)($in['order_status']??'');$payment=(string)($in['payment_status']??'');
$statuses=['Menunggu','Dimasak','Diantar','Selesai','Dibatalkan'];$payments=['Menunggu Pembayaran','Menunggu Konfirmasi','Lunas','Ditolak'];
if($id<1 || ($status!==''&&!in_array($status,$statuses,true)) || ($payment!==''&&!in_array($payment,$payments,true)))json_response(['error'=>'Data tidak valid.'],422);
if($status!==''){ $s=db()->prepare("UPDATE orders SET order_status=? WHERE id=?");$s->execute([$status,$id]);}
if($payment!==''){ $s=db()->prepare("UPDATE orders SET payment_status=? WHERE id=?");$s->execute([$payment,$id]);}
json_response(['ok'=>true]);
