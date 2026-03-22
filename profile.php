<?php
require_once 'includes/config.php';
require_once 'includes/books.php';

if (!isLoggedIn()) redirect('login.php');
if (isAdmin()) redirect('admin/dashboard.php');

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$conn->close();

$issuedBooks = getUserIssuedBooks($_SESSION['user_id']);
$activeBooks = array_filter($issuedBooks, fn($b) => $b['status'] === 'issued');
$returnedBooks = array_filter($issuedBooks, fn($b) => $b['status'] === 'returned');
$overdueBooks = array_filter($issuedBooks, fn($b) => $b['display_status'] === 'overdue');

$flash = getFlash();
$initials = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', trim($user['name'])))));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile — LibraNova</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="navbar-brand"><div class="logo-icon">📚</div>Libra<span>Nova</span></a>
  <button class="navbar-toggle">☰</button>
  <ul class="navbar-nav">
    <li><a href="index.php" class="nav-link">🏠 Home</a></li>
    <li><a href="books.php" class="nav-link">📖 Books</a></li>
    <li><a href="profile.php" class="nav-link active">👤 My Profile</a></li>
    <li>
      <form method="POST" action="includes/auth.php" style="display:inline">
        <input type="hidden" name="action" value="logout">
        <button type="submit" class="btn btn-outline-primary btn-sm">Logout</button>
      </form>
    </li>
  </ul>
</nav>

<!-- PROFILE HEADER -->
<div class="profile-header">
  <div class="container">
    <div style="display:flex;align-items:center;gap:1.5rem">
      <div class="avatar"><?= substr($initials, 0, 2) ?></div>
      <div>
        <h1 style="color:white;margin-bottom:0.25rem"><?= htmlspecialchars($user['name']) ?></h1>
        <p style="color:rgba(255,255,255,0.7);margin:0"><?= htmlspecialchars($user['email']) ?></p>
        <p style="color:rgba(255,255,255,0.5);font-size:0.85rem;margin-top:0.25rem">
          📅 Member since <?= date('F Y', strtotime($user['membership_date'])) ?>
        </p>
      </div>
    </div>
    <div style="display:flex;gap:2rem;margin-top:1.5rem">
      <div style="text-align:center">
        <div style="font-size:1.5rem;font-weight:700;color:var(--gold)"><?= count($activeBooks) ?></div>
        <div style="font-size:0.8rem;color:rgba(255,255,255,0.6)">Currently Borrowed</div>
      </div>
      <div style="text-align:center">
        <div style="font-size:1.5rem;font-weight:700;color:var(--gold)"><?= count($returnedBooks) ?></div>
        <div style="font-size:0.8rem;color:rgba(255,255,255,0.6)">Returned</div>
      </div>
      <div style="text-align:center">
        <div style="font-size:1.5rem;font-weight:700;color:<?= count($overdueBooks) > 0 ? '#e74c3c' : 'var(--gold)' ?>"><?= count($overdueBooks) ?></div>
        <div style="font-size:0.8rem;color:rgba(255,255,255,0.6)">Overdue</div>
      </div>
    </div>
  </div>
</div>

<div class="main-content">
  <div class="container">

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <?php if (count($overdueBooks) > 0): ?>
    <div class="fine-banner">
      <div class="icon">⚠️</div>
      <div>
        <strong>You have <?= count($overdueBooks) ?> overdue book(s)!</strong>
        <div style="font-size:0.875rem;color:var(--text-mid)">Please return them to avoid additional fines.</div>
      </div>
    </div>
    <?php endif; ?>

    <div class="grid-2" style="align-items:start;gap:2rem">

      <!-- CURRENTLY BORROWED -->
      <div>
        <div class="table-wrapper">
          <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);background:linear-gradient(to right,var(--navy),var(--navy-mid));color:white;border-radius:var(--radius-lg) var(--radius-lg) 0 0">
            <h3 style="color:white;margin:0">📤 Currently Borrowed (<?= count($activeBooks) ?>)</h3>
          </div>
          <?php if (empty($activeBooks)): ?>
          <div class="empty-state" style="padding:2rem">
            <div class="empty-icon">📭</div>
            <p>No books currently borrowed.</p>
            <a href="books.php" class="btn btn-primary btn-sm">Browse Books</a>
          </div>
          <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>Book</th>
                <th>Due Date</th>
                <th>Fine</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($activeBooks as $ib): ?>
              <?php
                $isOverdue = $ib['display_status'] === 'overdue';
                $daysOverdue = max(0, $ib['days_overdue'] ?? 0);
                $pendingFine = $isOverdue ? $daysOverdue * FINE_PER_DAY : 0;
              ?>
              <tr>
                <td>
                  <div style="font-weight:600;color:var(--navy)"><?= htmlspecialchars($ib['title']) ?></div>
                  <div style="font-size:0.8rem;color:var(--text-light)"><?= htmlspecialchars($ib['author']) ?></div>
                </td>
                <td>
                  <span class="due-date <?= $isOverdue ? 'badge badge-danger' : (strtotime($ib['due_date']) - time() < 3*86400 ? 'badge badge-warning' : '') ?>"
                    data-date="<?= $ib['due_date'] ?>">
                    <?= date('M j, Y', strtotime($ib['due_date'])) ?>
                  </span>
                  <?php if ($isOverdue): ?>
                    <div style="font-size:0.75rem;color:var(--danger);margin-top:2px"><?= $daysOverdue ?> days late</div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($pendingFine > 0): ?>
                    <span style="color:var(--danger);font-weight:600">$<?= number_format($pendingFine, 2) ?></span>
                  <?php else: ?>
                    <span style="color:var(--text-light)">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <button onclick="returnBook(<?= $ib['id'] ?>, '<?= addslashes(htmlspecialchars($ib['title'])) ?>')"
                    class="btn btn-success btn-sm">↩ Return</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>

      <!-- USER INFO + HISTORY -->
      <div>
        <!-- Account Info -->
        <div class="card mb-3">
          <div class="card-header"><h3>👤 Account Information</h3></div>
          <div class="card-body">
            <table style="width:100%;border:none">
              <tr style="border:none"><td style="padding:0.5rem 0;color:var(--text-light);font-size:0.85rem;border:none">Phone</td><td style="border:none;font-size:0.9rem"><?= htmlspecialchars($user['phone'] ?: '—') ?></td></tr>
              <tr style="border:none"><td style="padding:0.5rem 0;color:var(--text-light);font-size:0.85rem;border:none">Address</td><td style="border:none;font-size:0.9rem"><?= htmlspecialchars($user['address'] ?: '—') ?></td></tr>
              <tr style="border:none"><td style="padding:0.5rem 0;color:var(--text-light);font-size:0.85rem;border:none">Status</td><td style="border:none"><span class="badge badge-success">Active</span></td></tr>
            </table>
          </div>
        </div>

        <!-- Borrowing Rules -->
        <div class="card" style="background:linear-gradient(135deg,var(--navy),var(--navy-mid));border:none">
          <div class="card-body" style="color:white">
            <h4 style="color:var(--gold);margin-bottom:1rem">📋 Library Rules</h4>
            <ul style="list-style:none;display:flex;flex-direction:column;gap:0.6rem">
              <li style="color:rgba(255,255,255,0.8);font-size:0.875rem">📚 Maximum <strong style="color:white">3 books</strong> at a time</li>
              <li style="color:rgba(255,255,255,0.8);font-size:0.875rem">📅 Loan period: <strong style="color:white"><?= LOAN_DAYS ?> days</strong></li>
              <li style="color:rgba(255,255,255,0.8);font-size:0.875rem">💰 Late fine: <strong style="color:white">$<?= FINE_PER_DAY ?>/day</strong></li>
              <li style="color:rgba(255,255,255,0.8);font-size:0.875rem">🔄 Returns accepted at any time</li>
            </ul>
          </div>
        </div>
      </div>

    </div>

    <!-- BORROWING HISTORY -->
    <?php if (!empty($returnedBooks)): ?>
    <div class="table-wrapper mt-3">
      <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);background:linear-gradient(to right,var(--navy),var(--navy-mid));border-radius:var(--radius-lg) var(--radius-lg) 0 0">
        <h3 style="color:white;margin:0">📜 Borrowing History (<?= count($returnedBooks) ?>)</h3>
      </div>
      <table>
        <thead>
          <tr>
            <th>Book</th>
            <th>Issued</th>
            <th>Due Date</th>
            <th>Returned</th>
            <th>Fine</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($returnedBooks as $ib): ?>
          <tr>
            <td>
              <div style="font-weight:600"><?= htmlspecialchars($ib['title']) ?></div>
              <div style="font-size:0.8rem;color:var(--text-light)"><?= htmlspecialchars($ib['author']) ?></div>
            </td>
            <td><?= date('M j, Y', strtotime($ib['issue_date'])) ?></td>
            <td><?= date('M j, Y', strtotime($ib['due_date'])) ?></td>
            <td><?= $ib['return_date'] ? date('M j, Y', strtotime($ib['return_date'])) : '—' ?></td>
            <td>
              <?php if ($ib['fine_amount'] > 0): ?>
                <span style="color:var(--danger)">$<?= number_format($ib['fine_amount'], 2) ?></span>
              <?php else: ?>
                <span style="color:var(--success)">No fine</span>
              <?php endif; ?>
            </td>
            <td><span class="badge badge-success">✓ Returned</span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <div style="margin-top:2rem;text-align:center">
      <a href="books.php" class="btn btn-primary">Browse More Books →</a>
    </div>

  </div>
</div>

<footer>
  <p>© <?= date('Y') ?> <span>LibraNova</span> — Modern Library Management System</p>
</footer>

<div class="toast-container"></div>
<script src="js/main.js"></script>
</body>
</html>
