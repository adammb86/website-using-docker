<?php
/**
 * File: includes/fungsi.php
 * Fungsi: Kumpulan fungsi bantu (helper) yang dipakai di banyak halaman.
 *
 * Dengan menaruh fungsi di satu file, kita tidak perlu menulis kode
 * yang sama berulang-ulang di setiap halaman.
 */

// Daftar status perangkat yang diizinkan (sama dengan ENUM di database)
const DAFTAR_STATUS = ['aktif', 'rusak', 'perbaikan', 'nonaktif'];

/**
 * Mengamankan teks sebelum ditampilkan di HTML (mencegah serangan XSS).
 * Contoh: e('<script>') akan tampil sebagai teks biasa, bukan dijalankan.
 */
function e($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

/**
 * Menyimpan pesan flash ke session.
 * Pesan flash = pesan yang tampil SEKALI saja setelah redirect.
 * $tipe: 'success', 'error', atau 'info'
 */
function set_flash($tipe, $pesan)
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

/**
 * Mengambil pesan flash lalu menghapusnya dari session.
 * Mengembalikan null jika tidak ada pesan.
 */
function get_flash()
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Pindah ke halaman lain lalu hentikan script.
 */
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

/**
 * Mengembalikan class badge daisyUI sesuai status perangkat.
 */
function badge_status($status)
{
    $warna = [
        'aktif'     => 'badge-success',
        'rusak'     => 'badge-error',
        'perbaikan' => 'badge-warning',
        'nonaktif'  => 'badge-ghost',
    ];
    $class = $warna[$status] ?? 'badge-ghost';
    return '<span class="badge ' . $class . '">' . e(ucfirst($status)) . '</span>';
}

/**
 * Mengambil semua kategori (untuk dropdown dan filter).
 */
function ambil_kategori($pdo)
{
    return $pdo->query('SELECT id, nama FROM kategori ORDER BY nama')->fetchAll();
}

/**
 * Mengambil dan merapikan data form perangkat dari $_POST.
 * trim() membuang spasi di awal/akhir input.
 */
function ambil_input_perangkat()
{
    $kolom = ['nama', 'merk', 'tipe', 'ip_address', 'lokasi', 'status',
              'kategori_id', 'tahun_pengadaan', 'keterangan'];
    $data = [];
    foreach ($kolom as $k) {
        $data[$k] = trim($_POST[$k] ?? '');
    }
    return $data;
}

/**
 * Validasi data perangkat di sisi server.
 * Mengembalikan array berisi pesan error (kosong = data valid).
 */
function validasi_perangkat($pdo, $data)
{
    $error = [];

    // Nama wajib diisi dan maksimal 150 karakter
    if ($data['nama'] === '') {
        $error['nama'] = 'Nama perangkat wajib diisi.';
    } elseif (mb_strlen($data['nama']) > 150) {
        $error['nama'] = 'Nama perangkat maksimal 150 karakter.';
    }

    // Kategori wajib dipilih dan harus ada di tabel kategori
    if ($data['kategori_id'] === '' || !ctype_digit($data['kategori_id'])) {
        $error['kategori_id'] = 'Kategori wajib dipilih.';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM kategori WHERE id = ?');
        $stmt->execute([$data['kategori_id']]);
        if ($stmt->fetchColumn() == 0) {
            $error['kategori_id'] = 'Kategori tidak ditemukan.';
        }
    }

    // Status harus salah satu dari daftar yang diizinkan
    if (!in_array($data['status'], DAFTAR_STATUS, true)) {
        $error['status'] = 'Status tidak valid.';
    }

    // IP address boleh kosong, tapi jika diisi harus format IPv4/IPv6 yang benar
    if ($data['ip_address'] !== '' && !filter_var($data['ip_address'], FILTER_VALIDATE_IP)) {
        $error['ip_address'] = 'Format IP address tidak valid. Contoh: 192.168.1.1';
    }

    // Tahun boleh kosong, jika diisi harus angka 4 digit yang masuk akal
    if ($data['tahun_pengadaan'] !== '') {
        $tahun = (int) $data['tahun_pengadaan'];
        $tahun_ini = (int) date('Y');
        if (!ctype_digit($data['tahun_pengadaan']) || $tahun < 1990 || $tahun > $tahun_ini) {
            $error['tahun_pengadaan'] = "Tahun harus antara 1990 dan $tahun_ini.";
        }
    }

    // Batas panjang kolom teks lain
    foreach (['merk' => 100, 'tipe' => 100, 'lokasi' => 150] as $k => $maks) {
        if (mb_strlen($data[$k]) > $maks) {
            $error[$k] = ucfirst($k) . " maksimal $maks karakter.";
        }
    }

    return $error;
}

/**
 * Mengubah string kosong menjadi NULL agar tersimpan rapi di database.
 */
function kosong_jadi_null($nilai)
{
    return $nilai === '' ? null : $nilai;
}
