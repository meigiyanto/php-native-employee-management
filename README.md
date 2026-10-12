# Employee Management System

Aplikasi web untuk mengelola data karyawan, departemen, dan jabatan menggunakan **PHP Native** dan **MariaDB**.

Proyek ini dikembangkan sebagai *portfolio web development* dengan fokus pada pengelolaan data, autentikasi pengguna, keamanan dasar, dan antarmuka yang responsif.

## Fitur

### Dashboard

- Ringkasan jumlah karyawan.
- Statistik karyawan aktif.
- Jumlah departemen dan jabatan.
- Daftar lima karyawan terbaru.

### Manajemen Karyawan

- Menambahkan, melihat, mengubah, dan menghapus data karyawan.
- Pencarian berdasarkan informasi karyawan.
- Filter berdasarkan departemen dan jabatan.
- Pagination tabel karyawan.
- Penghapusan beberapa karyawan sekaligus.
- Ekspor data karyawan ke CSV.
- Impor data karyawan dari CSV dan unduh template impor.

### Manajemen Organisasi

- Pengelolaan departemen.
- Pengelolaan jabatan.
- Pagination daftar jabatan.
- Validasi hubungan antara departemen, jabatan, dan karyawan.

### Autentikasi dan Keamanan

- Login dan logout.
- Password disimpan menggunakan mekanisme hashing PHP.
- Pembatasan akses halaman untuk pengguna yang belum login.
- Pengecekan role pengguna ("admin" dan "hr").
- Perlindungan *CSRF* pada formulir yang relevan.
- Penggunaan PDO untuk akses database.
- Validasi input dan *escaping* output HTML.

## Teknologi

- **PHP Native**
- **MariaDB**
- **PDO**
- **HTML5**
- **CSS3**
- **JavaScript**
- **Bootstrap 5**
- **Bootstrap Icons**

## Struktur Proyek

```
.
├── app/
│   ├── config/
│   ├── controllers/
│   ├── helpers/
│   ├── middleware/
│   ├── models/
│   └── views/
├── database/
│   └── schema.sql
├── public/
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   └── index.php
├── routes/
│   └── web.php
├── .env
└── README.md
```

## Persyaratan

- **PHP 8.5** atau versi yang kompatibel dengan kode proyek.
- **MariaDB**.
- Ekstensi **PHP PDO** dan driver **PDO MySQL**.
- Browser modern.
- Koneksi internet untuk memuat aset **Bootstrap** dari CDN.

## Instalasi

1. Clone repositori

```
git clone https://github.com/meigiyanto/php-native-employee-management.git
cd php-native-employee-management
```

2. Siapkan database

Buat database menggunakan file **"database/schema.sql"**.

File tersebut membuat database **"employee_management"** beserta tabel departemen, jabatan, karyawan, dan pengguna.

Database dapat diimpor melalui **phpMyAdmin** atau klien **MariaDB**.

3. Konfigurasi koneksi database

Buat file **".env"** di direktori utama proyek, sejajar dengan folder **"app"**, **"database"**, dan **"public"**.

Contoh konfigurasi:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=employee_management
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan nilai tersebut dengan konfigurasi MariaDB yang digunakan.

Jangan mengunggah file **".env"** yang berisi kredensial asli ke repositori publik.

4. Jalankan aplikasi

Jalankan server pengembangan PHP dari direktori utama proyek:

```
php -S 127.0.0.1:8000 -t public
```

Buka alamat berikut di browser:

```
http://127.0.0.1:8000
```

5. Login

Gunakan akun yang sudah dibuat di tabel **"users"** untuk masuk ke aplikasi.

Pastikan akun memiliki role yang valid, yaitu **"admin"** atau **"hr"**.

## Struktur Data

|Tabel      | Fungsi|
|-----------|----------------------------------------|
|users      | Menyimpan akun pengguna dan role       |
|employees  | Menyimpan data karyawan                |
|departments| Menyimpan departemen                   |
|positions  | Menyimpan jabatan dan relasi departemen|

Kode karyawan menggunakan format seperti **"EMP-0001"**. Status pekerjaan yang tersedia meliputi **"active"**, **"inactive"**, **"on_leave"**, dan **"resigned**.

## Catatan Pengembangan

- Aplikasi menggunakan pola controller dan model sederhana dengan PHP Native.
- Akses database dilakukan menggunakan PDO.
- Antarmuka menggunakan Bootstrap 5.
- Pengelolaan akun pengguna melalui halaman administrasi khusus belum tersedia.

## Pengembangan Selanjutnya

- Halaman administrasi pengguna.
- Pengaturan hak akses berdasarkan role.
- Audit log aktivitas pengguna.
- Profil karyawan yang lebih lengkap.
- Grafik dan laporan statistik.
- Pengujian otomatis.

## Lisensi

Tentukan lisensi proyek sebelum mendistribusikan atau mengizinkan penggunaan ulang kode secara publik.
