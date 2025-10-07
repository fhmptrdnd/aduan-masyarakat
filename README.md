# Forum Aduan Masyarakat (AMI)

Deskripsi singkat
- Aplikasi web sederhana untuk masyarakat mengirim aduan ke pemerintah kota.
- Dibangun dengan PHP + PDO, MySQL (Laragon), dan Tailwind CSS untuk tampilan.
- Tujuan: memungkinkan user mendaftar, login, membuat aduan, dan memberikan akses admin untuk manajemen.

Apa saja yang diimplementasikan
- Koneksi dan inisialisasi database otomatis
  - Jika database/tabel belum ada, aplikasi akan membuat database `forum_aduan` dan tabel `users` serta `aduan`.
- Autentikasi dasar
  - Registrasi user (role default: `user`) dengan hashing password.
  - Login dengan verifikasi password.
  - Session untuk menyimpan informasi user (id, nama, email, role).
- Role dasar: `user` dan `admin`
  - Redirect/menu berbeda untuk admin dan user.
- UI dasar
  - Halaman utama dengan slideshow hero.
  - Modal untuk login dan register (dibuka otomatis saat error/berhasil).
  - Tombol akses ke dashboard atau form buat aduan (sesuai role).
- Data awal
  - Dibuat akun admin default saat setup: email dan password tersedia di README/demo.

Struktur database (ringkas)
- users: id, nama, email (unique), telepon, password (hash), role (ENUM 'user','admin'), created_at
- aduan: id, user_id (FK -> users.id), judul, deskripsi, lokasi, kategori, status (ENUM), created_at

Akun default (untuk demo)
- Email: admin@email.com
- Password: admin123
- Role: admin

Cara menjalankan (di Windows / Laragon)
1. Pastikan Laragon/XAMPP berjalan (MySQL + PHP aktif).
2. Letakkan folder proyek di `c:\laragon\www\ami` (sudah sesuai).
3. Buka browser ke: http://localhost/ami/src/index.php
   - Aplikasi akan mencoba membuat database dan tabel jika belum ada.
4. Gunakan akun admin demo atau daftar akun baru lewat modal register.

Catatan keamanan & operasional singkat
- Password disimpan sebagai hash (password_hash).
- Input form dibersihkan dengan fungsi sederhana `bersihkan_input()` (trim, stripslashes, htmlspecialchars) — masih disarankan menambah validasi sisi server yang lebih ketat.
- Untuk produksi:
  - Jangan gunakan akun admin demo; ganti password default.
  - Aktifkan HTTPS.
  - Batasi akses error reporting dan gunakan prepared statements (sudah dipakai).
  - Pertimbangkan CSRF token untuk form dan validasi telepon/input lebih ketat.

File utama yang perlu dicek
- src/index.php — titik masuk (login/register, inisialisasi DB, UI hero + modal)
- (future) admin_dashboard.php, dashboard.php, buat_aduan.php — halaman fungsional untuk admin/user

Pengembangan selanjutnya (ide)
- Halaman CRUD aduan (buat, lihat, edit, ubah status).
- Upload foto lampiran untuk aduan.
- Notifikasi email atau pemberitahuan status aduan.
- Pagination, pencarian, dan filter kategori/lokasi.
- API JSON untuk integrasi mobile.

Lisensi
- Bebas dipakai dan dikembangkan (sesuaikan lisensi jika diperlukan).

Jika ingin, saya bisa:
- Tambahkan README dalam bahasa Inggris.
- Buat file .env atau konfigurasi koneksi terpisah.
- Tambahkan skrip SQL terpisah untuk migrasi."
