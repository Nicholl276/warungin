<?php
require __DIR__ . '/../config.php'; require_admin();
$after=(int)($_GET['after']??0);
$q=db()->prepare("SELECT * FROM orders WHERE id>? ORDER BY id DESC LIMIT 100");$q->execute([$after]);$rows=$q->fetchAll();
foreach($rows as &$r){$r['items']=json_decode($r['items_json'],true)??[];unset($r['items_json']);$r['total']=(int)$r['total'];$r['id']=(int)$r['id'];}
json_response(['orders'=>$rows,'server_time'=>date(DATE_ATOM)]);
