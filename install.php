<?php
/**
 * Smart City Portal – One-time Installation Script
 *
 * Run this ONCE after importing database.sql to:
 *   1. Verify the DB connection
 *   2. Create tables if they don't exist
 *   3. Set the admin password securely
 *
 * DELETE this file after installation.
 */
require_once __DIR__ . '/includes/config.php';

$step    = (int)($_POST['step'] ?? $_GET['step'] ?? 0);
$message = '';
$success = false;

if ($step === 1) {
    // Test DB connection
    try {
        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET),
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        // Create DB if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `" . DB_NAME . "`");

        // Read and run database.sql
        $sql = file_get_contents(__DIR__ . '/database.sql');
        // Split statements
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
            if ($stmt) $pdo->exec($stmt);
        }
        $message = '✅ Database created and tables imported successfully.';
        $success = true;
    } catch (PDOException $e) {
        $message = '❌ Database error: ' . htmlspecialchars($e->getMessage());
    }
}

if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPass = $_POST['admin_password']         ?? '';
    $confirm = $_POST['admin_password_confirm'] ?? '';
    if (strlen($newPass) < 8) {
        $message = '❌ Password must be at least 8 characters.';
    } elseif ($newPass !== $confirm) {
        $message = '❌ Passwords do not match.';
    } else {
        try {
            require_once __DIR__ . '/includes/db.php';
            $db   = getDB();
            $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost'=>12]);
            $stmt = $db->prepare("UPDATE admin_users SET password=? WHERE username='admin'");
            $stmt->execute([$hash]);
            $message = '✅ Admin password updated. You can now <a href="' . APP_URL . '/admin/login.php">login</a>. <strong>Delete this file!</strong>';
            $success = true;
        } catch (Exception $e) {
            $message = '❌ Error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Install – Smart City Portal</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body style="background:var(--bg);padding:2rem;">
<div style="max-width:600px;margin:0 auto;">
  <div style="text-align:center;margin-bottom:2rem;">
    <div style="font-size:3rem;">🏙️</div>
    <h1 style="color:var(--blue-dark);">Smart City Portal – Installer</h1>
    <p style="color:var(--text-muted);">Complete these steps to finish setup.</p>
  </div>

  <?php if ($message): ?>
    <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>"><?= $message ?></div>
  <?php endif; ?>

  <!-- Step 1 -->
  <div class="card mb-2" style="margin-bottom:1rem;">
    <div class="card-header"><h3>Step 1 – Create Database &amp; Tables</h3></div>
    <div class="card-body">
      <p style="font-size:.9rem;margin-bottom:1rem;">
        This will create the <code><?= DB_NAME ?></code> database and run <code>database.sql</code>.
      </p>
      <a href="install.php?step=1" class="btn btn-primary">Run Database Setup</a>
    </div>
  </div>

  <!-- Step 2 -->
  <div class="card mb-2">
    <div class="card-header"><h3>Step 2 – Set Admin Password</h3></div>
    <div class="card-body">
      <form method="POST" action="install.php?step=2">
        <div class="form-group">
          <label for="admin_password">New Admin Password</label>
          <input type="password" id="admin_password" name="admin_password" class="form-control"
                 placeholder="Min 8 characters" required>
        </div>
        <div class="form-group">
          <label for="admin_password_confirm">Confirm Password</label>
          <input type="password" id="admin_password_confirm" name="admin_password_confirm" class="form-control"
                 placeholder="Repeat password" required>
        </div>
        <button type="submit" class="btn btn-primary">Set Password</button>
      </form>
    </div>
  </div>

  <div class="alert alert-warning">
    <span>⚠️</span> <strong>Security:</strong> Delete <code>install.php</code> after setup is complete.
  </div>
</div>
</body>
</html>
