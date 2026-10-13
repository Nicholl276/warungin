# WARUNGIN PHP — Prototype

## Isi fitur
- Landing page menu dengan loading screen setelah halaman dibuka dari QR.
- Cari dan filter kategori menu, keranjang, hitung total.
- Buat order ke database MySQL.
- Pilihan QRIS atau tunai.
- Kirim rincian pesanan melalui WhatsApp.
- Login admin, monitor order, filter status/jenis, update status pembayaran dan pesanan.
- Dashboard melakukan polling API setiap 3 detik untuk pembaruan mendekati real-time.
- Icon menggunakan Lucide, bukan emoji.

## Kebutuhan
PHP 8.1+, MySQL 8/MariaDB, Apache/Nginx. Untuk icon, font dan CDN Lucide dibutuhkan internet.

## Instalasi XAMPP
1. Ekstrak folder `warungin_php` ke `C:\xampp\htdocs\warungin`.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buka phpMyAdmin, import `schema.sql`.
4. Edit `config.php` untuk kredensial database dan nomor WhatsApp.
5. Buka `http://localhost/warungin/setup-admin.php`, buat akun admin dengan password minimal 10 karakter.
6. HAPUS `setup-admin.php` setelah akun dibuat.
7. Buka `http://localhost/warungin/` untuk halaman pelanggan.
8. Buka `http://localhost/warungin/admin-login.php` untuk dashboard admin.

## QRIS — penting
`assets/qris-placeholder.svg` hanyalah placeholder, bukan QRIS yang dapat dibayar. Ganti dengan gambar QRIS resmi milik merchant. Versi ini belum memverifikasi pembayaran otomatis. Admin harus mengubah status pembayaran menjadi "Lunas" setelah memverifikasi transaksi. QRIS dinamis dan verifikasi otomatis memerlukan integrasi resmi payment gateway (mis. Midtrans/Xendit) serta kredensial merchant.

## Realtime
Dashboard mengambil data dari API setiap 3 detik. Ini polling berkala (near-real-time), bukan WebSocket. Untuk trafik lebih besar, gunakan WebSocket/SSE dan antrean event.

## Keamanan sebelum produksi
- `DEMO_MODE` masih `true`; ganti setelah konfigurasi produksi.
- Tambahkan CSRF protection, rate limiting, validasi server yang lebih ketat, HTTPS, backup, audit log, dan pengelolaan role.
- Batasi akses file konfigurasi dan hapus file setup.
- Jangan menaruh kredensial payment gateway di JavaScript.
- Pertimbangkan menambahkan halaman CRUD menu untuk admin; pada versi ini menu awal disiapkan lewat database.
