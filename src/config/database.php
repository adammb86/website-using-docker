<?php
/**
 * File: config/database.php
 * Fungsi: Membuat koneksi ke database MySQL menggunakan PDO.
 *
 * File ini di-include (require_once) oleh semua halaman, sehingga
 * pengaturan koneksi cukup ditulis di satu tempat saja.
 * Selain koneksi, file ini juga:
 *   - memulai session (dipakai untuk flash message)
 *   - memuat fungsi-fungsi bantu (helper) dari includes/fungsi.php
 */

// Mulai session jika belum dimulai (dibutuhkan untuk pesan flash)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pengaturan koneksi (sesuai docker-compose.yml)
// Host "db" adalah nama service MySQL di docker-compose, bukan "localhost".
$db_host = 'db';
$db_port = '3306';
$db_name = 'app_db';
$db_user = 'app_user';
$db_pass = 'app_secret';

// DSN (Data Source Name) = alamat lengkap database untuk PDO
$dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        // Jika query error, lempar Exception agar bisa ditangkap try-catch
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Hasil query berupa array asosiatif: $baris['nama']
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Gunakan prepared statement asli dari MySQL
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Koneksi gagal: tampilkan pesan sederhana lalu hentikan program
    http_response_code(500);
    echo '<h2>Koneksi database gagal</h2>';
    echo '<p>Pastikan container MySQL sudah berjalan (<code>docker compose up -d</code>).</p>';
    echo '<p>Detail: ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}

// Muat fungsi-fungsi bantu
require_once __DIR__ . '/../includes/fungsi.php';
