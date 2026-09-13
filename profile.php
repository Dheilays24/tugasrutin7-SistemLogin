<?php
require_once 'functions.php';
requireLogin();

$user = findUserById($_SESSION['user_id']);
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmNew  = (string) ($_POST['confirm_new_password'] ?? '');

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

    // Cek duplikat email hanya jika email diganti ke email user lain
    if (empty($errors) && strtolower($email) !== strtolower($user['email'])) {
        $existing = findUserByEmail($email);
        if ($existing && $existing['id'] !== $user['id']) {
            $errors[] = 'Email tersebut sudah digunakan oleh akun lain.';
        }
    }

    // Ganti password bersifat opsional
    $wantsPasswordChange = $newPassword !== '' || $confirmNew !== '';
    if ($wantsPasswordChange) {
        if (strlen($newPassword) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        }
        if ($newPassword !== $confirmNew) {
            $errors[] = 'Konfirmasi password baru tidak cocok.';
        }
    }

    if (empty($errors)) {
        $updateData = [
            'name'  => sanitize($name),
            'email' => strtolower(sanitize($email)),
        ];

        if ($wantsPasswordChange) {
            $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        if (updateUser($user['id'], $updateData)) {
            $_SESSION['user_name']  = $updateData['name'];
            $_SESSION['user_email'] = $updateData['email'];
            $user = findUserById($user['id']);
            $success = 'Profile berhasil diperbarui.';
        } else {
            $errors[] = 'Terjadi kesalahan saat menyimpan perubahan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile — DelaAuth</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Ganti tema">🌙</button>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <span class="brand-logo">🔐</span>
                <span>DELAAUTH</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php">📊 Dashboard</a>
                <a href="profile.php" class="active">👤 Profile</a>
                <a href="logout.php" class="logout-link">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h2>Edit Profile</h2>
                <div class="user-chip">👤 <?= sanitize($user['name']) ?></div>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= sanitize($success) ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= sanitize($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <section class="detail-panel">
                <h4>Profile</h4>
                <form method="POST" action="profile.php" novalidate>
                    <div class="form-group">
                        <label for="name">Nama</label>
                        <input type="text" id="name" name="name" value="<?= sanitize($user['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= sanitize($user['email']) ?>" required>
                    </div>

                    <hr class="divider">
                    <p class="hint-text">Kosongkan bagian di bawah ini jika tidak ingin mengganti password.</p>

                    <div class="form-group">
                        <label for="new_password">Password Baru</label>
                        <div class="password-field">
                            <input type="password" id="new_password" name="new_password" placeholder="Minimal 6 karakter">
                            <button type="button" class="toggle-password" data-target="new_password" aria-label="Tampilkan password">👁</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_new_password">Konfirmasi Password Baru</label>
                        <div class="password-field">
                            <input type="password" id="confirm_new_password" name="confirm_new_password" placeholder="Ulangi password baru">
                            <button type="button" class="toggle-password" data-target="confirm_new_password" aria-label="Tampilkan password">👁</button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </form>
            </section>
        </main>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
