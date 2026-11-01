<?php
/**
 * File: install.php
 * Fungsi: Membuat tabel database (kategori & perangkat) dan mengisi data contoh.
 *
 * Cara pakai: buka http://localhost:8080/install.php di browser.
 * - Tabel dibuat dengan "CREATE TABLE IF NOT EXISTS", jadi aman dijalankan berulang.
 * - Data contoh hanya dimasukkan jika tabel masih kosong.
 * - Tombol "Reset" menghapus semua tabel lalu membuat ulang dari awal.
 */
require_once __DIR__ . '/config/database.php';

$log = [];      // Catatan langkah-langkah yang berhasil
$gagal = null;  // Pesan error jika ada

// Data contoh kategori: [nama, deskripsi]
$seed_kategori = [
    ['Router', 'Perangkat penghubung antar jaringan (routing paket antar network).'],
    ['Switch', 'Perangkat penghubung banyak komputer dalam satu jaringan lokal (LAN).'],
    ['Access Point', 'Pemancar jaringan nirkabel (Wi-Fi) untuk perangkat klien.'],
    ['Modem', 'Perangkat penghubung jaringan lokal ke penyedia internet (ISP).'],
    ['Kabel', 'Media transmisi: kabel UTP, fiber optik, patch cord, dll.'],
    ['Server', 'Komputer penyedia layanan jaringan (DHCP, DNS, file, web).'],
    ['UPS', 'Catu daya cadangan agar perangkat tetap menyala saat listrik padam.'],
    ['Printer Jaringan', 'Printer yang dapat digunakan bersama melalui jaringan.'],
];

// Data contoh perangkat:
// [nama, merk, tipe, ip, lokasi, status, nama_kategori, tahun, keterangan]
$seed_perangkat = [
    ['Router Utama Lab A', 'Cisco', 'ISR 1941', '192.168.10.1', 'Lab A', 'aktif', 'Router', 2019, 'Gateway utama Lab A, dipakai praktik routing statis & dinamis.'],
    ['Router Praktik MikroTik', 'MikroTik', 'RB951Ui-2HnD', '192.168.20.1', 'Lab B', 'aktif', 'Router', 2021, 'Untuk praktik konfigurasi hotspot dan firewall.'],
    ['Switch Lab B', 'TP-Link', 'TL-SG1024D', null, 'Lab B', 'aktif', 'Switch', 2020, 'Switch unmanaged 24 port gigabit.'],
    ['Switch Managed Lab A', 'Cisco', 'Catalyst 2960', '192.168.10.2', 'Lab A', 'perbaikan', 'Switch', 2018, 'Port 12-16 sering putus, sedang dicek teknisi.'],
    ['Access Point Ruang Guru', 'MikroTik', 'cAP ac', '192.168.30.5', 'Ruang Guru', 'aktif', 'Access Point', 2022, 'SSID: WIFI-GURU.'],
    ['Access Point Perpustakaan', 'Ubiquiti', 'UniFi AP AC Lite', '192.168.30.6', 'Perpustakaan', 'rusak', 'Access Point', 2017, 'Tidak menyala, adaptor PoE terbakar.'],
    ['Modem Fiber ISP', 'Huawei', 'HG8245H5', '192.168.100.1', 'Ruang Server', 'aktif', 'Modem', 2021, 'Modem ONT dari ISP, bandwidth 100 Mbps.'],
    ['Kabel UTP Cat6 (1 roll)', 'Belden', 'Cat6 305m', null, 'Gudang Lab', 'aktif', 'Kabel', 2023, 'Sisa sekitar 120 meter untuk praktik crimping.'],
    ['Server Lab TKJ', 'Dell', 'PowerEdge T140', '192.168.10.10', 'Ruang Server', 'aktif', 'Server', 2020, 'Debian 12: DHCP, DNS, dan web server praktik.'],
    ['UPS Ruang Server', 'APC', 'Back-UPS 1100VA', null, 'Ruang Server', 'aktif', 'UPS', 2020, 'Baterai diganti tahun 2024.'],
    ['UPS Lab A', 'ICA', 'CE 1200', null, 'Lab A', 'nonaktif', 'UPS', 2016, 'Sudah tidak dipakai, menunggu penghapusan aset.'],
    ['Printer Ruang TU', 'Epson', 'L6270', '192.168.40.20', 'Ruang TU', 'aktif', 'Printer Jaringan', 2022, 'Printer Wi-Fi untuk tata usaha.'],
];

// Jika tombol reset ditekan (form POST), hapus tabel terlebih dahulu
$reset = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset']));

try {
    if ($reset) {
        // Tabel perangkat dihapus dulu karena punya foreign key ke kategori
        $pdo->exec('DROP TABLE IF EXISTS perangkat');
        $pdo->exec('DROP TABLE IF EXISTS kategori');
        $log[] = 'Tabel lama dihapus (reset).';
    }

    // 1. Buat tabel kategori
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS kategori (
            id        INT AUTO_INCREMENT PRIMARY KEY,
            nama      VARCHAR(100) NOT NULL,
            deskripsi TEXT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $log[] = 'Tabel <code>kategori</code> siap.';

    // 2. Buat tabel perangkat (dengan foreign key ke kategori)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS perangkat (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            nama            VARCHAR(150) NOT NULL,
            merk            VARCHAR(100),
            tipe            VARCHAR(100),
            ip_address      VARCHAR(45),
            lokasi          VARCHAR(150),
            status          ENUM('aktif','rusak','perbaikan','nonaktif') DEFAULT 'aktif',
            kategori_id     INT NOT NULL,
            tahun_pengadaan YEAR,
            keterangan      TEXT,
            CONSTRAINT fk_perangkat_kategori
                FOREIGN KEY (kategori_id) REFERENCES kategori(id)
                ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $log[] = 'Tabel <code>perangkat</code> siap.';

    // 3. Isi data kategori jika masih kosong
    $jumlah = $pdo->query('SELECT COUNT(*) FROM kategori')->fetchColumn();
    if ($jumlah == 0) {
        $stmt = $pdo->prepare('INSERT INTO kategori (nama, deskripsi) VALUES (?, ?)');
        foreach ($seed_kategori as $k) {
            $stmt->execute($k);
        }
        $log[] = count($seed_kategori) . ' data kategori ditambahkan.';
    } else {
        $log[] = 'Data kategori sudah ada, tidak ditambah lagi.';
    }

    // 4. Isi data perangkat jika masih kosong
    $jumlah = $pdo->query('SELECT COUNT(*) FROM perangkat')->fetchColumn();
    if ($jumlah == 0) {
        // Buat "peta" nama kategori => id, agar seed bisa memakai nama kategori
        $peta = [];
        foreach ($pdo->query('SELECT id, nama FROM kategori') as $row) {
            $peta[$row['nama']] = $row['id'];
        }

        $stmt = $pdo->prepare('
            INSERT INTO perangkat
                (nama, merk, tipe, ip_address, lokasi, status, kategori_id, tahun_pengadaan, keterangan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        foreach ($seed_perangkat as $p) {
            [$nama, $merk, $tipe, $ip, $lokasi, $status, $kat, $tahun, $ket] = $p;
            $stmt->execute([$nama, $merk, $tipe, $ip, $lokasi, $status, $peta[$kat], $tahun, $ket]);
        }
        $log[] = count($seed_perangkat) . ' data perangkat ditambahkan.';
    } else {
        $log[] = 'Data perangkat sudah ada, tidak ditambah lagi.';
    }
} catch (PDOException $e) {
    $gagal = $e->getMessage();
}

$judul = 'Instalasi Database';
require __DIR__ . '/includes/header.php';
?>

<div class="breadcrumbs text-sm mb-4">
    <ul>
        <li><a href="/index.php">Dashboard</a></li>
        <li>Instalasi Database</li>
    </ul>
</div>

<div class="card bg-base-100 shadow-sm">
    <div class="card-body p-6">
        <h1 class="card-title text-2xl">Instalasi Database</h1>

        <?php if ($gagal): ?>
            <div role="alert" class="alert alert-error">
                <span>Instalasi gagal: <?= e($gagal) ?></span>
            </div>
        <?php else: ?>
            <div role="alert" class="alert alert-success">
                <span>Instalasi selesai. Database siap digunakan.</span>
            </div>
        <?php endif; ?>

        <ul class="list-disc pl-6 my-2 space-y-1">
            <?php foreach ($log as $baris): ?>
                <li><?= $baris /* berisi HTML buatan kita sendiri, aman */ ?></li>
            <?php endforeach; ?>
        </ul>

        <div class="card-actions justify-between items-center mt-4">
            <form method="post" onsubmit="return confirm('Semua data akan dihapus dan diisi ulang dengan data contoh. Lanjutkan?')">
                <button type="submit" name="reset" value="1" class="btn btn-ghost btn-sm text-error">Reset ke data contoh</button>
            </form>
            <a href="/index.php" class="btn btn-primary">Buka Dashboard</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
