<?php
$pageTitle = 'Login – Smart City Portal';
require_once __DIR__ . '/includes/functions.php';
startSession();

if (isLoggedIn()) {
    redirect(APP_URL . '/dashboard.php');
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim(strtolower($_POST['email']    ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        $db   = getDB();
        $stmt = $db->prepare('SELECT id, full_name, password, is_active FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors['general'] = 'Invalid email address or password.';
        } elseif (!$user['is_active']) {
            $errors['general'] = 'Your account has been deactivated. Please contact support.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
            redirect(APP_URL . '/dashboard.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="form-page" id="main-content">
  <div class="form-wrapper">
    <div class="form-card">
      <div class="form-card-header">
        <div class="form-icon">🔐</div>
        <h2>Citizen Login</h2>
        <p>Sign in to manage and track your complaints</p>
      </div>

      <div class="form-card-body">
        <?= renderFlash() ?>

        <?php if (isset($errors['general'])): ?>
          <div class="alert alert-danger"><span>✖</span> <?= e($errors['general']) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" data-validate novalidate>

          <div class="form-group">
            <label for="email">Email Address <span class="required">*</span></label>
            <input type="email" id="email" name="email"
                   class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                   value="<?= e($email) ?>" placeholder="you@example.com"
                   required autocomplete="email">
            <?php if (isset($errors['email'])): ?>
              <div class="form-error"><?= e($errors['email']) ?></div>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label for="password">Password <span class="required">*</span></label>
            <input type="password" id="password" name="password"
                   class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                   placeholder="Your password" required autocomplete="current-password">
            <?php if (isset($errors['password'])): ?>
              <div class="form-error"><?= e($errors['password']) ?></div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn btn-primary btn-full">Login →</button>
        </form>

        <div style="text-align:center;margin-top:1.2rem;padding-top:1rem;border-top:1px solid var(--border);">
          <p style="font-size:.82rem;color:var(--text-muted);">
            Demo credentials: <code>citizen@example.com</code> / <code>password</code>
          </p>
        </div>
      </div>

      <div class="form-footer">
        Don't have an account? <a href="<?= APP_URL ?>/register.php" style="font-weight:600;">Register here</a>
      </div>
    </div>

    <!-- Admin link -->
    <p class="text-center mt-2" style="font-size:.85rem;color:var(--text-muted);">
      Are you an administrator?
      <a href="<?= APP_URL ?>/admin/login.php" style="font-weight:600;">Admin Login →</a>
    </p>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
