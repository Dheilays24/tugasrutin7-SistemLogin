# DelaAuth — Sistem Autentikasi PHP Native

Tugas Rutin 7 — Login & Register menggunakan PHP native (tanpa framework) dengan
penyimpanan data di file `users.json`.

## Cara menjalankan

1. Pastikan PHP terpasang (XAMPP/Laragon di Windows, atau `php` CLI di
   Mac/Linux).
2. Buka terminal di folder project ini, lalu jalankan:
   ```
   php -S localhost:8000
   ```
3. Buka browser ke `http://localhost:8000`.

   Kalau memakai XAMPP: taruh folder ini di `htdocs/`, lalu buka
   `http://localhost/TugasWeb-Pertemuan7-LoginRegister/`.

## Struktur file

| File            | Fungsi                                              |
|-----------------|------------------------------------------------------|
| `functions.php` | Semua fungsi bantu (JSON storage, session, sanitasi, remember me) |
| `index.php`     | Entry point, redirect ke dashboard/login             |
| `register.php`  | Registrasi user baru                                  |
| `login.php`     | Login + Remember Me                                   |
| `dashboard.php` | Halaman utama setelah login (dilindungi session)      |
| `profile.php`   | Edit nama, email, dan ganti password (opsional)       |
| `logout.php`    | Hapus session & cookie remember-me                     |
| `users.json`    | "Database" penyimpanan user (format JSON)              |
| `css/style.css` | Styling, termasuk dark/light mode                      |
| `js/script.js`  | Show/hide password, toggle tema, toast notification    |

## Fitur wajib (sesuai requirement Tugas Rutin 7)

- Register dengan validasi nama, email, dan password (minimal 6 karakter)
- Password di-hash dengan `password_hash()` — tidak pernah disimpan plain text
- Data user disimpan di `users.json`
- Cek email duplikat saat register
- Login dengan `password_verify()` + PHP session
- Dashboard yang hanya bisa diakses jika sudah login (proteksi via `requireLogin()`)
- Logout yang menghancurkan session
- Semua input disanitasi dengan `htmlspecialchars()` untuk mencegah XSS
- Pesan error/sukses ditampilkan dengan jelas di setiap form

## Fitur bonus yang ditambahkan

- ⭐ **Remember Me** — token acak disimpan (di-hash) di `users.json` dan
  cookie di browser, supaya user tidak perlu login ulang selama 30 hari
- ⭐ **Edit Profile** — ubah nama, email, dan ganti password (opsional)
- ⭐ **Show/Hide Password** — tombol mata 👁 di setiap input password
- ⭐ **Dark/Light Mode** — tersimpan di `localStorage` browser
- ⭐ **Toast Notification** — notifikasi kecil saat aksi berhasil
- ⭐ **Dashboard modern** — data profil, status akun, dan tanggal bergabung
  diambil langsung dari `users.json` + session, bukan teks statis
- ⭐ **Responsive design** — sidebar otomatis menyesuaikan di layar kecil

## Catatan keamanan yang diterapkan

- Password di-hash dengan algoritma `bcrypt` (`PASSWORD_DEFAULT`)
- Token remember-me disimpan dalam bentuk hash (SHA-256), bukan token asli
- Cookie remember-me diset `httponly` agar tidak bisa diakses lewat JavaScript
- Semua output ke HTML melewati `htmlspecialchars()` untuk mencegah XSS
- `session_regenerate_id(true)` dipanggil setiap kali login berhasil, untuk
  mencegah session fixation

## Status pengujian

Semua alur berikut sudah diuji secara fungsional (bukan cuma cek syntax):
register berhasil, validasi form (nama/email/password kosong atau format
salah), penolakan email duplikat, login gagal saat password salah, login
berhasil + session terisi, dashboard menolak akses tanpa login, edit profile
tersimpan dengan benar, input berbahaya (`<script>`) otomatis tersanitasi,
dan logout membersihkan session + cookie remember-me.
