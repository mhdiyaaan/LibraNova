<?php
require_once '../includes/config.php';
require_once '../includes/books.php';

if (!isAdmin()) redirect('../login.php');

$returnedBooks = getAllIssuedBooks('returned');
$flash = getFlash();

$totalFines = array_sum(array_column($returnedBooks, 'fine_amount'));
$booksWithFines = count(array_filter($returnedBooks, fn($b) => $b['fine_amount'] > 0));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Returned Books — LibraNova Admin</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="../index.php" class="navbar-brand"><div class="logo-icon">📚</div>Libra<span>Nova</span> <small style="font-size:0.65rem;background:var(--gold);padding:2px 6px;border-radius:4px;margin-left:6px">ADMIN</small></a>
  <ul class="navbar-nav">
    <li><a href="../index.php" class="nav-link">🏠 Site</a></li>
    <li><form method="POST" action="../includes/auth.php" style="display:inline"><input type="hidden" name="action" value="logout"><button type="submit" class="btn btn-outline-primary btn-sm">Logout</button></form></li>
  </ul>
</nav>

<div class="admin-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Main</div>
      <a href="dashboard.php" class="sidebar-link"><span class="icon">📊</span> Dashboard</a>
      <a href="books.php" class="sidebar-link"><span class="icon">📚</span> Manage Books</a>
      <a href="users.php" class="sidebar-link"><span class="icon">👥</span> Manage Users</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Circulation</div>
      <a href="issued.php" class="sidebar-link"><span class="icon">📤</span> Issued Books</a>
      <a href="returned.php" class="sidebar-link active"><span class="icon">↩️</span> Returned Books</a>
      <a href="overdue.php" class="sidebar-link"><span class="icon">⚠️</span> Overdue Books</a>
    </div>
  </aside>

  <main class="admin-content">
    <div style="margin-bottom:1.5rem">
      <h2>↩️ Returned Books</h2>
      <p><?= count($returnedBooks) ?> total returns</p>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <!-- SUMMARY CARDS -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem">
      <div class="stat-card">
        <div class="stat-icon returned">↩️</div>
        <div>
          <div class="stat-number"><?= count($returnedBooks) ?></div>
          <div class="stat-label">Total Returns</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon fines">💰</div>
        <div>
          <div class="stat-number">$<?= number_format($totalFines, 2) ?></div>
          <div class="stat-label">Fines Collected</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon overdue">⚠️</div>
        <div>
          <div class="stat-number"><?= $booksWithFines ?></div>
          <div class="stat-label">Returns with Fines</div>
        </div>
      </div>
    </div>

    <div class="search-section">
      <input type="text" id="table-search" class="form-control" placeholder="🔍 Filter by user or book...">
    </div>

    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Book</th>
            <th>Issued</th>
            <th>Due Date</th>
            <th>Returned</th>
            <th>Fine</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($returnedBooks)): ?>
          <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-light)">No returns yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($returnedBooks as $ib): ?>
          <?php $wasLate = !empty($ib['return_date']) && $ib['return_date'] > $ib['due_date']; ?>
          <tr class="searchable-row">
            <td style="color:var(--text-light)">#<?= $ib['id'] ?></td>
            <td>
              <div style="font-weight:600;font-size:0.875rem"><?= htmlspecialchars($ib['user_name']) ?></div>
              <div style="font-size:0.78rem;color:var(--text-light)"><?= htmlspecialchars($ib['user_email']) ?></div>
            </td>
            <td>
              <div style="font-weight:600;font-size:0.875rem"><?= htmlspecialchars($ib['book_title']) ?></div>
              <div style="font-size:0.78rem;color:var(--text-light);font-style:italic"><?= htmlspecialchars($ib['author']) ?></div>
            </td>
            <td style="font-size:0.875rem"><?= date('M j, Y', strtotime($ib['issue_date'])) ?></td>
            <td style="font-size:0.875rem"><?= date('M j, Y', strtotime($ib['due_date'])) ?></td>
            <td style="font-size:0.875rem">
              <?= $ib['return_date'] ? date('M j, Y', strtotime($ib['return_date'])) : '—' ?>
              <?php if ($wasLate): ?>
                <div style="font-size:0.72rem;color:var(--danger)">
                  <?= (int)((strtotime($ib['return_date']) - strtotime($ib['due_date'])) / 86400) ?> days late
                </div>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($ib['fine_amount'] > 0): ?>
                <span style="color:var(--danger);font-weight:700">$<?= number_format($ib['fine_amount'], 2) ?></span>
              <?php else: ?>
                <span style="color:var(--success)">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($wasLate): ?>
                <span class="badge badge-warning">⚠️ Late Return</span>
              <?php else: ?>
                <span class="badge badge-success">✓ On Time</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<div class="toast-container"></div>
<script src="../js/main.js"></script>
</body>
</html>
