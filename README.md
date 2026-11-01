# Sistem Inventaris Perangkat Jaringan Sekolah

Aplikasi web sederhana untuk mencatat dan mengelola perangkat jaringan di laboratorium komputer sekolah: router, switch, access point, server, UPS, dan lainnya.

Project ini dibuat sebagai **bahan ajar untuk guru SMK jurusan Teknik Komputer dan Jaringan (TKJ)**. Lewat project ini siswa bisa belajar membangun aplikasi web dengan PHP dan MySQL, mulai dari koneksi database sampai operasi CRUD (Create, Read, Update, Delete).

## Fitur

- **Dashboard**: ringkasan jumlah perangkat per status dan per kategori
- **Daftar Perangkat**: tabel semua perangkat, dengan pencarian dan filter kategori/status
- **Tambah Perangkat**: form untuk menambah perangkat baru, lengkap dengan validasi
- **Detail Perangkat**: informasi lengkap satu perangkat
- **Edit Perangkat**: form untuk mengubah data perangkat
- **Hapus Perangkat**: konfirmasi sebelum data dihapus

## Teknologi

| Komponen | Keterangan |
|----------|------------|
| PHP 8.4 (PHP-FPM) | Bahasa pemrograman, tanpa framework |
| MySQL 8.0 | Database |
| Nginx 1.27 | Web server |
| Docker & Docker Compose | Menjalankan ketiga komponen di atas dalam container |
| daisyUI + Tailwind CSS | Tampilan, dimuat lewat CDN (perlu koneksi internet) |

Dengan Docker, **PHP, MySQL, dan Nginx tidak perlu diinstal satu per satu** di komputer. Cukup instal Docker, lalu semuanya berjalan otomatis.

---

## Persiapan

Komputer yang dipakai perlu:

1. **Docker Desktop** (Windows/macOS) atau **Docker Engine** (Linux)
   Unduh di https://www.docker.com/products/docker-desktop
2. **Koneksi internet**, untuk mengunduh image Docker saat pertama kali dijalankan dan untuk memuat tampilan (CDN).
3. **Port 8080 dan 3307** belum dipakai aplikasi lain.

Untuk memastikan Docker sudah terpasang, buka Terminal (macOS/Linux) atau PowerShell/Command Prompt (Windows), lalu ketik:

```bash
docker --version
docker compose version
```

Jika kedua perintah menampilkan nomor versi, Docker siap digunakan.

> **Catatan Windows:** pastikan Docker Desktop sudah dibuka dan statusnya *running* sebelum menjalankan perintah `docker`.

---

## Langkah 1: Menyalin Project

Pilih salah satu cara berikut.

**Cara A: Salin folder secara langsung**

Salin seluruh folder `website-using-docker` (misalnya lewat flashdisk atau folder bersama jaringan) ke komputer tujuan. Pastikan file-file berikut ikut tersalin:

```
website-using-docker/
├── docker-compose.yml
├── Dockerfile.php
├── nginx.conf
└── src/
```

**Cara B: Dari GitHub**

```bash
git clone https://github.com/adammb86/website-using-docker.git
```

Atau unduh dalam bentuk ZIP: buka https://github.com/adammb86/website-using-docker, klik tombol **Code** lalu **Download ZIP**, kemudian ekstrak.

Lalu masuk ke folder project:

```bash
cd website-using-docker
```

Semua perintah berikutnya dijalankan **dari dalam folder ini**.

---

## Langkah 2: Menjalankan Container

```bash
docker compose up -d --build
```

Penjelasan:
- `up`: membuat dan menjalankan container
- `-d`: berjalan di latar belakang (terminal tetap bisa dipakai)
- `--build`: membangun image PHP dari `Dockerfile.php`

Proses pertama kali bisa memakan waktu beberapa menit karena Docker mengunduh image. Setelah selesai, cek status container:

```bash
docker compose ps
```

Harus ada 3 container yang berjalan:

| Container | Fungsi |
|-----------|--------|
| `nginx_web` | Web server, bisa diakses di port 8080 |
| `php_app` | Menjalankan kode PHP |
| `mysql_db` | Database MySQL (status `healthy` artinya siap) |

---

## Langkah 3: Membuat Tabel Database

Buka browser dan akses:

```
http://localhost:8080/install.php
```

Halaman ini akan:
1. Membuat tabel `kategori` dan `perangkat`
2. Mengisi data contoh (8 kategori dan 12 perangkat)

Jika muncul pesan **"Instalasi selesai"**, database sudah siap. Halaman ini aman dibuka berulang kali karena data contoh tidak akan dobel.

> Ingin mengembalikan data ke kondisi awal setelah praktik? Buka lagi `install.php`, lalu klik **"Reset ke data contoh"**.

---

## Langkah 4: Membuka Aplikasi

```
http://localhost:8080
```

Aplikasi siap digunakan.

**Diakses dari komputer lain di jaringan lab:** ganti `localhost` dengan alamat IP komputer yang menjalankan Docker, misalnya `http://192.168.10.50:8080`. Cara melihat alamat IP: `ipconfig` (Windows) atau `ip a` / `ifconfig` (Linux/macOS). Pastikan firewall mengizinkan port 8080.

---

## Perintah Docker yang Sering Dipakai

| Perintah | Fungsi |
|----------|--------|
| `docker compose up -d` | Menjalankan container |
| `docker compose stop` | Menghentikan container (data tetap aman) |
| `docker compose down` | Menghentikan dan menghapus container (data database **tetap aman**) |
| `docker compose down -v` | Menghapus container **beserta data database** (hati-hati!) |
| `docker compose ps` | Melihat status container |
| `docker compose logs -f` | Melihat log semua container (tekan `Ctrl+C` untuk keluar) |
| `docker compose restart` | Menjalankan ulang container |

**Mengedit kode:** folder `src/` terhubung langsung ke dalam container, jadi perubahan file PHP **langsung terlihat** setelah halaman browser di-refresh. Container tidak perlu dijalankan ulang.

---

## Mengakses Database Secara Langsung

**Lewat terminal (MySQL di dalam container):**

```bash
docker exec -it mysql_db mysql -u app_user -papp_secret app_db
```

Contoh query yang bisa dicoba siswa:

```sql
SHOW TABLES;
SELECT * FROM kategori;
SELECT nama, ip_address, lokasi FROM perangkat WHERE status = 'aktif';
```

Ketik `exit` untuk keluar.

**Lewat aplikasi seperti DBeaver, HeidiSQL, atau MySQL Workbench:**

| Pengaturan | Nilai |
|------------|-------|
| Host | `localhost` |
| Port | `3307` |
| Database | `app_db` |
| Username | `app_user` |
| Password | `app_secret` |

> Dari dalam container (kode PHP), host database adalah `db` dengan port `3306`. Dari komputer Anda (luar container), gunakan `localhost` dengan port `3307`.

---

## Struktur Folder

```
website-using-docker/
├── docker-compose.yml          # Pengaturan 3 container (Nginx, PHP, MySQL)
├── Dockerfile.php              # Resep image PHP + ekstensi PDO MySQL
├── nginx.conf                  # Konfigurasi web server Nginx
└── src/                        # Kode aplikasi
    ├── config/
    │   └── database.php        # Koneksi database (PDO) + session
    ├── includes/
    │   ├── fungsi.php          # Fungsi bantu: flash message, validasi, badge
    │   ├── header.php          # Bagian atas halaman + navbar
    │   ├── footer.php          # Bagian bawah halaman
    │   └── form_perangkat.php  # Form yang dipakai bersama oleh tambah & edit
    ├── perangkat/
    │   ├── index.php           # Daftar perangkat + pencarian + filter
    │   ├── tambah.php          # Tambah perangkat (INSERT)
    │   ├── detail.php          # Detail perangkat (SELECT ... WHERE id)
    │   ├── edit.php            # Ubah perangkat (UPDATE)
    │   └── hapus.php           # Hapus perangkat (DELETE)
    ├── assets/
    │   └── style.css           # Gaya tambahan
    ├── index.php               # Dashboard
    └── install.php             # Membuat tabel + data contoh
```

### Struktur Database

```
kategori (1) ──────< perangkat (banyak)
  id                    id
  nama                  nama, merk, tipe
  deskripsi             ip_address, lokasi
                        status (aktif / rusak / perbaikan / nonaktif)
                        kategori_id  → foreign key ke kategori.id
                        tahun_pengadaan, keterangan
```

Satu kategori bisa memiliki banyak perangkat (relasi *one-to-many*).

---

## Saran Urutan Belajar untuk Siswa

1. `docker-compose.yml`: memahami cara 3 container saling terhubung
2. `config/database.php`: koneksi PHP ke MySQL dengan PDO
3. `install.php`: `CREATE TABLE`, foreign key, dan `INSERT`
4. `perangkat/index.php`: `SELECT`, `JOIN`, `LIKE` untuk pencarian, dan prepared statement
5. `perangkat/detail.php`: mengambil satu data berdasarkan `id`
6. `perangkat/tambah.php`: form POST, validasi, dan `INSERT`
7. `perangkat/edit.php`: `UPDATE`
8. `perangkat/hapus.php`: `DELETE` dan alasan memakai POST
9. `index.php`: `GROUP BY` dan `COUNT` untuk dashboard

Setiap file sudah diberi komentar penjelasan dalam bahasa Indonesia.

---

## Mengatasi Masalah

| Masalah | Solusi |
|---------|--------|
| `port is already allocated` saat menjalankan container | Port 8080 atau 3307 sudah dipakai aplikasi lain (misalnya XAMPP). Matikan aplikasi tersebut, atau ganti port di `docker-compose.yml`, misalnya `"8081:80"`, lalu akses `http://localhost:8081`. |
| Halaman menampilkan **"Koneksi database gagal"** | MySQL belum siap. Tunggu sekitar 30 detik setelah `docker compose up`, lalu refresh. Cek dengan `docker compose ps` bahwa `mysql_db` sudah berstatus `healthy`. |
| Dashboard menampilkan **"Database belum disiapkan"** | Buka `http://localhost:8080/install.php` terlebih dahulu. |
| Tampilan berantakan, tanpa warna atau gaya | Komputer tidak terhubung internet sehingga CSS dari CDN tidak termuat. Sambungkan ke internet lalu refresh. |
| `Cannot connect to the Docker daemon` | Docker Desktop belum dijalankan. Buka Docker Desktop, tunggu sampai statusnya *running*. |
| Ingin mengulang semuanya dari nol | Jalankan `docker compose down -v`, lalu `docker compose up -d --build`, lalu buka `install.php` lagi. |

---

## Catatan Keamanan

Project ini ditujukan untuk **pembelajaran di lingkungan lab**. Password database ditulis langsung di `docker-compose.yml` dan `config/database.php`, dan aplikasi belum memiliki fitur login. Jangan pasang project ini apa adanya di server yang bisa diakses dari internet.
