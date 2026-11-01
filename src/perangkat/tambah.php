<?php
/**
 * File: perangkat/tambah.php
 * Fungsi: Menampilkan form tambah perangkat dan menyimpan data baru (INSERT).
 *
 * Alur:
 *   1. Saat dibuka biasa (GET)  -> tampilkan form kosong
 *   2. Saat form dikirim (POST) -> validasi -> simpan -> redirect ke daftar
 *   3. Jika validasi gagal      -> tampilkan form lagi beserta pesan error
 */
require_once __DIR__ . '/../config/database.php';

// Nilai awal form (kosong)
$data = [
    'nama' => '', 'merk' => '', 'tipe' => '', 'ip_address' => '', 'lokasi' => '',
    'status' => 'aktif', 'kategori_id' => $_GET['kategori'] ?? '',
    'tahun_pengadaan' => '', 'keterangan' => '',
];
$error = [];

try {
    $daftar_kategori = ambil_kategori($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data  = ambil_input_perangkat();
        $error = validasi_perangkat($pdo, $data);

        if (!$error) {
            $stmt = $pdo->prepare('
                INSERT INTO perangkat
                    (nama, merk, tipe, ip_address, lokasi, status, kategori_id, tahun_pengadaan, keterangan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            ]);

            set_flash('success', 'Perangkat "' . $data['nama'] . '" berhasil ditambahkan.');
            redirect('/perangkat/index.php');
        }
    }
} catch (PDOException $e) {
    $daftar_kategori = $daftar_kategori ?? [];
    $error['umum'] = 'Terjadi kesalahan database: ' . $e->getMessage();
}

$judul = 'Tambah Perangkat';
$menu_aktif = 'tambah';
require __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumbs text-sm mb-4">
    <ul>
        <li><a href="/index.php">Dashboard</a></li>
        <li><a href="/perangkat/index.php">Daftar Perangkat</a></li>
        <li>Tambah</li>
    </ul>
</div>

<div class="card bg-base-100 shadow-sm max-w-3xl">
    <form method="post" class="card-body p-6">
        <h1 class="card-title text-2xl mb-2">Tambah Perangkat</h1>

        <?php if ($error): ?>
            <div role="alert" class="alert alert-error mb-2">
                <span><?= e($error['umum'] ?? 'Data belum valid. Periksa kembali isian yang ditandai merah.') ?></span>
            </div>
        <?php endif; ?>

        <?php require __DIR__ . '/../includes/form_perangkat.php'; ?>

        <div class="card-actions justify-end mt-4">
            <a href="/perangkat/index.php" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
