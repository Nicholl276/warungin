CREATE DATABASE IF NOT EXISTS warungin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE warungin;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(80) NOT NULL DEFAULT 'Makanan',
  description VARCHAR(255) DEFAULT '',
  price INT NOT NULL,
  image_url VARCHAR(500) DEFAULT '',
  available TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(24) NOT NULL UNIQUE,
  customer_name VARCHAR(120) NOT NULL,
  table_label VARCHAR(50) DEFAULT '',
  order_type ENUM('Dine-in','Antar Kampus') NOT NULL DEFAULT 'Dine-in',
  notes TEXT,
  items_json JSON NOT NULL,
  total INT NOT NULL,
  payment_method ENUM('QRIS','Tunai') NOT NULL DEFAULT 'QRIS',
  payment_status ENUM('Menunggu Pembayaran','Menunggu Konfirmasi','Lunas','Ditolak') NOT NULL DEFAULT 'Menunggu Pembayaran',
  order_status ENUM('Menunggu','Dimasak','Diantar','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO menu_items (name, category, description, price, available)
SELECT 'Indomie Goreng', 'Mi Instan', 'Mi goreng favorit', 8000, 1
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE name='Indomie Goreng');
INSERT INTO menu_items (name, category, description, price, available)
SELECT 'Indomie Telur', 'Mi Instan', 'Mi dengan telur', 12000, 1
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE name='Indomie Telur');
INSERT INTO menu_items (name, category, description, price, available)
SELECT 'Nasi Goreng', 'Makanan', 'Nasi goreng hangat', 15000, 1
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE name='Nasi Goreng');
INSERT INTO menu_items (name, category, description, price, available)
SELECT 'Es Teh', 'Minuman', 'Teh dingin menyegarkan', 4000, 1
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE name='Es Teh');
INSERT INTO menu_items (name, category, description, price, available)
SELECT 'Kopi Susu', 'Minuman', 'Kopi susu hangat atau dingin', 6000, 1
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE name='Kopi Susu');
