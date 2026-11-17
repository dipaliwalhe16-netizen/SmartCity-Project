<?php
$pageTitle = 'Smart City Citizen Complaint Portal';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" aria-labelledby="hero-heading">
  <div class="hero-content">
    <div class="hero-badge">🏙️ Smart City Initiative</div>
    <h1 id="hero-heading">Your Voice Builds a Better City</h1>
    <p>Register civic complaints online, track their resolution in real time, and hold your city administration accountable – all in one place.</p>
    <div class="hero-actions">
      <a href="<?= APP_URL ?>/complaint.php" class="btn btn-primary">📝 File a Complaint</a>
      <a href="<?= APP_URL ?>/track.php"     class="btn btn-secondary">🔍 Track Complaint</a>
    </div>
  </div>
</section>

<!-- Stats bar -->
<?php
require_once __DIR__ . '/includes/db.php';
try {
    $db    = getDB();
    $total = $db->query("SELECT COUNT(*) FROM complaints")->fetchColumn();
    $pend  = $db->query("SELECT COUNT(*) FROM complaints WHERE status='Pending'")->fetchColumn();
    $prog  = $db->query("SELECT COUNT(*) FROM complaints WHERE status='In Progress'")->fetchColumn();
    $res   = $db->query("SELECT COUNT(*) FROM complaints WHERE status='Resolved'")->fetchColumn();
} catch (Exception $e) {
    $total = $pend = $prog = $res = '–';
}
?>
<section class="section" style="padding-top:2rem;padding-bottom:2rem;background:var(--white);">
  <div class="container">
    <div class="stats-grid" style="max-width:900px;margin:0 auto;">
      <div class="stat-card blue">
        <div class="stat-icon">📋</div>
        <div class="stat-info">
          <div class="stat-value"><?= htmlspecialchars((string)$total) ?></div>
          <div class="stat-label">Total Complaints</div>
        </div>
      </div>
      <div class="stat-card orange">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
          <div class="stat-value"><?= htmlspecialchars((string)$pend) ?></div>
          <div class="stat-label">Pending</div>
        </div>
      </div>
      <div class="stat-card blue" style="border-left-color:#3b82f6;">
        <div class="stat-icon">🔧</div>
        <div class="stat-info">
          <div class="stat-value"><?= htmlspecialchars((string)$prog) ?></div>
          <div class="stat-label">In Progress</div>
        </div>
      </div>
      <div class="stat-card green">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
          <div class="stat-value"><?= htmlspecialchars((string)$res) ?></div>
          <div class="stat-label">Resolved</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- How it works -->
<section class="section">
  <div class="container">
    <div class="section-title">
      <h2>How It Works</h2>
      <p>Three easy steps to get your civic issue resolved</p>
      <div class="section-divider"></div>
    </div>
    <div class="features-grid" style="max-width:900px;margin:0 auto;">
      <div class="feature-card">
        <div class="feature-icon">📝</div>
        <h3>1. Register &amp; Login</h3>
        <p>Create a free citizen account to submit and track your complaints securely.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">📤</div>
        <h3>2. File Complaint</h3>
        <p>Describe your civic issue, attach a photo, and submit. Receive a unique tracking ID instantly.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🔍</div>
        <h3>3. Track Progress</h3>
        <p>Use your tracking ID to monitor resolution status in real time – no login required.</p>
      </div>
    </div>
  </div>
</section>

<!-- Complaint categories -->
<section class="section" style="background:var(--white);">
  <div class="container">
    <div class="section-title">
      <h2>Complaint Categories</h2>
      <p>We handle a wide range of civic issues</p>
      <div class="section-divider"></div>
    </div>
    <div class="features-grid">
      <?php
      $cats = [
        ['🛣️','Roads & Footpaths','Potholes, damaged footpaths, road cave-ins'],
        ['💧','Water Supply','No water, contaminated supply, leakage'],
        ['⚡','Electricity','Power outages, faulty poles, transformer issues'],
        ['🗑️','Garbage & Sanitation','Overflowing bins, illegal dumping, cleaning'],
        ['🚰','Drainage & Sewage','Blocked drains, sewage overflow, flooding'],
        ['💡','Street Lights','Faulty, missing, or dim street lighting'],
        ['🌳','Parks & Gardens','Damaged park equipment, overgrown vegetation'],
        ['🚌','Public Transport','Bus shelter damage, route issues, stops'],
        ['🔊','Noise Pollution','Illegal construction noise, events, industries'],
        ['🏗️','Illegal Construction','Unauthorised buildings and encroachments'],
        ['ℹ️','Other','Any other civic grievance not listed above'],
      ];
      foreach ($cats as [$icon, $title, $desc]):
      ?>
      <div class="feature-card">
        <div class="feature-icon"><?= $icon ?></div>
        <h3><?= htmlspecialchars($title) ?></h3>
        <p><?= htmlspecialchars($desc) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section" style="background:linear-gradient(135deg,var(--blue-dark),var(--green-dark));color:var(--white);text-align:center;">
  <div class="container" style="max-width:700px;">
    <div style="font-size:2.5rem;margin-bottom:1rem;">🏙️</div>
    <h2 style="font-size:clamp(1.4rem,3vw,2rem);font-weight:800;margin-bottom:.75rem;">Ready to Make a Difference?</h2>
    <p style="opacity:.9;margin-bottom:2rem;">Join thousands of citizens already using the Smart City Portal to improve their communities.</p>
    <div class="hero-actions">
      <a href="<?= APP_URL ?>/register.php" class="btn btn-primary">Get Started Free</a>
      <a href="<?= APP_URL ?>/track.php"    class="btn btn-secondary">Track Existing Complaint</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
