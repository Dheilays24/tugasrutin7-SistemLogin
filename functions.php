<?php
/**
 * functions.php
 * Kumpulan fungsi bantu untuk DelaAuth
 * - Penyimpanan data user di users.json
 * - Session management
 * - Sanitasi input
 * - Remember Me (cookie)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('USERS_FILE', __DIR__ . '/users.json');

/**
 * Ambil semua user dari users.json
 * @return array
 */
function loadUsers(): array
{
    if (!file_exists(USERS_FILE)) {
        file_put_contents(USERS_FILE, json_encode([], JSON_PRETTY_PRINT));
    }

    $raw = file_get_contents(USERS_FILE);
    $users = json_decode($raw, true);

    return is_array($users) ? $users : [];
}

/**
 * Simpan array user ke users.json
 */
function saveUsers(array $users): bool
{
    $json = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(USERS_FILE, $json) !== false;
}

/**
 * Cari user berdasarkan email (case-insensitive)
 */
function findUserByEmail(string $email): ?array
{
    $email = strtolower(trim($email));
    foreach (loadUsers() as $user) {
        if (strtolower($user['email']) === $email) {
            return $user;
        }
    }
    return null;
}

/**
 * Cari user berdasarkan id
 */
function findUserById(string $id): ?array
{
    foreach (loadUsers() as $user) {
        if ($user['id'] === $id) {
            return $user;
        }
    }
    return null;
}

/**
 * Update satu user (berdasarkan id) di dalam users.json
 */
function updateUser(string $id, array $newData): bool
{
    $users = loadUsers();
    $found = false;

    foreach ($users as $index => $user) {
        if ($user['id'] === $id) {
            $users[$index] = array_merge($user, $newData);
            $found = true;
            break;
        }
    }

    if ($found) {
        return saveUsers($users);
    }
    return false;
}

/**
 * Bersihkan input teks agar aman ditampilkan (mencegah XSS)
 */
function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Apakah user sedang login?
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Paksa halaman hanya bisa diakses jika sudah login.
 * Kalau belum login, redirect ke login.php
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Simpan pesan flash (sukses/error) untuk ditampilkan sekali saja
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Ambil dan hapus pesan flash yang tersimpan
 */
function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Buat token acak untuk Remember Me
 */
function generateRememberToken(): string
{
    return bin2hex(random_bytes(32));
}

/**
 * Pasang cookie Remember Me + simpan hash token di users.json
 */
function setRememberMe(array $user): void
{
    $token = generateRememberToken();
    $hashedToken = hash('sha256', $token);

    updateUser($user['id'], ['remember_token' => $hashedToken]);

    $expire = time() + (30 * 24 * 60 * 60); // 30 hari
    setcookie('remember_email', $user['email'], $expire, '/', '', false, true);
    setcookie('remember_token', $token, $expire, '/', '', false, true);
}

/**
 * Hapus cookie & token Remember Me
 */
function clearRememberMe(): void
{
    if (isset($_SESSION['user_id'])) {
        updateUser($_SESSION['user_id'], ['remember_token' => null]);
    }
    setcookie('remember_email', '', time() - 3600, '/');
    setcookie('remember_token', '', time() - 3600, '/');
}

/**
 * Cek cookie Remember Me. Kalau valid, otomatis login-kan user.
 * Panggil fungsi ini di awal halaman publik (index/login/register).
 */
function checkRememberMe(): void
{
    if (isLoggedIn()) {
        return;
    }

    if (!isset($_COOKIE['remember_email'], $_COOKIE['remember_token'])) {
        return;
    }

    $user = findUserByEmail($_COOKIE['remember_email']);
    if (!$user || empty($user['remember_token'])) {
        return;
    }

    $incomingHash = hash('sha256', $_COOKIE['remember_token']);
    if (hash_equals($user['remember_token'], $incomingHash)) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
    }
}

/**
 * Format tanggal Indonesia sederhana (11 September 2026)
 */
function formatTanggalIndo(string $datetime): string
{
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $timestamp = strtotime($datetime);
    $tgl = date('j', $timestamp);
    $bln = (int) date('n', $timestamp);
    $thn = date('Y', $timestamp);
    return "{$tgl} {$bulan[$bln]} {$thn}";
}
