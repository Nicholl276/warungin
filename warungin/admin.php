<?php
require __DIR__.'/config.php';require_admin();
$pdo=db();

// Handle Tambah Menu (Tanpa Foto)
$menu_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_menu') {
    $name = trim($_POST['menu_name']);
    $price = (int)$_POST['menu_price'];
    $category = trim($_POST['menu_category']);

    if (!empty($name) && $price > 0) {
        $stmt = $pdo->prepare("INSERT INTO menu_items (name, price, category) VALUES (?, ?, ?)");
        $stmt->execute([$name, $price, $category]);
        $menu_msg = "Menu berhasil ditambahkan!";
    }
}

// Handle Hapus Menu
if (isset($_GET['delete_menu'])) {
    $menu_id = (int)$_GET['delete_menu'];
    $pdo->prepare("DELETE FROM menu_items WHERE id = ?")->execute([$menu_id]);
    header("Location: admin.php");
    exit;
}

$stats=$pdo->query("SELECT SUM(order_status='Menunggu') waiting,SUM(order_status='Dimasak') cooking,SUM(order_status='Diantar') delivery,COALESCE(SUM(CASE WHEN payment_status='Lunas' THEN total ELSE 0 END),0) revenue,COUNT(*) count_all FROM orders")->fetch();
$orders=$pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 100")->fetchAll();
foreach($orders as &$o)$o['items']=json_decode($o['items_json'],true)??[];

$menus = $pdo->query("SELECT * FROM menu_items ORDER BY id DESC")->fetchAll();

// Data Laporan Harian
$report_date = $_GET['report_date'] ?? date('Y-m-d');
$stmt_rep = $pdo->prepare("SELECT * FROM orders WHERE DATE(created_at) = ? AND payment_status = 'Lunas' ORDER BY id DESC");
$stmt_rep->execute([$report_date]);
$daily_orders = $stmt_rep->fetchAll();
$total_omzet_harian = 0;
foreach($daily_orders as $do) { $total_omzet_harian += (int)$do['total']; }
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin <?=e(APP_NAME)?></title><link rel="stylesheet" href="assets/style.css"><style>
@media print { 
    body { background: #fff !important; color: #000 !important; } 
    .admin-header, .admin-toolbar, .stats-grid, .admin-main > .stats-grid:nth-of-type(2), .orders-grid, .admin-foot, .no-print { display: none !important; } 
    .print-report-only { display: block !important; padding: 20px; } 
}
.print-report-only { display: none; }
</style></head><body class="admin-page"><header class="admin-header"><div class="admin-brand"><span class="admin-logo"><i data-lucide="chef-hat"></i></span><div><h1><?=e(APP_NAME)?> ADMIN <span>KDS & POS</span></h1><p><i data-lucide="radio"></i> Realtime Order Monitor · Kampus UNISSULA Kaligawe</p></div></div><div class="admin-actions"><span class="live-pill"><i data-lucide="radio-tower"></i> Realtime</span><a href="index.php" class="outline-button"><i data-lucide="external-link"></i> Portal Mahasiswa</a><a href="admin-logout.php" class="outline-button"><i data-lucide="log-out"></i> Keluar</a></div></header>
<main class="admin-main">
<section class="stats-grid"><article class="stat-card"><span class="stat-icon muted"><i data-lucide="clock-3"></i></span><div><p>Menunggu Antrean</p><strong id="stat-waiting"><?= (int)$stats['waiting']?></strong></div></article><article class="stat-card"><span class="stat-icon orange"><i data-lucide="flame"></i></span><div><p>Sedang Dimasak</p><strong id="stat-cooking"><?= (int)$stats['cooking']?></strong></div></article><article class="stat-card"><span class="stat-icon green"><i data-lucide="truck"></i></span><div><p>Diantar ke Kelas</p><strong id="stat-delivery"><?= (int)$stats['delivery']?></strong></div></article><article class="stat-card"><span class="stat-icon green"><i data-lucide="wallet"></i></span><div><p>Total Omzet Lunas</p><strong id="stat-revenue">Rp <?=number_format((int)$stats['revenue'],0,',','.')?></strong></div></article></section>

<!-- SECTION KELOLA MENU & LAPORAN HARIAN -->
<section style="display: flex; gap: 1.5rem; margin-bottom: 2rem; width: 100%;">
    <!-- KELOLA MENU -->
    <article class="stat-card" style="flex: 1; padding: 1.5rem; display: flex; flex-direction: column;">
        <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 8px; font-size: 1.1rem;"><i data-lucide="utensils" style="width:18px;height:18px;"></i> Kelola Menu Makanan</h3>
        <?php if($menu_msg): ?><p style="color: #10b981; font-size: 0.9rem; margin-bottom: 10px;"><?=e($menu_msg)?></p><?php endif; ?>
        <form method="POST" style="display: flex; flex-direction: column; gap: 10px;">
            <input type="hidden" name="action" value="add_menu">
            <input type="text" name="menu_name" placeholder="Nama Menu (cth: Es Teh)" required style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); color: inherit; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
            <div style="display: flex; gap: 10px; width: 100%;">
                <input type="number" name="menu_price" placeholder="Harga (cth: 5000)" required style="width: 50%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); color: inherit; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
                <input type="text" name="menu_category" placeholder="Kategori" style="width: 50%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); color: inherit; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
            </div>
            <button type="submit" class="outline-button" style="background: var(--accent); color: #fff; justify-content: center; padding: 10px; font-size: 0.95rem; margin-top: 5px; width: 100%;">Tambah Menu</button>
        </form>
        <div style="max-height: 160px; overflow-y: auto; margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
            <?php foreach($menus as $m): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,0.04);">
                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 250px;"><?=e($m['name'])?> (Rp <?=number_format((int)$m['price'],0,',','.')?>)</span>
                <a href="admin.php?delete_menu=<?=$m['id']?>" onclick="return confirm('Hapus menu ini?')" style="color: #ef4444;"><i data-lucide="trash-2" style="width:16px;height:16px;"></i></a>
            </div>
            <?php endforeach; ?>
        </div>
    </article>

    <!-- CETAK LAPORAN HARIAN -->
    <article class="stat-card" style="flex: 1; padding: 1.5rem; display: flex; flex-direction: column;">
        <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 8px; font-size: 1.1rem;"><i data-lucide="printer" style="width:18px;height:18px;"></i> Cetak Laporan Harian</h3>
        <form method="GET" style="display: flex; gap: 10px; margin-bottom: 12px; width: 100%;">
            <input type="date" name="report_date" value="<?=e($report_date)?>" style="flex: 1; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); color: inherit; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;" onchange="this.form.submit()">
            <button type="button" onclick="window.print()" class="outline-button" style="background: var(--accent); color: #fff; padding: 10px 16px; font-size: 0.95rem; white-space: nowrap;"><i data-lucide="printer" style="width:16px;height:16px;"></i> Cetak</button>
        </form>
        <div style="max-height: 220px; overflow-y: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 8px;">
            <table style="width: 100%; font-size: 0.85rem; border-collapse: collapse;">
                <?php if(empty($daily_orders)): ?>
                <tr><td colspan="3" style="text-align: center; color: #888; padding: 25px;">Tidak ada transaksi lunas</td></tr>
                <?php else: foreach($daily_orders as $do): ?>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                    <td style="padding: 8px 4px; width: 35%;">#<?=e($do['order_code'])?></td>
                    <td style="padding: 8px 4px; width: 40%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?=e($do['customer_name'])?></td>
                    <td style="padding: 8px 4px; width: 25%; text-align: right;">Rp <?=number_format((int)$do['total'],0,',','.')?></td>
                </tr>
                <?php endforeach; endif; ?>
            </table>
        </div>
    </article>
</section>

<!-- KHUSUS CETAK (PRINT VIEW) -->
<div class="print-report-only">
    <h2>LAPORAN PENJUALAN HARIAN - <?=e(APP_NAME)?></h2>
    <p>Tanggal: <strong><?=e($report_date)?></strong></p>
    <p>Total Transaksi Lunas: <strong><?=count($daily_orders)?> pesanan</strong></p>
    <p style="font-size: 1.1rem; margin-bottom: 15px;">Total Omzet Harian: <strong>Rp <?=number_format($total_omzet_harian,0,',','.')?></strong></p>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="border-bottom: 2px solid #000; text-align: left;">
                <th style="padding: 6px;">Kode Pesanan</th>
                <th style="padding: 6px;">Nama Pemesan</th>
                <th style="padding: 6px;">Tipe</th>
                <th style="padding: 6px; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($daily_orders as $do): ?>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 6px;">#<?=e($do['order_code'])?></td>
                <td style="padding: 6px;"><?=e($do['customer_name'])?></td>
                <td style="padding: 6px;"><?=e($do['order_type'])?></td>
                <td style="padding: 6px; text-align: right;">Rp <?=number_format((int)$do['total'],0,',','.')?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<section class="admin-toolbar"><div class="admin-tabs"><button class="admin-tab active" data-filter="all">Semua Pesanan <b id="count-all"><?= (int)$stats['count_all']?></b></button><button class="admin-tab" data-filter="Antar Kampus">Antar Kampus</button><button class="admin-tab" data-filter="Dine-in">Makan di Meja</button><button class="admin-tab" data-filter="Dimasak">Dimasak</button><button class="admin-tab" data-filter="Selesai">Selesai</button></div><div class="sync-label"><span class="sync-dot"></span> Sinkron tiap 3 detik <button id="refresh-orders" class="outline-button"><i data-lucide="refresh-cw"></i> Perbarui Data</button></div></section>
<div id="orders-grid" class="orders-grid"><?php foreach($orders as $o): ?><article class="order-card" data-id="<?=$o['id']?>" data-type="<?=e($o['order_type'])?>" data-status="<?=e($o['order_status'])?>"><div class="order-top"><div><h2>#<?=e($o['order_code'])?></h2><p><?=e($o['customer_name'])?> · <?=e($o['table_label'])?></p></div><span class="status-badge <?=strtolower(str_replace(' ','-',$o['order_status']))?>"><?=e($o['order_status'])?></span></div><div class="order-kind"><i data-lucide="<?=$o['order_type']==='Antar Kampus'?'truck':'utensils'?>"></i><div><b><?=e($o['order_type'])==='Dine-in'?'MAKAN DI '.APP_NAME:'ANTAR KE KAMPUS UNISSULA'?></b><p><?=e($o['table_label']?:'Lokasi belum diisi')?></p></div></div><div class="order-items"><?php foreach($o['items'] as $it):?><div><span><?=e($it['qty'])?>× <?=e($it['name'])?></span><b>Rp <?=number_format((int)$it['subtotal'],0,',','.')?></b></div><?php endforeach;?></div><div class="order-total"><span>Total · <?=e($o['payment_method'])?></span><strong>Rp <?=number_format((int)$o['total'],0,',','.')?></strong></div><div class="payment-row"><span>Pembayaran: <b><?=e($o['payment_status'])?></b></span><select class="payment-select" aria-label="Status pembayaran"><option <?= $o['payment_status']==='Menunggu Pembayaran'?'selected':''?>>Menunggu Pembayaran</option><option <?= $o['payment_status']==='Menunggu Konfirmasi'?'selected':''?>>Menunggu Konfirmasi</option><option <?= $o['payment_status']==='Lunas'?'selected':''?>>Lunas</option><option <?= $o['payment_status']==='Ditolak'?'selected':''?>>Ditolak</option></select></div><label class="status-control">Status pesanan<select class="status-select"><option <?= $o['order_status']==='Menunggu'?'selected':''?>>Menunggu</option><option <?= $o['order_status']==='Dimasak'?'selected':''?>>Dimasak</option><option <?= $o['order_status']==='Diantar'?'selected':''?>>Diantar</option><option <?= $o['order_status']==='Selesai'?'selected':''?>>Selesai</option><option <?= $o['order_status']==='Dibatalkan'?'selected':''?>>Dibatalkan</option></select></label></article><?php endforeach;?></div><div id="no-orders" class="empty-state" hidden><i data-lucide="inbox"></i><p>Belum ada pesanan pada filter ini.</p></div><div class="admin-foot"><span id="last-sync">Memuat status sinkronisasi...</span><span>Panel admin · WARUNGIN</span></div></main><script src="https://unpkg.com/lucide@latest"></script><script>lucide.createIcons();</script><script src="assets/admin.js"></script></body></html>