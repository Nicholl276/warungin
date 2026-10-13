<?php
declare(strict_types=1);
session_start();

/*
 * WARUNGIN configuration.
 * Update these values for your hosting/database.
 */
const DB_HOST = '127.0.0.1';
const DB_NAME = 'warungin';
const DB_USER = 'root';
const DB_PASS = '';
const APP_NAME = 'BURJONUK';
const WHATSAPP_NUMBER = '62895367025656'; // Replace with the shop's WhatsApp number.
const QRIS_IMAGE = 'assets/qris-placeholder.svg'; // Replace with your official QRIS image.
const DEMO_MODE = true; // Set false for deployment and configure real credentials.
date_default_timezone_set('Asia/Jakarta');

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    return $pdo;
}
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function json_response($data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function require_admin(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: admin-login.php');
        exit;
    }
}
