<?php
/**
 * File: perangkat/edit.php
 * Fungsi: Menampilkan form edit perangkat dan menyimpan perubahan (UPDATE).
 *
 * Alur:
 *   1. Ambil id dari URL (?id=...), lalu cari datanya di database
 *   2. GET  -> tampilkan form berisi data lama
 *   3. POST -> validasi -> update -> redirect ke halaman detail
 */
require_once __DIR__ . '/../config/database.php';

// Pastikan id berupa angka
$id = $_GET['id'] ?? '';
if (!ctype_digit($id)) {
    set_flash('error', 'ID perangkat tidak valid.');
    redirect('/perangkat/index.php');
}

$error = [];

try {
    // Ambil data lama perangkat
    $stmt = $pdo->prepare('SELECT * FROM perangkat WHERE id = ?');
    $stmt->execute([$id]);
    $data = $stmt->fetch();

    if (!$data) {
        set_flash('error', 'Perangkat tidak ditemukan.');
        redirect('/perangkat/index.php');
    }

    $nama_lama = $data['nama'];
    $daftar_kategori = ambil_kategori($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data  = ambil_input_perangkat();
        $error = validasi_perangkat($pdo, $data);

        if (!$error) {
            $stmt = $pdo->prepare('
                UPDATE perangkat SET
                    nama = ?, merk = ?, tipe = ?, ip_address = ?, lokasi = ?,
                    status = ?, kategori_id = ?, tahun_pengadaan = ?, keterangan = ?
                WHERE id = ?
            ');
            $stmt->execute([
                $data['nama'],
                kosong_jadi_null($data['merk']),
                kosong_jadi_null($data['tipe']),
                kosong_jadi_null($data['ip_address']),
                kosong_jadi_null($data['lokasi']),
                $data['status'],
                $data['kategori_id'],
                kosong_jadi_null($data['tahun_pengadaan']),
                kosong_jadi_null($data['keterangan']),
                $id,
            ]);

            set_flash('success', 'Perubahan data "' . $data['nama'] . '" berhasil disimpan.');
            redirect('/perangkat/detail.php?id=' . $id);
        }
    }
} catch (PDOException $e) {
    set_flash('error', 'Terjadi kesalahan database: ' . $e->getMessage());
    redirect('/perangkat/index.php');
}

$judul = 'Edit Perangkat';
$menu_aktif = 'daftar';
require __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumbs text-sm mb-4">
    <ul>
        <li><a href="/index.php">Dashboard</a></li>
        <li><a href="/perangkat/index.php">Daftar Perangkat</a></li>
        <li><a href="/perangkat/detail.php?id=<?= $id ?>"><?= e($nama_lama) ?></a></li>
        <li>Edit</li>
    </ul>
</div>

<div class="card bg-base-100 shadow-sm max-w-3xl">
    <form method="post" class="card-body p-6">
        <h1 class="card-title text-2xl mb-2">Edit Perangkat</h1>

        <?php if ($error): ?>
            <div role="alert" class="alert alert-error mb-2">
                <span>Data belum valid. Periksa kembali isian yang ditandai merah.</span>
            </div>
        <?php endif; ?>

        <?php require __DIR__ . '/../includes/form_perangkat.php'; ?>

        <div class="card-actions justify-end mt-4">
            <a href="/perangkat/detail.php?id=<?= $id ?>" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
