<?php
/**
 * File: index.php
 * Fungsi: Halaman Dashboard.
 * Menampilkan ringkasan jumlah perangkat (total & per status),
 * jumlah perangkat per kategori, dan 5 perangkat yang terakhir ditambahkan.
 */
require_once __DIR__ . '/config/database.php';

$belum_install = false;

try {
    // Jumlah perangkat per status, contoh hasil: ['aktif' => 8, 'rusak' => 1, ...]
    $per_status = array_fill_keys(DAFTAR_STATUS, 0);
    $hasil = $pdo->query('SELECT status, COUNT(*) AS jumlah FROM perangkat GROUP BY status');
    foreach ($hasil as $row) {
        $per_status[$row['status']] = (int) $row['jumlah'];
    }
    $total = array_sum($per_status);

    // Jumlah perangkat per kategori.
    // LEFT JOIN dipakai agar kategori yang belum punya perangkat tetap muncul (jumlah 0).
    $per_kategori = $pdo->query('
        SELECT k.id, k.nama, k.deskripsi, COUNT(p.id) AS jumlah
        FROM kategori k
        LEFT JOIN perangkat p ON p.kategori_id = k.id
        GROUP BY k.id, k.nama, k.deskripsi
        ORDER BY k.nama
    ')->fetchAll();

    // 5 perangkat terbaru (id terbesar = paling baru ditambahkan)
    $terbaru = $pdo->query('
        SELECT p.id, p.nama, p.lokasi, p.status, k.nama AS kategori
        FROM perangkat p
        JOIN kategori k ON k.id = p.kategori_id
        ORDER BY p.id DESC
        LIMIT 5
    ')->fetchAll();
} catch (PDOException $e) {
    // Error paling umum di sini: tabel belum dibuat (install.php belum dijalankan)
    $belum_install = true;
}

$judul = 'Dashboard';
$menu_aktif = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<div class="breadcrumbs text-sm mb-4">
    <ul>
        <li>Dashboard</li>
    </ul>
</div>

<h1 class="text-2xl font-semibold mb-4">Dashboard</h1>

<?php if ($belum_install): ?>
    <div role="alert" class="alert alert-info">
        <span>Database belum disiapkan. Jalankan instalasi terlebih dahulu.</span>
        <a href="/install.php" class="btn btn-primary btn-sm">Jalankan install.php</a>
    </div>
<?php else: ?>

    <!-- Ringkasan status -->
    <div class="stats stats-vertical sm:stats-horizontal shadow-sm w-full mb-6">
        <div class="stat">
            <div class="stat-title">Total Perangkat</div>
            <div class="stat-value"><?= $total ?></div>
            <div class="stat-desc"><?= count($per_kategori) ?> kategori</div>
        </div>
        <div class="stat">
            <div class="stat-title">Aktif</div>
            <div class="stat-value text-success"><?= $per_status['aktif'] ?></div>
            <div class="stat-desc">Berfungsi normal</div>
        </div>
        <div class="stat">
            <div class="stat-title">Perbaikan</div>
            <div class="stat-value text-warning"><?= $per_status['perbaikan'] ?></div>
            <div class="stat-desc">Sedang diperbaiki</div>
        </div>
        <div class="stat">
            <div class="stat-title">Rusak</div>
            <div class="stat-value text-error"><?= $per_status['rusak'] ?></div>
            <div class="stat-desc">Perlu ditindaklanjuti</div>
        </div>
        <div class="stat">
            <div class="stat-title">Nonaktif</div>
            <div class="stat-value text-base-content/60"><?= $per_status['nonaktif'] ?></div>
            <div class="stat-desc">Tidak digunakan</div>
        </div>
    </div>

    <!-- Jumlah per kategori -->
    <h2 class="text-lg font-semibold mb-2">Perangkat per Kategori</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <?php foreach ($per_kategori as $kat): ?>
            <a href="/perangkat/index.php?kategori=<?= $kat['id'] ?>" class="card bg-base-100 shadow-sm hover:bg-base-300/40 transition">
                <div class="card-body p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold"><?= e($kat['nama']) ?></h3>
                        <span class="text-2xl font-bold"><?= $kat['jumlah'] ?></span>
                    </div>
                    <p class="text-sm text-base-content/70"><?= e($kat['deskripsi']) ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Perangkat terbaru -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-6">
            <div class="flex items-center justify-between">
                <h2 class="card-title text-lg">Perangkat Terbaru</h2>
                <a href="/perangkat/index.php" class="btn btn-ghost btn-sm">Lihat semua →</a>
            </div>
            <?php if (!$terbaru): ?>
                <p class="text-base-content/70">Belum ada perangkat.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr><th>Nama</th><th>Kategori</th><th>Lokasi</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($terbaru as $p): ?>
                                <tr>
                                    <td><a class="link link-hover" href="/perangkat/detail.php?id=<?= $p['id'] ?>"><?= e($p['nama']) ?></a></td>
                                    <td><?= e($p['kategori']) ?></td>
                                    <td><?= e($p['lokasi']) ?></td>
                                    <td><?= badge_status($p['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
