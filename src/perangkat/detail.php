<?php
/**
 * File: perangkat/detail.php
 * Fungsi: Menampilkan detail lengkap satu perangkat berdasarkan id (?id=...).
 */
require_once __DIR__ . '/../config/database.php';

$id = $_GET['id'] ?? '';
if (!ctype_digit($id)) {
    set_flash('error', 'ID perangkat tidak valid.');
    redirect('/perangkat/index.php');
}

try {
    // JOIN dengan tabel kategori untuk mendapatkan nama kategorinya
    $stmt = $pdo->prepare('
        SELECT p.*, k.nama AS kategori
        FROM perangkat p
        JOIN kategori k ON k.id = p.kategori_id
        WHERE p.id = ?
    ');
    $stmt->execute([$id]);
    $p = $stmt->fetch();
} catch (PDOException $e) {
    set_flash('error', 'Gagal mengambil data: ' . $e->getMessage());
    redirect('/perangkat/index.php');
}

if (!$p) {
    set_flash('error', 'Perangkat tidak ditemukan.');
    redirect('/perangkat/index.php');
}

// Data yang ditampilkan: label => nilai (tanda "-" jika kosong)
$rincian = [
    'Kategori'        => e($p['kategori']),
    'Merk'            => e($p['merk']) ?: '-',
    'Tipe / Model'    => e($p['tipe']) ?: '-',
    'IP Address'      => $p['ip_address'] ? '<span class="font-ip">' . e($p['ip_address']) . '</span>' : '-',
    'Lokasi'          => e($p['lokasi']) ?: '-',
    'Status'          => badge_status($p['status']),
    'Tahun Pengadaan' => e($p['tahun_pengadaan']) ?: '-',
];

$judul = 'Detail ' . $p['nama'];
$menu_aktif = 'daftar';
require __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumbs text-sm mb-4">
    <ul>
        <li><a href="/index.php">Dashboard</a></li>
        <li><a href="/perangkat/index.php">Daftar Perangkat</a></li>
        <li><?= e($p['nama']) ?></li>
    </ul>
</div>

<div class="card bg-base-100 shadow-sm max-w-3xl">
    <div class="card-body p-6">
        <h1 class="card-title text-2xl"><?= e($p['nama']) ?></h1>

        <div class="overflow-x-auto">
            <table class="table">
                <tbody>
                    <?php foreach ($rincian as $label => $nilai): ?>
                        <tr>
                            <th class="w-48 font-medium text-base-content/70"><?= $label ?></th>
                            <td><?= $nilai /* sudah di-escape di atas */ ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th class="w-48 font-medium text-base-content/70 align-top">Keterangan</th>
                        <td class="whitespace-pre-line"><?= e($p['keterangan']) ?: '-' ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card-actions justify-between mt-4">
            <a href="/perangkat/index.php" class="btn btn-ghost">← Kembali</a>
            <div class="flex gap-2">
                <a href="/perangkat/hapus.php?id=<?= $p['id'] ?>" class="btn btn-error btn-outline">Hapus</a>
                <a href="/perangkat/edit.php?id=<?= $p['id'] ?>" class="btn btn-primary">Edit</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
