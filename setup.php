<?php
// ============================================================
// Smart City Portal — One-time Setup / Password Fix
// Run ONCE after importing database.sql
// URL: http://localhost/smart-city-portal/setup.php
// DELETE this file from production after use.
// ============================================================
require_once __DIR__ . '/config/db.php';

$db = getDB();

// Demo accounts with correct passwords
$accounts = [
    ['admin@smartcity.gov.in',    'Admin@123',    'Admin'],
    ['sanitation@smartcity.gov.in','Dept@123',    'Sanitation Dept'],
    ['roads@smartcity.gov.in',    'Dept@123',     'Roads Dept'],
    ['water@smartcity.gov.in',    'Dept@123',     'Water Dept'],
    ['drainage@smartcity.gov.in', 'Dept@123',     'Drainage Dept'],
    ['lighting@smartcity.gov.in', 'Dept@123',     'Lighting Dept'],
    ['rahul@citizen.com',         'Citizen@123',  'Citizen - Rahul'],
    ['priya@citizen.com',         'Citizen@123',  'Citizen - Priya'],
    ['amit@citizen.com',          'Citizen@123',  'Citizen - Amit'],
];

$stmt = $db->prepare('UPDATE users SET password = ? WHERE email = ?');
$results = [];
foreach ($accounts as [$email, $plain, $label]) {
    $hash = password_hash($plain, PASSWORD_BCRYPT);
    $stmt->execute([$hash, $email]);
    $results[] = ['label'=>$label,'email'=>$email,'password'=>$plain,'rows'=>$stmt->rowCount()];
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Setup — Smart City Portal</title>
<style>
  body{font-family:sans-serif;background:#f0f4f8;padding:30px;margin:0}
  .box{background:#fff;border-radius:12px;padding:32px;max-width:700px;
       margin:0 auto;box-shadow:0 4px 20px rgba(0,0,0,.1)}
  h2{color:#1a3c6b;margin-bottom:20px}
  table{width:100%;border-collapse:collapse;margin-top:16px}
  th,td{border:1px solid #dee2e6;padding:10px 14px;text-align:left;font-size:.88rem}
  th{background:#1a3c6b;color:#fff}
  .ok{color:green;font-weight:700} .warn{color:orange;font-weight:700}
  .btn{display:inline-block;margin-top:20px;padding:10px 24px;
       background:#1a3c6b;color:#fff;border-radius:6px;text-decoration:none}
  .danger{margin-top:20px;padding:14px;background:#fff3cd;border:1px solid #ffc107;
          border-radius:8px;font-size:.875rem;color:#664d03}
</style>
</head>
<body>
<div class="box">
  <h2>🔐 Smart City Portal — Setup Complete</h2>
  <p>Passwords have been set for all demo accounts.</p>
  <table>
    <thead><tr><th>Account</th><th>Email</th><th>Password</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($results as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['label']) ?></td>
      <td><?= htmlspecialchars($r['email']) ?></td>
      <td><code><?= htmlspecialchars($r['password']) ?></code></td>
      <td class="<?= $r['rows']>0?'ok':'warn' ?>">
        <?= $r['rows']>0 ? '✅ Updated' : '⚠️ Not found' ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <a href="index.php" class="btn">→ Go to Portal</a>
  <div class="danger">
    ⚠️ <strong>Security Warning:</strong> Delete <code>setup.php</code> after use.
    It resets all passwords on every visit.
  </div>
</div>
</body>
</html>
