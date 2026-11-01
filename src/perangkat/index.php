<?php
/**
 * File: perangkat/index.php
 * Fungsi: Menampilkan daftar semua perangkat dalam bentuk tabel.
 * Fitur:
 *   - Pencarian (?cari=...) berdasarkan nama, merk, tipe, IP, atau lokasi
 *   - Filter kategori (?kategori=ID)
 *   - Filter status (?status=aktif/rusak/perbaikan/nonaktif)
 */
require_once __DIR__ . '/../config/database.php';

// Ambil parameter dari URL ($_GET). Jika tidak ada, isi dengan string kosong.
$cari     = trim($_GET['cari'] ?? '');
$kategori = $_GET['kategori'] ?? '';
$status   = $_GET['status'] ?? '';

$perangkat = [];
$daftar_kategori = [];
$error = null;

try {
    $daftar_kategori = ambil_kategori($pdo);

    // Susun query secara bertahap. WHERE 1=1 memudahkan penambahan "AND ..."
    $sql = '
        SELECT p.id, p.nama, p.merk, p.tipe, p.ip_address, p.lokasi, p.status,
               k.nama AS kategori
        FROM perangkat p
        JOIN kategori k ON k.id = p.kategori_id
        WHERE 1=1
    ';
    $params = [];

    if ($cari !== '') {
        // Tanda ? diisi nilai di execute(), BUKAN ditempel langsung ke SQL (anti SQL injection)
        $sql .= ' AND (p.nama LIKE ? OR p.merk LIKE ? OR p.tipe LIKE ? OR p.ip_address LIKE ? OR p.lokasi LIKE ?)';
        $kata = '%' . $cari . '%';
        array_push($params, $kata, $kata, $kata, $kata, $kata);
    }
    if ($kategori !== '' && ctype_digit($kategori)) {
        $sql .= ' AND p.kategori_id = ?';
        $params[] = $kategori;
    }
    if (in_array($status, DAFTAR_STATUS, true)) {
        $sql .= ' AND p.status = ?';
        $params[] = $status;
    }

    $sql .= ' ORDER BY k.nama, p.nama';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $perangkat = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Gagal mengambil data. Sudahkah menjalankan install.php? (' . $e->getMessage() . ')';
}

$ada_filter = ($cari !== '' || $kategori !== '' || $status !== '');

$judul = 'Daftar Perangkat';
$menu_aktif = 'daftar';
require __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumbs text-sm mb-4">
    <ul>
        <li><a href="/index.php">Dashboard</a></li>
        <li>Daftar Perangkat</li>
    </ul>
</div>

<div class="flex flex-wrap items-center justify-between gap-4 mb-4">
    <h1 class="text-2xl font-semibold">Daftar Perangkat</h1>
    <a href="/perangkat/tambah.php" class="btn btn-primary">+ Tambah Perangkat</a>
</div>

<?php if ($error): ?>
    <div role="alert" class="alert alert-error mb-4"><span><?= e($error) ?></span></div>
<?php endif; ?>

<!-- Form pencarian & filter (method GET agar hasilnya bisa di-bookmark) -->
<form method="get" class="card bg-base-100 shadow-sm mb-4">
    <div class="card-body p-4 grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="text" name="cari" value="<?= e($cari) ?>" placeholder="Cari nama, merk, IP, lokasi..." class="input input-bordered w-full md:col-span-2">

        <select name="kategori" class="select select-bordered w-full">
            <option value="">Semua kategori</option>
            <?php foreach ($daftar_kategori as $k): ?>
                <option value="<?= $k['id'] ?>" <?= (string) $k['id'] === $kategori ? 'selected' : '' ?>><?= e($k['nama']) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="status" class="select select-bordered w-full">
            <option value="">Semua status</option>
            <?php foreach (DAFTAR_STATUS as $s): ?>
                <option value="<?= $s ?>" <?= $s === $status ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="md:col-span-4 flex gap-2 justify-end">
            <?php if ($ada_filter): ?>
                <a href="/perangkat/index.php" class="btn btn-ghost">Reset</a>
            <?php endif; ?>
            <button type="submit" class="btn btn-secondary">Cari</button>
        </div>
    </div>
</form>

<div class="card bg-base-100 shadow-sm">
    <div class="card-body p-4">
        <p class="text-sm text-base-content/70">Menampilkan <?= count($perangkat) ?> perangkat</p>

        <?php if (!$perangkat): ?>
            <p class="py-6 text-center text-base-content/70">
                <?= $ada_filter ? 'Tidak ada perangkat yang cocok dengan pencarian.' : 'Belum ada data perangkat.' ?>
            </p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Perangkat</th>
                            <th>Kategori</th>
                            <th>Merk / Tipe</th>
                            <th>IP Address</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($perangkat as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="font-medium"><?= e($p['nama']) ?></td>
                                <td><?= e($p['kategori']) ?></td>
                                <td><?= e(trim($p['merk'] . ' ' . $p['tipe'])) ?: '-' ?></td>
                                <td class="font-ip"><?= e($p['ip_address']) ?: '-' ?></td>
                                <td><?= e($p['lokasi']) ?: '-' ?></td>
                                <td><?= badge_status($p['status']) ?></td>
                                <td class="text-right whitespace-nowrap">
                                    <a href="/perangkat/detail.php?id=<?= $p['id'] ?>" class="btn btn-ghost btn-xs">Detail</a>
                                    <a href="/perangkat/edit.php?id=<?= $p['id'] ?>" class="btn btn-ghost btn-xs">Edit</a>
                                    <a href="/perangkat/hapus.php?id=<?= $p['id'] ?>" class="btn btn-ghost btn-xs text-error">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
