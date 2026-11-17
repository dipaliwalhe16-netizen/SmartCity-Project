<?php
$pageTitle = 'Register – Smart City Portal';
require_once __DIR__ . '/includes/functions.php';
startSession();

if (isLoggedIn()) {
    redirect(APP_URL . '/dashboard.php');
}

$errors = [];
$values = ['full_name'=>'','email'=>'','mobile'=>'','city_area'=>'','password'=>'','confirm_password'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect & sanitise
    $full_name        = trim($_POST['full_name']        ?? '');
    $email            = trim(strtolower($_POST['email'] ?? ''));
    $mobile           = trim($_POST['mobile']           ?? '');
    $city_area        = trim($_POST['city_area']        ?? '');
    $password         = $_POST['password']              ?? '';
    $confirm_password = $_POST['confirm_password']      ?? '';

    $values = compact('full_name','email','mobile','city_area','password','confirm_password');

    // Validate
    if (strlen($full_name) < 3 || strlen($full_name) > 150) {
        $errors['full_name'] = 'Full name must be 3–150 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
        $errors['mobile'] = 'Enter a valid 10-digit Indian mobile number.';
    }
    if (strlen($city_area) < 3) {
        $errors['city_area'] = 'City area / address is required.';
    }
    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Password must include at least one uppercase letter and one digit.';
    }
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $db = getDB();
        // Check duplicate email
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'This email is already registered. Please login.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]);
            $ins  = $db->prepare(
                'INSERT INTO users (full_name, email, mobile, city_area, password) VALUES (?,?,?,?,?)'
            );
            $ins->execute([$full_name, $email, $mobile, $city_area, $hash]);
            setFlash('success', 'Registration successful! Please login.');
            redirect(APP_URL . '/login.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="form-page" id="main-content">
  <div class="form-wrapper">
    <div class="form-card">
      <div class="form-card-header">
        <div class="form-icon">👤</div>
        <h2>Create Your Account</h2>
        <p>Join the Smart City Portal and start reporting civic issues</p>
      </div>

      <div class="form-card-body">
        <?= renderFlash() ?>
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <span>✖</span> Please fix the errors below.
          </div>
        <?php endif; ?>

        <form method="POST" action="register.php" data-validate novalidate>

          <div class="form-group">
            <label for="full_name">Full Name <span class="required">*</span></label>
            <input type="text" id="full_name" name="full_name" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                   value="<?= e($values['full_name']) ?>" placeholder="e.g. Arjun Sharma" maxlength="150" required autocomplete="name">
            <?php if (isset($errors['full_name'])): ?>
              <div class="form-error"><?= e($errors['full_name']) ?></div>
            <?php endif; ?>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="email">Email Address <span class="required">*</span></label>
              <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                     value="<?= e($values['email']) ?>" placeholder="you@example.com" required autocomplete="email">
              <?php if (isset($errors['email'])): ?>
                <div class="form-error"><?= e($errors['email']) ?></div>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label for="mobile">Mobile Number <span class="required">*</span></label>
              <input type="tel" id="mobile" name="mobile" class="form-control <?= isset($errors['mobile']) ? 'is-invalid' : '' ?>"
                     value="<?= e($values['mobile']) ?>" placeholder="9XXXXXXXXX" maxlength="10" required autocomplete="tel">
              <?php if (isset($errors['mobile'])): ?>
                <div class="form-error"><?= e($errors['mobile']) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-group">
            <label for="city_area">City Area / Address <span class="required">*</span></label>
            <input type="text" id="city_area" name="city_area" class="form-control <?= isset($errors['city_area']) ? 'is-invalid' : '' ?>"
                   value="<?= e($values['city_area']) ?>" placeholder="e.g. Andheri East, Mumbai" maxlength="200" required>
            <?php if (isset($errors['city_area'])): ?>
              <div class="form-error"><?= e($errors['city_area']) ?></div>
            <?php endif; ?>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="password">Password <span class="required">*</span></label>
              <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                     placeholder="Min 8 chars" required autocomplete="new-password">
              <div class="form-hint">Minimum 8 characters, include uppercase &amp; digit.</div>
              <?php if (isset($errors['password'])): ?>
                <div class="form-error"><?= e($errors['password']) ?></div>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label for="confirm_password">Confirm Password <span class="required">*</span></label>
              <input type="password" id="confirm_password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                     placeholder="Re-enter password" required autocomplete="new-password">
              <?php if (isset($errors['confirm_password'])): ?>
                <div class="form-error"><?= e($errors['confirm_password']) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-full" style="margin-top:.5rem;">
            Create Account →
          </button>
        </form>
      </div>

      <div class="form-footer">
        Already have an account? <a href="<?= APP_URL ?>/login.php" style="font-weight:600;">Login here</a>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
