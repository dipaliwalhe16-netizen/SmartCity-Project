<?php
$pageTitle = 'File a Complaint – Smart City Portal';
require_once __DIR__ . '/includes/functions.php';
startSession();

$errors = [];
$values = [
    'citizen_name'   => '',
    'email'          => '',
    'mobile'         => '',
    'category'       => '',
    'title'          => '',
    'description'    => '',
    'city_area'      => '',
    'complaint_date' => date('Y-m-d'),
];

// Pre-fill if logged in
if (isLoggedIn()) {
    $db       = getDB();
    $stmt     = $db->prepare('SELECT full_name, email, mobile, city_area FROM users WHERE id=?');
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    if ($u) {
        $values['citizen_name'] = $u['full_name'];
        $values['email']        = $u['email'];
        $values['mobile']       = $u['mobile'];
        $values['city_area']    = $u['city_area'] ?? '';
    }
}

$categories = [
    'Roads & Footpaths','Water Supply','Electricity','Garbage & Sanitation',
    'Drainage & Sewage','Street Lights','Parks & Gardens','Public Transport',
    'Noise Pollution','Illegal Construction','Other',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect
    $citizen_name   = trim($_POST['citizen_name']   ?? '');
    $email          = trim(strtolower($_POST['email'] ?? ''));
    $mobile         = trim($_POST['mobile']         ?? '');
    $category       = trim($_POST['category']       ?? '');
    $title          = trim($_POST['title']          ?? '');
    $description    = trim($_POST['description']    ?? '');
    $city_area      = trim($_POST['city_area']      ?? '');
    $complaint_date = trim($_POST['complaint_date'] ?? '');

    $values = compact('citizen_name','email','mobile','category','title','description','city_area','complaint_date');

    // Validate
    if (strlen($citizen_name) < 3) $errors['citizen_name'] = 'Name must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    if (!preg_match('/^[6-9]\d{9}$/', $mobile)) $errors['mobile'] = 'Enter a valid 10-digit mobile number.';
    if (!in_array($category, $categories, true)) $errors['category'] = 'Please select a valid category.';
    if (strlen($title) < 5) $errors['title'] = 'Title must be at least 5 characters.';
    if (strlen($description) < 20) $errors['description'] = 'Description must be at least 20 characters.';
    if (strlen($city_area) < 3) $errors['city_area'] = 'City area / address is required.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $complaint_date) || strtotime($complaint_date) === false) {
        $errors['complaint_date'] = 'Enter a valid date.';
    } elseif (strtotime($complaint_date) > strtotime(date('Y-m-d'))) {
        $errors['complaint_date'] = 'Complaint date cannot be in the future.';
    }

    // Photo
    $photoFilename = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = handleImageUpload($_FILES['photo']);
        if (!$upload['ok']) {
            $errors['photo'] = $upload['error'];
        } else {
            $photoFilename = $upload['path'];
        }
    }

    if (empty($errors)) {
        $db          = getDB();
        $trackingId  = generateTrackingId();
        $userId      = isLoggedIn() ? $_SESSION['user_id'] : null;

        $ins = $db->prepare(
            'INSERT INTO complaints
             (tracking_id, user_id, citizen_name, email, mobile, category, title,
              description, city_area, complaint_date, photo)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        );
        $ins->execute([
            $trackingId, $userId, $citizen_name, $email, $mobile, $category,
            $title, $description, $city_area, $complaint_date,
            $photoFilename ?: null
        ]);

        setFlash('success', "Complaint filed successfully! Your Tracking ID is: {$trackingId}");
        redirect(APP_URL . '/track.php?id=' . urlencode($trackingId));
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="form-page" id="main-content" style="padding-top:2rem;padding-bottom:2rem;">
  <div class="form-wrapper wide">
    <div class="form-card">
      <div class="form-card-header">
        <div class="form-icon">📝</div>
        <h2>File a Complaint</h2>
        <p>Report a civic issue to your city authority</p>
      </div>

      <div class="form-card-body">
        <?= renderFlash() ?>
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger"><span>✖</span> Please correct the errors highlighted below.</div>
        <?php endif; ?>

        <form method="POST" action="complaint.php" enctype="multipart/form-data" data-validate novalidate>

          <h3 style="font-size:1rem;color:var(--blue-dark);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid var(--border);">
            👤 Personal Information
          </h3>

          <div class="form-row">
            <div class="form-group">
              <label for="citizen_name">Citizen Name <span class="required">*</span></label>
              <input type="text" id="citizen_name" name="citizen_name"
                     class="form-control <?= isset($errors['citizen_name']) ? 'is-invalid' : '' ?>"
                     value="<?= e($values['citizen_name']) ?>" placeholder="Your full name"
                     maxlength="150" required>
              <?php if (isset($errors['citizen_name'])): ?>
                <div class="form-error"><?= e($errors['citizen_name']) ?></div>
              <?php else: ?><div class="form-error"></div><?php endif; ?>
            </div>

            <div class="form-group">
              <label for="email">Email Address <span class="required">*</span></label>
              <input type="email" id="email" name="email"
                     class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                     value="<?= e($values['email']) ?>" placeholder="you@example.com" required>
              <?php if (isset($errors['email'])): ?>
                <div class="form-error"><?= e($errors['email']) ?></div>
              <?php else: ?><div class="form-error"></div><?php endif; ?>
            </div>
          </div>

          <div class="form-group" style="max-width:300px;">
            <label for="mobile">Mobile Number <span class="required">*</span></label>
            <input type="tel" id="mobile" name="mobile"
                   class="form-control <?= isset($errors['mobile']) ? 'is-invalid' : '' ?>"
                   value="<?= e($values['mobile']) ?>" placeholder="9XXXXXXXXX" maxlength="10" required>
            <?php if (isset($errors['mobile'])): ?>
              <div class="form-error"><?= e($errors['mobile']) ?></div>
            <?php else: ?><div class="form-error"></div><?php endif; ?>
          </div>

          <h3 style="font-size:1rem;color:var(--blue-dark);margin:1.5rem 0 1rem;padding-bottom:.5rem;border-bottom:1px solid var(--border);">
            📋 Complaint Details
          </h3>

          <div class="form-row">
            <div class="form-group">
              <label for="category">Complaint Category <span class="required">*</span></label>
              <select id="category" name="category"
                      class="form-control <?= isset($errors['category']) ? 'is-invalid' : '' ?>" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= e($cat) ?>" <?= $values['category'] === $cat ? 'selected' : '' ?>>
                    <?= e($cat) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['category'])): ?>
                <div class="form-error"><?= e($errors['category']) ?></div>
              <?php else: ?><div class="form-error"></div><?php endif; ?>
            </div>

            <div class="form-group">
              <label for="complaint_date">Complaint Date <span class="required">*</span></label>
              <input type="date" id="complaint_date" name="complaint_date"
                     class="form-control <?= isset($errors['complaint_date']) ? 'is-invalid' : '' ?>"
                     value="<?= e($values['complaint_date']) ?>"
                     max="<?= date('Y-m-d') ?>" required>
              <?php if (isset($errors['complaint_date'])): ?>
                <div class="form-error"><?= e($errors['complaint_date']) ?></div>
              <?php else: ?><div class="form-error"></div><?php endif; ?>
            </div>
          </div>

          <div class="form-group">
            <label for="title">Complaint Title <span class="required">*</span></label>
            <input type="text" id="title" name="title"
                   class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                   value="<?= e($values['title']) ?>"
                   placeholder="Brief title describing the issue" maxlength="255" required>
            <?php if (isset($errors['title'])): ?>
              <div class="form-error"><?= e($errors['title']) ?></div>
            <?php else: ?><div class="form-error"></div><?php endif; ?>
          </div>

          <div class="form-group">
            <label for="description">Complaint Description <span class="required">*</span></label>
            <textarea id="description" name="description"
                      class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                      placeholder="Describe the issue in detail (minimum 20 characters)"
                      maxlength="2000" required><?= e($values['description']) ?></textarea>
            <div class="form-hint"><span class="char-count">0 / 2000</span></div>
            <?php if (isset($errors['description'])): ?>
              <div class="form-error"><?= e($errors['description']) ?></div>
            <?php else: ?><div class="form-error"></div><?php endif; ?>
          </div>

          <div class="form-group">
            <label for="city_area">City Area / Address <span class="required">*</span></label>
            <input type="text" id="city_area" name="city_area"
                   class="form-control <?= isset($errors['city_area']) ? 'is-invalid' : '' ?>"
                   value="<?= e($values['city_area']) ?>"
                   placeholder="e.g. Near Central Park, Sector 12, Noida"
                   maxlength="255" required>
            <?php if (isset($errors['city_area'])): ?>
              <div class="form-error"><?= e($errors['city_area']) ?></div>
            <?php else: ?><div class="form-error"></div><?php endif; ?>
          </div>

          <div class="form-group">
            <label for="photo">Upload Photo <span style="color:var(--text-muted);font-weight:400;">(optional, max 5 MB)</span></label>
            <div class="file-upload-area">
              <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif,image/webp">
              <div class="upload-icon">📷</div>
              <p>Click or drag a photo here</p>
              <p style="font-size:.75rem;margin-top:.25rem;">JPG, PNG, GIF, or WebP – max 5 MB</p>
            </div>
            <div id="photo-preview"></div>
            <?php if (isset($errors['photo'])): ?>
              <div class="form-error"><?= e($errors['photo']) ?></div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn btn-primary btn-full" style="margin-top:.75rem;">
            🚀 Submit Complaint
          </button>
        </form>
      </div>

      <div class="form-footer">
        You will receive a unique <strong>Tracking ID</strong> after submission.
        <?php if (!isLoggedIn()): ?>
          <br><a href="<?= APP_URL ?>/login.php">Login</a> to manage complaints in your dashboard.
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
