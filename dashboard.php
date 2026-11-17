<?php
$pageTitle = 'My Dashboard – Smart City Portal';
require_once __DIR__ . '/includes/functions.php';
startSession();
requireLogin(APP_URL . '/login.php');

$db     = getDB();
$userId = (int)$_SESSION['user_id'];

// Fetch user info
$userStmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch();
if (!$user) {
    session_destroy();
    redirect(APP_URL . '/login.php');
}

// Stats
$stats = [];
foreach (['Pending','In Progress','Resolved','Rejected'] as $s) {
    $st = $db->prepare('SELECT COUNT(*) FROM complaints WHERE user_id=? AND status=?');
    $st->execute([$userId, $s]);
    $stats[$s] = (int)$st->fetchColumn();
}
$totalComplaints = array_sum($stats);

// Recent 5 complaints
$recent = $db->prepare(
    'SELECT tracking_id, title, category, status, complaint_date, created_at
     FROM complaints WHERE user_id=? ORDER BY created_at DESC LIMIT 5'
);
$recent->execute([$userId]);
$recentComplaints = $recent->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
<div class="dashboard-layout">

  <!-- Sidebar -->
  <aside class="sidebar" aria-label="Citizen navigation">
    <div class="sidebar-logo"><span>🏙️</span> SmartCity</div>
    <nav class="sidebar-menu">
      <div class="sidebar-section">Main Menu</div>
      <a href="<?= APP_URL ?>/dashboard.php"      class="active"><span class="menu-icon">📊</span> Dashboard</a>
      <a href="<?= APP_URL ?>/complaint.php"><span class="menu-icon">📝</span> File Complaint</a>
      <a href="<?= APP_URL ?>/my-complaints.php"><span class="menu-icon">📋</span> My Complaints</a>
      <a href="<?= APP_URL ?>/track.php"><span class="menu-icon">🔍</span> Track Complaint</a>
      <div class="sidebar-section">Account</div>
      <a href="<?= APP_URL ?>/logout.php"><span class="menu-icon">🚪</span> Logout</a>
    </nav>
  </aside>

  <!-- Main -->
  <section class="main-content">
    <?= renderFlash() ?>

    <div class="page-header">
      <h1>👋 Welcome, <?= e($user['full_name']) ?>!</h1>
      <a href="<?= APP_URL ?>/complaint.php" class="btn btn-primary btn-sm">+ New Complaint</a>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card blue">
        <div class="stat-icon">📋</div>
        <div class="stat-info">
          <div class="stat-value"><?= $totalComplaints ?></div>
          <div class="stat-label">Total Filed</div>
        </div>
      </div>
      <div class="stat-card orange">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
          <div class="stat-value"><?= $stats['Pending'] ?></div>
          <div class="stat-label">Pending</div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#3b82f6;">
        <div class="stat-icon" style="background:#dbeafe;">🔧</div>
        <div class="stat-info">
          <div class="stat-value"><?= $stats['In Progress'] ?></div>
          <div class="stat-label">In Progress</div>
        </div>
      </div>
      <div class="stat-card green">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
          <div class="stat-value"><?= $stats['Resolved'] ?></div>
          <div class="stat-label">Resolved</div>
        </div>
      </div>
    </div>

    <!-- Profile card + Recent complaints -->
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:1.5rem;align-items:start;" class="responsive-grid-2">

      <!-- Profile -->
      <div class="card">
        <div class="card-header"><h3>👤 My Profile</h3></div>
        <div class="card-body">
          <div style="text-align:center;padding-bottom:1rem;border-bottom:1px solid var(--border);margin-bottom:1rem;">
            <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--green-dark),var(--blue-dark));display:flex;align-items:center;justify-content:center;color:#fff;font-size:2rem;margin:0 auto .75rem;">
              <?= strtoupper(mb_substr($user['full_name'],0,1)) ?>
            </div>
            <strong><?= e($user['full_name']) ?></strong>
          </div>
          <dl style="font-size:.88rem;display:flex;flex-direction:column;gap:.6rem;">
            <div><dt style="color:var(--text-muted);font-size:.78rem;text-transform:uppercase;">Email</dt><dd><?= e($user['email']) ?></dd></div>
            <div><dt style="color:var(--text-muted);font-size:.78rem;text-transform:uppercase;">Mobile</dt><dd><?= e($user['mobile']) ?></dd></div>
            <div><dt style="color:var(--text-muted);font-size:.78rem;text-transform:uppercase;">Area</dt><dd><?= e($user['city_area'] ?? '–') ?></dd></div>
            <div><dt style="color:var(--text-muted);font-size:.78rem;text-transform:uppercase;">Member Since</dt><dd><?= date('d M Y', strtotime($user['created_at'])) ?></dd></div>
          </dl>
        </div>
      </div>

      <!-- Recent complaints -->
      <div class="card">
        <div class="card-header">
          <h3>📋 Recent Complaints</h3>
          <a href="<?= APP_URL ?>/my-complaints.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="card-body" style="padding:0;">
          <?php if (empty($recentComplaints)): ?>
            <div style="padding:2rem;text-align:center;color:var(--text-muted);">
              <div style="font-size:2.5rem;margin-bottom:.5rem;">📭</div>
              <p>No complaints filed yet.</p>
              <a href="<?= APP_URL ?>/complaint.php" class="btn btn-primary btn-sm" style="margin-top:.75rem;">File Your First Complaint</a>
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Tracking ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recentComplaints as $c): ?>
                    <tr>
                      <td><code style="font-size:.8rem;"><?= e($c['tracking_id']) ?></code></td>
                      <td><?= e(mb_strimwidth($c['title'],0,40,'…')) ?></td>
                      <td><span style="font-size:.8rem;"><?= e($c['category']) ?></span></td>
                      <td><?= statusBadge($c['status']) ?></td>
                      <td style="white-space:nowrap;"><?= date('d M Y', strtotime($c['complaint_date'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="card mt-3">
      <div class="card-header"><h3>⚡ Quick Actions</h3></div>
      <div class="card-body" style="display:flex;gap:1rem;flex-wrap:wrap;">
        <a href="<?= APP_URL ?>/complaint.php"     class="btn btn-primary">📝 File New Complaint</a>
        <a href="<?= APP_URL ?>/my-complaints.php" class="btn btn-outline">📋 View All Complaints</a>
        <a href="<?= APP_URL ?>/track.php"         class="btn btn-blue">🔍 Track Complaint</a>
      </div>
    </div>

  </section>
</div>
</main>

<style>
@media(max-width:768px){.responsive-grid-2{grid-template-columns:1fr!important;}}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
