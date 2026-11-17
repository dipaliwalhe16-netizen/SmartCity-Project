<?php
$pageTitle = 'My Complaints – Smart City Portal';
require_once __DIR__ . '/includes/functions.php';
startSession();
requireLogin(APP_URL . '/login.php');

$db     = getDB();
$userId = (int)$_SESSION['user_id'];

// Filters
$filterStatus   = $_GET['status']   ?? '';
$filterCategory = $_GET['category'] ?? '';
$search         = trim($_GET['search'] ?? '');
$page           = max(1, (int)($_GET['page'] ?? 1));
$perPage        = 10;

$allowedStatuses = ['','Pending','In Progress','Resolved','Rejected'];
if (!in_array($filterStatus, $allowedStatuses, true)) $filterStatus = '';

$categories = [
    '','Roads & Footpaths','Water Supply','Electricity','Garbage & Sanitation',
    'Drainage & Sewage','Street Lights','Parks & Gardens','Public Transport',
    'Noise Pollution','Illegal Construction','Other',
];

// Build WHERE
$where  = ['c.user_id = :uid'];
$params = [':uid' => $userId];

if ($filterStatus)   { $where[] = 'c.status = :status';   $params[':status']   = $filterStatus; }
if ($filterCategory) { $where[] = 'c.category = :cat';    $params[':cat']      = $filterCategory; }
if ($search)         { $where[] = '(c.title LIKE :s OR c.tracking_id LIKE :s)'; $params[':s'] = "%{$search}%"; }

$whereSql = 'WHERE ' . implode(' AND ', $where);

$totalStmt = $db->prepare("SELECT COUNT(*) FROM complaints c {$whereSql}");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();

$pg     = paginate($total, $perPage, $page);
$offset = $pg['offset'];

$listStmt = $db->prepare(
    "SELECT c.id, c.tracking_id, c.title, c.category, c.status, c.complaint_date, c.created_at, c.photo
     FROM complaints c {$whereSql}
     ORDER BY c.created_at DESC
     LIMIT :limit OFFSET :offset"
);
foreach ($params as $key => $val) $listStmt->bindValue($key, $val);
$listStmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$listStmt->execute();
$complaints = $listStmt->fetchAll();

// Selected complaint detail (modal)
$selectedId = (int)($_GET['view'] ?? 0);
$detail = null;
if ($selectedId) {
    $ds = $db->prepare('SELECT * FROM complaints WHERE id=? AND user_id=?');
    $ds->execute([$selectedId, $userId]);
    $detail = $ds->fetch();
}

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
<div class="dashboard-layout">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo"><span>🏙️</span> SmartCity</div>
    <nav class="sidebar-menu">
      <div class="sidebar-section">Main Menu</div>
      <a href="<?= APP_URL ?>/dashboard.php"><span class="menu-icon">📊</span> Dashboard</a>
      <a href="<?= APP_URL ?>/complaint.php"><span class="menu-icon">📝</span> File Complaint</a>
      <a href="<?= APP_URL ?>/my-complaints.php" class="active"><span class="menu-icon">📋</span> My Complaints</a>
      <a href="<?= APP_URL ?>/track.php"><span class="menu-icon">🔍</span> Track Complaint</a>
      <div class="sidebar-section">Account</div>
      <a href="<?= APP_URL ?>/logout.php"><span class="menu-icon">🚪</span> Logout</a>
    </nav>
  </aside>

  <!-- Main -->
  <section class="main-content">
    <?= renderFlash() ?>

    <div class="page-header">
      <h1>📋 My Complaints</h1>
      <a href="<?= APP_URL ?>/complaint.php" class="btn btn-primary btn-sm">+ New Complaint</a>
    </div>

    <!-- Filters -->
    <form method="GET" action="my-complaints.php">
      <div class="filter-bar">
        <div class="form-group" style="margin-bottom:0;">
          <label style="font-size:.8rem;">Search</label>
          <input type="text" name="search" class="form-control"
                 placeholder="Title or Tracking ID" value="<?= e($search) ?>">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label style="font-size:.8rem;">Status</label>
          <select name="status" class="form-control">
            <?php foreach ($allowedStatuses as $s): ?>
              <option value="<?= e($s) ?>" <?= $filterStatus === $s ? 'selected' : '' ?>>
                <?= $s === '' ? 'All Statuses' : e($s) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label style="font-size:.8rem;">Category</label>
          <select name="category" class="form-control">
            <?php foreach ($categories as $cat): ?>
              <option value="<?= e($cat) ?>" <?= $filterCategory === $cat ? 'selected' : '' ?>>
                <?= $cat === '' ? 'All Categories' : e($cat) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="my-complaints.php" class="btn btn-sm" style="background:#f3f4f6;border:1px solid var(--border);">Reset</a>
      </div>
    </form>

    <!-- Table -->
    <div class="card">
      <div class="card-header">
        <h3>📋 Complaints (<?= $total ?>)</h3>
      </div>
      <div class="card-body" style="padding:0;">
        <?php if (empty($complaints)): ?>
          <div style="padding:2.5rem;text-align:center;color:var(--text-muted);">
            <div style="font-size:2.5rem;margin-bottom:.5rem;">📭</div>
            <p>No complaints found.</p>
            <a href="<?= APP_URL ?>/complaint.php" class="btn btn-primary btn-sm" style="margin-top:.75rem;">File a Complaint</a>
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Tracking ID</th>
                  <th>Title</th>
                  <th>Category</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($complaints as $i => $c): ?>
                <tr>
                  <td><?= $offset + $i + 1 ?></td>
                  <td><code style="font-size:.8rem;"><?= e($c['tracking_id']) ?></code></td>
                  <td><?= e(mb_strimwidth($c['title'],0,50,'…')) ?></td>
                  <td style="font-size:.82rem;"><?= e($c['category']) ?></td>
                  <td style="white-space:nowrap;"><?= date('d M Y', strtotime($c['complaint_date'])) ?></td>
                  <td><?= statusBadge($c['status']) ?></td>
                  <td>
                    <a href="my-complaints.php?view=<?= $c['id'] ?>&<?= http_build_query(['status'=>$filterStatus,'category'=>$filterCategory,'search'=>$search,'page'=>$page]) ?>"
                       class="btn btn-outline btn-sm">View</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php if ($pg['pages'] > 1): ?>
          <div class="pagination" style="padding:1rem;">
            <?php
            $q = http_build_query(['status'=>$filterStatus,'category'=>$filterCategory,'search'=>$search]);
            if ($pg['current'] > 1): ?>
              <a href="?page=<?= $pg['current']-1 ?>&<?= $q ?>">‹ Prev</a>
            <?php else: ?><span class="disabled">‹ Prev</span><?php endif; ?>
            <?php for ($p=1; $p<=$pg['pages']; $p++): ?>
              <?php if ($p === $pg['current']): ?>
                <span class="current"><?= $p ?></span>
              <?php else: ?>
                <a href="?page=<?= $p ?>&<?= $q ?>"><?= $p ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <?php if ($pg['current'] < $pg['pages']): ?>
              <a href="?page=<?= $pg['current']+1 ?>&<?= $q ?>">Next ›</a>
            <?php else: ?><span class="disabled">Next ›</span><?php endif; ?>
          </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

  </section>
</div>
</main>

<!-- Detail Modal -->
<?php if ($detail): ?>
<div class="modal-overlay open" id="complaint-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
  <div class="modal-box">
    <div class="modal-header">
      <h3 id="modal-title">📋 Complaint: <?= e($detail['tracking_id']) ?></h3>
      <a href="my-complaints.php" class="modal-close" aria-label="Close">×</a>
    </div>
    <div class="modal-body">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem;">
        <div><?= statusBadge($detail['status']) ?></div>
        <div style="font-size:.82rem;color:var(--text-muted);">Filed: <?= date('d M Y', strtotime($detail['created_at'])) ?></div>
      </div>

      <div class="detail-grid">
        <div class="detail-item"><label>Category</label><p><?= e($detail['category']) ?></p></div>
        <div class="detail-item"><label>Date of Issue</label><p><?= date('d M Y', strtotime($detail['complaint_date'])) ?></p></div>
        <div class="detail-item" style="grid-column:1/-1;"><label>Title</label><p><?= e($detail['title']) ?></p></div>
        <div class="detail-item" style="grid-column:1/-1;"><label>Description</label><p style="font-weight:400;color:var(--text-muted);"><?= nl2br(e($detail['description'])) ?></p></div>
        <div class="detail-item" style="grid-column:1/-1;"><label>Location</label><p><?= e($detail['city_area']) ?></p></div>
        <?php if ($detail['admin_remarks']): ?>
        <div class="detail-item" style="grid-column:1/-1;">
          <label>Admin Remarks</label>
          <div class="alert alert-info" style="margin:0;"><?= nl2br(e($detail['admin_remarks'])) ?></div>
        </div>
        <?php endif; ?>
      </div>

      <?php if ($detail['photo']): ?>
      <div style="margin-top:1rem;">
        <label style="font-size:.78rem;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);">Photo</label>
        <img src="<?= UPLOAD_URL . e($detail['photo']) ?>" alt="Complaint photo"
             style="max-height:220px;border-radius:8px;border:1px solid var(--border);margin-top:.5rem;width:auto;">
      </div>
      <?php endif; ?>
    </div>
    <div class="modal-footer">
      <a href="<?= APP_URL ?>/track.php?id=<?= urlencode($detail['tracking_id']) ?>" class="btn btn-blue btn-sm">🔍 Full Track View</a>
      <a href="my-complaints.php" class="btn btn-sm modal-cancel" style="background:#f3f4f6;border:1px solid var(--border);">Close</a>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
