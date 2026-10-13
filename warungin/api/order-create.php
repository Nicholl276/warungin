<?php
require __DIR__ . '/../config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error'=>'Method not allowed'],405);
$input=json_decode(file_get_contents('php://input'),true) ?? [];
$name=trim((string)($input['customer_name']??''));
$items=$input['items']??[];
if($name==='' || !is_array($items) || count($items)===0) json_response(['error'=>'Nama dan menu wajib diisi.'],422);
if(count($items)>40) json_response(['error'=>'Terlalu banyak item.'],422);
$ids=[]; foreach($items as $it){$id=(int)($it['id']??0);$qty=(int)($it['qty']??0);if($id<1||$qty<1||$qty>99)json_response(['error'=>'Item pesanan tidak valid.'],422);$ids[$id]=$qty;}
$pdo=db();$pdo->beginTransaction();
try{
  $placeholders=implode(',',array_fill(0,count($ids),'?'));
  $q=$pdo->prepare("SELECT id,name,price,available FROM menu_items WHERE id IN ($placeholders)");$q->execute(array_keys($ids));$rows=$q->fetchAll();
  if(count($rows)!==count($ids))throw new RuntimeException('Ada menu yang tidak ditemukan.');
  $lineItems=[];$total=0;
  foreach($rows as $row){if(!(int)$row['available'])throw new RuntimeException($row['name'].' sedang habis.');$qty=$ids[(int)$row['id']];$subtotal=(int)$row['price']*$qty;$total+=$subtotal;$lineItems[]=['id'=>(int)$row['id'],'name'=>$row['name'],'price'=>(int)$row['price'],'qty'=>$qty,'subtotal'=>$subtotal];}
  $code='BNK-'.random_int(1000,9999).'-'.strtoupper(substr(bin2hex(random_bytes(2)),0,4));
  $type=($input['order_type']??'Dine-in')==='Antar Kampus'?'Antar Kampus':'Dine-in';
  $method=($input['payment_method']??'QRIS')==='Tunai'?'Tunai':'QRIS';
  $stmt=$pdo->prepare("INSERT INTO orders(order_code,customer_name,table_label,order_type,notes,items_json,total,payment_method) VALUES(?,?,?,?,?,?,?,?)");
  $stmt->execute([$code,$name,trim((string)($input['table_label']??'')),$type,trim((string)($input['notes']??'')),json_encode($lineItems,JSON_UNESCAPED_UNICODE),$total,$method]);
  $id=(int)$pdo->lastInsertId();$pdo->commit();
  json_response(['ok'=>true,'id'=>$id,'code'=>$code,'total'=>$total,'items'=>$lineItems,'payment_method'=>$method,'whatsapp'=>WHATSAPP_NUMBER]);
}catch(Throwable $e){$pdo->rollBack();json_response(['error'=>$e instanceof RuntimeException?$e->getMessage():'Pesanan gagal dibuat.'],400);}
