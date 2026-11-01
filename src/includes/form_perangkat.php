<?php
/**
 * File: includes/form_perangkat.php
 * Fungsi: Isi form perangkat yang dipakai bersama oleh tambah.php dan edit.php.
 *
 * Variabel yang harus sudah ada sebelum file ini di-include:
 *   $data            -> array nilai form (kosong saat tambah, berisi data lama saat edit)
 *   $error           -> array pesan error validasi, kunci = nama kolom
 *   $daftar_kategori -> daftar kategori untuk dropdown
 */

/**
 * Menampilkan pesan error di bawah input, jika kolom tersebut punya error.
 */
function pesan_error($error, $kolom)
{
    if (isset($error[$kolom])) {
        echo '<span class="text-error text-sm mt-1">' . e($error[$kolom]) . '</span>';
    }
}

/**
 * Menambahkan class merah (input-error) pada input yang error.
 */
function class_error($error, $kolom, $prefix)
{
    return isset($error[$kolom]) ? " $prefix-error" : '';
}
?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    <label class="form-control w-full md:col-span-2">
        <div class="label"><span class="label-text">Nama Perangkat <span class="text-error">*</span></span></div>
        <input type="text" name="nama" value="<?= e($data['nama']) ?>" maxlength="150" required
               placeholder="Contoh: Router Utama Lab A"
               class="input input-bordered w-full<?= class_error($error, 'nama', 'input') ?>">
        <?php pesan_error($error, 'nama'); ?>
    </label>

    <label class="form-control w-full">
        <div class="label"><span class="label-text">Kategori <span class="text-error">*</span></span></div>
        <select name="kategori_id" required class="select select-bordered w-full<?= class_error($error, 'kategori_id', 'select') ?>">
            <option value="">-- Pilih kategori --</option>
            <?php foreach ($daftar_kategori as $k): ?>
                <option value="<?= $k['id'] ?>" <?= (string) $k['id'] === (string) $data['kategori_id'] ? 'selected' : '' ?>><?= e($k['nama']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php pesan_error($error, 'kategori_id'); ?>
    </label>

    <label class="form-control w-full">
        <div class="label"><span class="label-text">Status <span class="text-error">*</span></span></div>
        <select name="status" class="select select-bordered w-full<?= class_error($error, 'status', 'select') ?>">
            <?php foreach (DAFTAR_STATUS as $s): ?>
                <option value="<?= $s ?>" <?= $s === $data['status'] ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
        <?php pesan_error($error, 'status'); ?>
    </label>

    <label class="form-control w-full">
        <div class="label"><span class="label-text">Merk</span></div>
        <input type="text" name="merk" value="<?= e($data['merk']) ?>" maxlength="100"
               placeholder="Contoh: Cisco, MikroTik, TP-Link"
               class="input input-bordered w-full<?= class_error($error, 'merk', 'input') ?>">
        <?php pesan_error($error, 'merk'); ?>
    </label>

    <label class="form-control w-full">
        <div class="label"><span class="label-text">Tipe / Model</span></div>
        <input type="text" name="tipe" value="<?= e($data['tipe']) ?>" maxlength="100"
               placeholder="Contoh: RB951Ui-2HnD"
               class="input input-bordered w-full<?= class_error($error, 'tipe', 'input') ?>">
        <?php pesan_error($error, 'tipe'); ?>
    </label>

    <label class="form-control w-full">
        <div class="label"><span class="label-text">IP Address</span></div>
        <input type="text" name="ip_address" value="<?= e($data['ip_address']) ?>" maxlength="45"
               placeholder="Contoh: 192.168.1.1 (kosongkan jika tidak ada)"
               class="input input-bordered w-full font-ip<?= class_error($error, 'ip_address', 'input') ?>">
        <?php pesan_error($error, 'ip_address'); ?>
    </label>

    <label class="form-control w-full">
        <div class="label"><span class="label-text">Lokasi</span></div>
        <input type="text" name="lokasi" value="<?= e($data['lokasi']) ?>" maxlength="150"
               placeholder="Contoh: Lab A, Ruang Server"
               class="input input-bordered w-full<?= class_error($error, 'lokasi', 'input') ?>">
        <?php pesan_error($error, 'lokasi'); ?>
    </label>

    <label class="form-control w-full">
        <div class="label"><span class="label-text">Tahun Pengadaan</span></div>
        <input type="number" name="tahun_pengadaan" value="<?= e($data['tahun_pengadaan']) ?>"
               min="1990" max="<?= date('Y') ?>" placeholder="Contoh: <?= date('Y') ?>"
               class="input input-bordered w-full<?= class_error($error, 'tahun_pengadaan', 'input') ?>">
        <?php pesan_error($error, 'tahun_pengadaan'); ?>
    </label>

    <label class="form-control w-full md:col-span-2">
        <div class="label"><span class="label-text">Keterangan</span></div>
        <textarea name="keterangan" rows="3" placeholder="Catatan tambahan (opsional)"
                  class="textarea textarea-bordered w-full"><?= e($data['keterangan']) ?></textarea>
    </label>

</div>
