<?php
/**
 * TUMSDA Admin — Password Reset Utility
 * Access via: http://localhost/tum/tumsdachurch.org/reset_admin.php
 *
 * DELETE THIS FILE after you've reset your password!
 */

require_once __DIR__ . '/db_connect.php';

$db = getPublicDB();
$message = '';
$error   = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $stmt = $db->prepare('SELECT id, name, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "No account found for <strong>{$email}</strong>.";
        } else {
            $hash = password_hash($password, PASSWORD_ARGON2ID);
            $upd  = $db->prepare('UPDATE users SET password_hash = ?, is_active = 1 WHERE email = ?');
            $upd->execute([$hash, $email]);
            $message = "✅ Password reset for <strong>{$user['name']}</strong> ({$user['role']}). You can now <a href='http://localhost:5173/login' style='color:#2563eb'>log in</a>. <br><strong>Delete this file now!</strong>";
        }
    }
}

// List all users
$users = $db->query('SELECT id, name, email, role, is_active, created_at FROM users ORDER BY id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>TUMSDA — Admin Password Reset</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: system-ui, sans-serif; background: #f1f5f9; display: flex; min-height: 100vh; align-items: flex-start; justify-content: center; padding: 2rem; }
    .card { background: #fff; border-radius: 1rem; padding: 2rem; width: 100%; max-width: 560px; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
    h1 { font-size: 1.4rem; margin-bottom: .25rem; color: #0f172a; }
    p.sub { font-size: .875rem; color: #64748b; margin-bottom: 1.5rem; }
    .warn { background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; border-radius: .5rem; padding: .75rem 1rem; font-size: .85rem; margin-bottom: 1.5rem; }
    .success { background: #dcfce7; border: 1px solid #16a34a; color: #166534; border-radius: .5rem; padding: .75rem 1rem; font-size: .9rem; margin-bottom: 1.5rem; }
    .err { background: #fee2e2; border: 1px solid #dc2626; color: #7f1d1d; border-radius: .5rem; padding: .75rem 1rem; font-size: .9rem; margin-bottom: 1.5rem; }
    label { display: block; font-size: .8rem; font-weight: 600; color: #475569; margin-bottom: .35rem; margin-top: 1rem; }
    input { width: 100%; padding: .6rem .9rem; border: 1px solid #cbd5e1; border-radius: .5rem; font-size: .9rem; outline: none; }
    input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
    button { width: 100%; margin-top: 1.25rem; padding: .75rem; background: #2563eb; color: #fff; border: none; border-radius: .5rem; font-size: 1rem; font-weight: 600; cursor: pointer; }
    button:hover { background: #1d4ed8; }
    hr { border: none; border-top: 1px solid #e2e8f0; margin: 1.5rem 0; }
    table { width: 100%; border-collapse: collapse; font-size: .82rem; }
    th { text-align: left; padding: .4rem .5rem; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
    td { padding: .4rem .5rem; border-bottom: 1px solid #f1f5f9; color: #0f172a; }
    .badge { display: inline-block; padding: .15rem .5rem; border-radius: .25rem; font-size: .7rem; font-weight: 700; }
    .admin { background: #dbeafe; color: #1e40af; }
    .member { background: #f1f5f9; color: #64748b; }
    .active { color: #16a34a; }
    .inactive { color: #dc2626; }
  </style>
</head>
<body>
<div class="card">
  <h1>🔐 Admin Password Reset</h1>
  <p class="sub">Use this to recover access to the TUMSDA admin panel.</p>

  <div class="warn">⚠️ <strong>Security notice:</strong> Delete <code>reset_admin.php</code> immediately after use.</div>

  <?php if ($message): ?>
    <div class="success"><?= $message ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="err"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <label for="email">Email Address</label>
    <input id="email" name="email" type="email" required placeholder="admin@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

    <label for="password">New Password (min 8 chars)</label>
    <input id="password" name="password" type="password" required placeholder="New password">

    <label for="confirm">Confirm New Password</label>
    <input id="confirm" name="confirm" type="password" required placeholder="Repeat password">

    <button type="submit">Reset Password</button>
  </form>

  <hr>
  <p style="font-size:.8rem;font-weight:600;color:#64748b;margin-bottom:.75rem;">EXISTING ACCOUNTS</p>
  <?php if (empty($users)): ?>
    <p style="font-size:.85rem;color:#94a3b8;">No users found. Use the admin Register page to create the first account.</p>
  <?php else: ?>
    <table>
      <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th></tr>
      <?php foreach ($users as $u): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><span class="badge <?= $u['role'] ?>"><?= $u['role'] ?></span></td>
        <td class="<?= $u['is_active'] ? 'active' : 'inactive' ?>"><?= $u['is_active'] ? 'Yes' : 'No' ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>
</body>
</html>
