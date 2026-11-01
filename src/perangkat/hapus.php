<?php
/**
 * File: perangkat/hapus.php
 * Fungsi: Konfirmasi dan menghapus satu perangkat (DELETE).
 *
 * Alur:
 *   1. GET  (?id=...) -> tampilkan modal konfirmasi "Yakin ingin menghapus?"
 *   2. POST           -> hapus data, lalu redirect ke daftar perangkat
 *
 * Kenapa hapus memakai POST, bukan link GET biasa?
 * Karena link GET bisa "terklik" tanpa sengaja (misalnya oleh browser yang
 * memuat ulang halaman). Operasi yang mengubah data sebaiknya lewat POST.
 */
require_once __DIR__ . '/../config/database.php';

$id = $_GET['id'] ?? '';
if (!ctype_digit($id)) {
    set_flash('error', 'ID perangkat tidak valid.');
    redirect('/perangkat/index.php');
}

try {
    // Ambil data perangkat yang akan dihapus
    $stmt = $pdo->prepare('
        SELECT p.id, p.nama, p.ip_address, p.lokasi, k.nama AS kategori
        FROM perangkat p
        JOIN kategori k ON k.id = p.kategori_id
        WHERE p.id = ?
    ');
    $stmt->execute([$id]);
    $perangkat = $stmt->fetch();

    if (!$perangkat) {
        set_flash('error', 'Perangkat tidak ditemukan.');
        redirect('/perangkat/index.php');
    }

    // Jika tombol "Ya, Hapus" ditekan
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare('DELETE FROM perangkat WHERE id = ?');
        $stmt->execute([$id]);

        set_flash('success', 'Perangkat "' . $perangkat['nama'] . '" berhasil dihapus.');
        redirect('/perangkat/index.php');
    }
} catch (PDOException $e) {
    set_flash('error', 'Gagal menghapus perangkat: ' . $e->getMessage());
    redirect('/perangkat/index.php');
}

$judul = 'Hapus Perangkat';
$menu_aktif = 'daftar';
require __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumbs text-sm mb-4">
    <ul>
        <li><a href="/index.php">Dashboard</a></li>
        <li><a href="/perangkat/index.php">Daftar Perangkat</a></li>
        <li>Hapus</li>
    </ul>
</div>

<!-- Modal konfirmasi: class "modal-open" membuat modal langsung tampil -->
<div class="modal modal-open" role="dialog">
    <div class="modal-box">
        <h3 class="text-lg font-semibold">Hapus Perangkat?</h3>
        <p class="py-4">Data berikut akan dihapus secara permanen dan tidak bisa dikembalikan:</p>

        <div class="bg-base-200 rounded p-4 text-sm space-y-1">
            <div><span class="text-base-content/70">Nama:</span> <strong><?= e($perangkat['nama']) ?></strong></div>
            <div><span class="text-base-content/70">Kategori:</span> <?= e($perangkat['kategori']) ?></div>
            <div><span class="text-base-content/70">IP Address:</span> <span class="font-ip"><?= e($perangkat['ip_address']) ?: '-' ?></span></div>
            <div><span class="text-base-content/70">Lokasi:</span> <?= e($perangkat['lokasi']) ?: '-' ?></div>
        </div>

        <form method="post" class="modal-action">
            <a href="/perangkat/index.php" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-error">Ya, Hapus</button>
        </form>
    </div>
    <!-- Klik di luar kotak modal = batal -->
    <a href="/perangkat/index.php" class="modal-backdrop">Tutup</a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
