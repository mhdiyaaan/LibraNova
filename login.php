<?php
require_once 'includes/config.php';
if (isLoggedIn()) redirect(isAdmin() ? 'admin/dashboard.php' : 'index.php');
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — LibraNova</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <div style="font-size:2.5rem;margin-bottom:0.5rem">📚</div>
      <h2>Libra<span>Nova</span></h2>
      <p>Sign in to your library account</p>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>">
      <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="includes/auth.php">
      <input type="hidden" name="action" value="login">
      
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required autofocus>
      </div>
      
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
      </div>
      
      <button type="submit" class="btn btn-primary w-100" style="margin-top:0.5rem">
        🔑 Sign In
      </button>
    </form>

    <div class="auth-divider">or</div>

    <div style="text-align:center">
      <p style="color:var(--text-mid);font-size:0.875rem">
        Don't have an account? 
        <a href="register.php" style="color:var(--gold);font-weight:600">Register here</a>
      </p>
    </div>

    <div style="margin-top:1.5rem;padding:1rem;background:var(--cream);border-radius:var(--radius);font-size:0.8rem;color:var(--text-light)">
      <strong style="color:var(--text-mid)">Demo Credentials:</strong><br>
      Admin: admin@library.com / Admin@123
    </div>

    <div style="text-align:center;margin-top:1rem">
      <a href="index.php" style="font-size:0.85rem;color:var(--text-light)">← Back to Home</a>
    </div>
  </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
