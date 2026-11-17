<?php
$pageTitle = 'Track Complaint – Smart City Portal';
require_once __DIR__ . '/includes/functions.php';
startSession();

$complaint  = null;
$trackingId = trim($_GET['id'] ?? $_POST['tracking_id'] ?? '');
$error      = '';

if ($trackingId !== '') {
    $trackingId = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $trackingId));
    if (strlen($trackingId) < 5) {
        $error = 'Please enter a valid tracking ID.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare(
            'SELECT c.*, u.full_name AS registered_name
             FROM complaints c
             LEFT JOIN users u ON u.id = c.user_id
             WHERE c.tracking_id = ?'
        );
        $stmt->execute([$trackingId]);
        $complaint = $stmt->fetch();
        if (!$complaint) {
            $error = 'No complaint found with tracking ID <strong>' . e($trackingId) . '</strong>. Please check and try again.';
        }
    }
}

// Timeline steps
$allSteps = ['Pending','In Progress','Resolved'];
$statusIndex = [
    'Pending'     => 0,
    'In Progress' => 1,
    'Resolved'    => 2,
    'Rejected'    => -1,
];

require_once __DIR__ . '/includes/header.php';
?>

<main class="form-page" id="main-content" style="align-items:flex-start;padding-top:2.5rem;">
  <div class="form-wrapper wide" style="max-width:680px;">
    <div class="form-card">
      <div class="form-card-header">
        <div class="form-icon">🔍</div>
        <h2>Track Your Complaint</h2>
        <p>Enter your tracking ID to check real-time status</p>
      </div>

      <div class="form-card-body">
        <?= renderFlash() ?>

        <form method="GET" action="track.php">
          <div class="d-flex gap-1" style="align-items:flex-end;">
            <div class="form-group" style="flex:1;margin-bottom:0;">
              <label for="tracking_id">Tracking ID <span class="required">*</span></label>
              <input type="text" id="tracking_id" name="id"
                     class="form-control" style="text-transform:uppercase;letter-spacing:1px;"
                     value="<?= e($trackingId) ?>"
                     placeholder="e.g. SCP2024AB12CD"
                     maxlength="20" required>
            </div>
            <button type="submit" class="btn btn-primary" style="padding:.65rem 1.4rem;flex-shrink:0;">
              🔍 Track
            </button>
          </div>
        </form>

        <?php if ($error): ?>
          <div class="alert alert-danger mt-2"><span>✖</span> <?= $error ?></div>
        <?php endif; ?>

      </div>
    </div>

    <?php if ($complaint): ?>
    <!-- Result card -->
    <div class="track-result mt-3">
      <div class="track-result-header">
        <div>
          <div style="font-size:.8rem;opacity:.75;text-transform:uppercase;letter-spacing:.5px;">Tracking ID</div>
          <h3><?= e($complaint['tracking_id']) ?></h3>
        </div>
        <div style="text-align:right;">
          <?= statusBadge($complaint['status']) ?>
          <div style="font-size:.8rem;opacity:.75;margin-top:.3rem;">
            Filed on <?= date('d M Y', strtotime($complaint['complaint_date'])) ?>
          </div>
        </div>
      </div>

      <?php if ($complaint['status'] !== 'Rejected'): ?>
      <!-- Timeline -->
      <div class="track-timeline">
        <?php
        $currentIdx = $statusIndex[$complaint['status']] ?? 0;
        foreach ($allSteps as $i => $step):
            $cls = '';
            if ($i < $currentIdx) $cls = 'done';
            elseif ($i === $currentIdx) $cls = 'active';
            $icon = $i < $currentIdx ? '✔' : ($i === $currentIdx ? '●' : '○');
        ?>
        <div class="timeline-step <?= $cls ?>">
          <div class="timeline-dot"><?= $icon ?></div>
          <div>
            <strong><?= e($step) ?></strong>
            <?php if ($i === $currentIdx): ?>
              <div style="font-size:.8rem;color:var(--text-muted);">Current status</div>
            <?php elseif ($i < $currentIdx): ?>
              <div style="font-size:.8rem;color:var(--green-dark);">Completed</div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="alert alert-danger" style="margin:1rem;"><span>✖</span> This complaint has been <strong>rejected</strong>.</div>
      <?php endif; ?>

      <!-- Details grid -->
      <div style="padding:0 1.5rem 1.5rem;">
        <h4 style="color:var(--blue-dark);margin-bottom:1rem;font-size:.95rem;text-transform:uppercase;letter-spacing:.4px;">Complaint Details</h4>

        <div class="detail-grid">
          <div class="detail-item">
            <label>Citizen Name</label>
            <p><?= e($complaint['citizen_name']) ?></p>
          </div>
          <div class="detail-item">
            <label>Category</label>
            <p><?= e($complaint['category']) ?></p>
          </div>
          <div class="detail-item" style="grid-column:1/-1;">
            <label>Complaint Title</label>
            <p><?= e($complaint['title']) ?></p>
          </div>
          <div class="detail-item" style="grid-column:1/-1;">
            <label>Description</label>
            <p style="font-weight:400;color:var(--text-muted);line-height:1.6;"><?= nl2br(e($complaint['description'])) ?></p>
          </div>
          <div class="detail-item">
            <label>City Area / Address</label>
            <p><?= e($complaint['city_area']) ?></p>
          </div>
          <div class="detail-item">
            <label>Date Submitted</label>
            <p><?= date('d M Y', strtotime($complaint['created_at'])) ?></p>
          </div>
          <?php if ($complaint['admin_remarks']): ?>
          <div class="detail-item" style="grid-column:1/-1;">
            <label>Admin Remarks</label>
            <div class="alert alert-info" style="margin:0;">
              <?= nl2br(e($complaint['admin_remarks'])) ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <?php if ($complaint['photo']): ?>
        <div style="margin-top:1.25rem;">
          <label style="font-size:.78rem;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);">Attached Photo</label>
          <img src="<?= UPLOAD_URL . e($complaint['photo']) ?>"
               alt="Complaint photo"
               style="max-height:280px;border-radius:8px;border:1px solid var(--border);margin-top:.5rem;width:auto;">
        </div>
        <?php endif; ?>

        <div style="display:flex;gap:.75rem;margin-top:1.5rem;flex-wrap:wrap;">
          <a href="<?= APP_URL ?>/track.php" class="btn btn-outline btn-sm">🔍 Track Another</a>
          <button id="print-btn" class="btn btn-sm" style="background:#f3f4f6;border:1px solid var(--border);">🖨️ Print</button>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
