<?php
require_once 'functions.php';

checkRememberMe();
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name            = trim($_POST['name'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $password        = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    // Simpan kembali input (sudah disanitasi) untuk ditampilkan jika ada error
    $old['name']  = sanitize($name);
    $old['email'] = sanitize($email);

    // ===== VALIDASI =====
    if ($name === '') {
        $errors[] = 'Nama tidak boleh kosong.';
    } elseif (strlen($name) < 3) {
        $errors[] = 'Nama minimal 3 karakter.';
    }

    if ($email === '') {
        $errors[] = 'Email tidak boleh kosong.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if ($password === '') {
        $errors[] = 'Password tidak boleh kosong.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }

    if ($confirmPassword !== $password) {
        $errors[] = 'Konfirmasi password tidak cocok dengan password.';
    }

    // Cek duplikat email hanya jika email valid
    if (empty($errors) && findUserByEmail($email)) {
        $errors[] = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';
    }

    // ===== SIMPAN JIKA VALID =====
    if (empty($errors)) {
        $users = loadUsers();

        $newUser = [
            'id'             => uniqid('user_', true),
            'name'           => sanitize($name),
            'email'          => strtolower(sanitize($email)),
            'password'       => password_hash($password, PASSWORD_DEFAULT),
            'created_at'     => date('Y-m-d H:i:s'),
            'remember_token' => null,
        ];

        $users[] = $newUser;

        if (saveUsers($users)) {
            setFlash('success', 'Registrasi berhasil! Silakan login ke akunmu.');
            header('Location: login.php');
            exit;
        } else {
            $errors[] = 'Terjadi kesalahan saat menyimpan data. Coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — DelaAuth</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Ganti tema">🌙</button>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="brand">
                <span class="brand-logo">🔐</span>
                <h1>DELAAUTH</h1>
            </div>
            <p class="subtitle">Buat akun baru</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= sanitize($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" novalidate>
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Dela Chintaka"
                        value="<?= $old['name'] ?>"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="dela@gmail.com"
                        value="<?= $old['email'] ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                        <button type="button" class="toggle-password" data-target="password" aria-label="Tampilkan password">👁</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <div class="password-field">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                        <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Tampilkan password">👁</button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">REGISTER</button>
            </form>

            <p class="switch-auth">Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
