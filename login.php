<?php
require_once 'functions.php';

checkRememberMe();
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$old = ['email' => ''];
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = trim($_POST['email'] ?? '');
    $password   = (string) ($_POST['password'] ?? '');
    $rememberMe = isset($_POST['remember_me']);

    $old['email'] = sanitize($email);

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } else {
        $user = findUserByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Email atau password salah.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            if ($rememberMe) {
                setRememberMe($user);
            }

            setFlash('success', 'Berhasil login. Selamat datang kembali, ' . $user['name'] . '!');
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — DelaAuth</title>
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
            <p class="subtitle">Welcome Back 👋</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?= sanitize($flash['type']) ?>">
                    <?= sanitize($flash['message']) ?>
                </div>
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

            <form method="POST" action="login.php" novalidate>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="dela@gmail.com"
                        value="<?= $old['email'] ?>"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="toggle-password" data-target="password" aria-label="Tampilkan password">👁</button>
                    </div>
                </div>

                <div class="form-group form-check">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me" id="remember_me">
                        Remember Me
                    </label>
                </div>

                <button type="submit" class="btn-primary">LOGIN</button>
            </form>

            <p class="switch-auth">Belum punya akun? <a href="register.php">Register</a></p>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
