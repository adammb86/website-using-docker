<?php
/**
 * File: includes/header.php
 * Fungsi: Bagian atas halaman (head HTML + navbar + pesan flash).
 *
 * Sebelum meng-include file ini, halaman perlu mengisi:
 *   $judul       -> judul halaman, contoh: 'Dashboard'
 *   $menu_aktif  -> menu yang disorot: 'dashboard', 'daftar', atau 'tambah'
 */
$judul      = $judul ?? 'Halaman';
$menu_aktif = $menu_aktif ?? '';

// Daftar menu navigasi: kunci => [label, alamat]
$menu = [
    'dashboard' => ['Dashboard', '/index.php'],
    'daftar'    => ['Daftar Perangkat', '/perangkat/index.php'],
    'tambah'    => ['Tambah Perangkat', '/perangkat/tambah.php'],
];

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id" data-theme="corporate">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($judul) ?> - Sistem Inventaris Jaringan</title>
    <!-- CDN daisyUI + Tailwind -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.24/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="/assets/style.css" rel="stylesheet">
</head>
<body class="min-h-screen flex flex-col bg-base-200">

    <!-- Navbar -->
    <div class="navbar bg-base-100 shadow-sm">
        <div class="max-w-7xl mx-auto w-full flex flex-wrap items-center gap-2 px-4">
            <a href="/index.php" class="text-lg font-semibold mr-auto">🖧 Sistem Inventaris Jaringan</a>
            <ul class="menu menu-horizontal p-0 gap-1">
                <?php foreach ($menu as $kunci => [$label, $url]): ?>
                    <li>
                        <a href="<?= $url ?>" class="<?= $menu_aktif === $kunci ? 'active' : '' ?>"><?= $label ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Konten utama -->
    <main class="flex-1 w-full max-w-7xl mx-auto p-4 md:p-6">

        <?php if ($flash): ?>
            <!-- Pesan flash (tampil sekali setelah redirect) -->
            <div role="alert" class="alert alert-<?= e($flash['tipe']) ?> mb-4">
                <span><?= e($flash['pesan']) ?></span>
            </div>
        <?php endif; ?>
