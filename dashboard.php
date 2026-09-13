<?php
require_once 'functions.php';
requireLogin();

$user = findUserById($_SESSION['user_id']);
if (!$user) {
    // Data user sudah tidak ada (misalnya dihapus manual dari JSON)
    session_destroy();
    header('Location: login.php');
    exit;
}

$flash = getFlash();
$joinDate = formatTanggalIndo($user['created_at']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — DelaAuth</title>
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
                <a href="dashboard.php" class="active">📊 Dashboard</a>
                <a href="profile.php">👤 Profile</a>
                <a href="logout.php" class="logout-link">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h2>Dashboard</h2>
                <div class="user-chip">
                    👤 <?= sanitize($user['name']) ?>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="alert alert-<?= sanitize($flash['type']) ?>">
                    <?= sanitize($flash['message']) ?>
                </div>
            <?php endif; ?>

            <section class="welcome-banner">
                <h3>Welcome back, <?= sanitize($user['name']) ?> 👋</h3>
                <p>Senang melihatmu lagi di DelaAuth.</p>
            </section>

            <section class="card-grid">
                <div class="info-card">
                    <div class="info-icon">👤</div>
                    <div class="info-label">Profile</div>
                    <div class="info-value"><?= sanitize($user['name']) ?></div>
                </div>

                <div class="info-card">
                    <div class="info-icon">🔐</div>
                    <div class="info-label">Account</div>
                    <div class="info-value">
                        <span class="status-dot"></span> Active
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon">📅</div>
                    <div class="info-label">Joined</div>
                    <div class="info-value"><?= sanitize($joinDate) ?></div>
                </div>
            </section>

            <section class="detail-panel">
                <h4>Account Information</h4>
                <table class="detail-table">
                    <tr>
                        <td>Nama</td>
                        <td><?= sanitize($user['name']) ?></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td><?= sanitize($user['email']) ?></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td><span class="status-dot"></span> Active</td>
                    </tr>
                    <tr>
                        <td>Bergabung sejak</td>
                        <td><?= sanitize($joinDate) ?></td>
                    </tr>
                </table>
                <a href="profile.php" class="btn-secondary">Edit Profile</a>
            </section>
        </main>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
