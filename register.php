<?php
require_once 'includes/config.php';
if (isLoggedIn()) redirect('index.php');
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — LibraNova</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card" style="max-width:520px">
    <div class="auth-logo">
      <div style="font-size:2.5rem;margin-bottom:0.5rem">📚</div>
      <h2>Libra<span>Nova</span></h2>
      <p>Create your library membership</p>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>">
      <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="includes/auth.php">
      <input type="hidden" name="action" value="register">
      
      <div class="form-group">
        <label class="form-label">Full Name *</label>
        <input type="text" name="name" class="form-control" placeholder="John Doe" required minlength="2" maxlength="100">
      </div>

      <div class="form-group">
        <label class="form-label">Email Address *</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label class="form-label">Password * <small id="pw-strength" style="float:right"></small></label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Min. 6 characters" required minlength="6">
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password *</label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Phone Number</label>
        <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 000-0000" maxlength="20">
      </div>

      <div class="form-group">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" placeholder="Your address (optional)" style="min-height:70px"></textarea>
      </div>

      <button type="submit" class="btn btn-primary w-100">
        ✨ Create Account
      </button>
    </form>

    <div class="auth-divider">or</div>

    <div style="text-align:center">
      <p style="color:var(--text-mid);font-size:0.875rem">
        Already have an account? 
        <a href="login.php" style="color:var(--gold);font-weight:600">Sign in</a>
      </p>
    </div>

    <div style="text-align:center;margin-top:1rem">
      <a href="index.php" style="font-size:0.85rem;color:var(--text-light)">← Back to Home</a>
    </div>
  </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
