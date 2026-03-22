<?php
require_once '../includes/config.php';
require_once '../includes/books.php';

if (!isAdmin()) redirect('../login.php');

$stats = getDashboardStats();
$overdueList = getAllIssuedBooks('overdue');
$recentIssued = getAllIssuedBooks('issued');
$recentIssued = array_slice($recentIssued, 0, 5);
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — LibraNova</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="../index.php" class="navbar-brand"><div class="logo-icon">📚</div>Libra<span>Nova</span> <small style="font-size:0.65rem;background:var(--gold);padding:2px 6px;border-radius:4px;margin-left:6px">ADMIN</small></a>
  <button class="navbar-toggle">☰</button>
  <ul class="navbar-nav">
    <li><a href="../index.php" class="nav-link">🏠 Site</a></li>
    <li><span class="nav-link" style="color:var(--gold-light)">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
    <li>
      <form method="POST" action="../includes/auth.php" style="display:inline">
        <input type="hidden" name="action" value="logout">
        <button type="submit" class="btn btn-outline-primary btn-sm">Logout</button>
      </form>
    </li>
  </ul>
</nav>

<div class="admin-layout">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Main</div>
      <a href="dashboard.php" class="sidebar-link active"><span class="icon">📊</span> Dashboard</a>
      <a href="books.php" class="sidebar-link"><span class="icon">📚</span> Manage Books</a>
      <a href="users.php" class="sidebar-link"><span class="icon">👥</span> Manage Users</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Circulation</div>
      <a href="issued.php" class="sidebar-link"><span class="icon">📤</span> Issued Books</a>
      <a href="returned.php" class="sidebar-link"><span class="icon">↩️</span> Returned Books</a>
      <a href="overdue.php" class="sidebar-link"><span class="icon">⚠️</span> Overdue Books</a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="admin-content">
    <div style="margin-bottom:1.5rem">
      <h2>Dashboard Overview</h2>
      <p>Welcome back, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong> — <?= date('l, F j, Y') ?></p>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon books">📚</div>
        <div>
          <div class="stat-number"><?= number_format($stats['total_books']) ?></div>
          <div class="stat-label">Total Books</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon users">👥</div>
        <div>
          <div class="stat-number"><?= number_format($stats['total_users']) ?></div>
          <div class="stat-label">Registered Users</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon issued">📤</div>
        <div>
          <div class="stat-number"><?= number_format($stats['total_issued']) ?></div>
          <div class="stat-label">Currently Issued</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon returned">↩️</div>
        <div>
          <div class="stat-number"><?= number_format($stats['total_returned']) ?></div>
          <div class="stat-label">Returned Books</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon overdue">⚠️</div>
        <div>
          <div class="stat-number" style="<?= $stats['overdue_books'] > 0 ? 'color:var(--danger)' : '' ?>"><?= number_format($stats['overdue_books']) ?></div>
          <div class="stat-label">Overdue Books</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon fines">💰</div>
        <div>
          <div class="stat-number">$<?= number_format($stats['total_fines'], 0) ?></div>
          <div class="stat-label">Total Fines</div>
        </div>
      </div>
    </div>

    <div class="grid-2" style="align-items:start;gap:1.5rem">

      <!-- RECENTLY ISSUED -->
      <div class="table-wrapper">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);background:linear-gradient(to right,var(--navy),var(--navy-mid));border-radius:var(--radius-lg) var(--radius-lg) 0 0;display:flex;align-items:center;justify-content:space-between">
          <h3 style="color:white;margin:0">Recently Issued</h3>
          <a href="issued.php" style="color:var(--gold-light);font-size:0.85rem">View All →</a>
        </div>
        <?php if (empty($recentIssued)): ?>
        <div class="empty-state" style="padding:2rem">
          <div class="empty-icon">📭</div>
          <p>No books currently issued.</p>
        </div>
        <?php else: ?>
        <table>
          <thead>
            <tr><th>User</th><th>Book</th><th>Due</th><th>Action</th></tr>
          </thead>
          <tbody>
            <?php foreach ($recentIssued as $ib): ?>
            <tr>
              <td>
                <div style="font-weight:600;font-size:0.85rem"><?= htmlspecialchars($ib['user_name']) ?></div>
              </td>
              <td style="font-size:0.85rem"><?= htmlspecialchars(substr($ib['book_title'],0,30)) ?>...</td>
              <td>
                <?php $overdue = $ib['due_date'] < date('Y-m-d'); ?>
                <span class="badge <?= $overdue ? 'badge-danger' : 'badge-info' ?>">
                  <?= date('M j', strtotime($ib['due_date'])) ?>
                </span>
              </td>
              <td>
                <button onclick="returnBook(<?= $ib['id'] ?>, '<?= addslashes(htmlspecialchars($ib['book_title'])) ?>')" class="btn btn-success btn-sm">↩</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

      <!-- OVERDUE ALERTS -->
      <div class="table-wrapper">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);background:linear-gradient(to right,#c0392b,#e74c3c);border-radius:var(--radius-lg) var(--radius-lg) 0 0;display:flex;align-items:center;justify-content:space-between">
          <h3 style="color:white;margin:0">⚠️ Overdue Books (<?= count($overdueList) ?>)</h3>
          <a href="overdue.php" style="color:rgba(255,255,255,0.8);font-size:0.85rem">View All →</a>
        </div>
        <?php if (empty($overdueList)): ?>
        <div class="empty-state" style="padding:2rem">
          <div class="empty-icon">✅</div>
          <p>No overdue books!</p>
        </div>
        <?php else: ?>
        <table>
          <thead>
            <tr><th>User</th><th>Book</th><th>Days Late</th><th>Fine</th></tr>
          </thead>
          <tbody>
            <?php foreach (array_slice($overdueList, 0, 5) as $ib): ?>
            <?php $daysLate = (strtotime(date('Y-m-d')) - strtotime($ib['due_date'])) / 86400; $fine = $daysLate * FINE_PER_DAY; ?>
            <tr>
              <td style="font-size:0.85rem"><?= htmlspecialchars($ib['user_name']) ?></td>
              <td style="font-size:0.85rem"><?= htmlspecialchars(substr($ib['book_title'],0,25)) ?>...</td>
              <td><span class="badge badge-danger"><?= (int)$daysLate ?>d</span></td>
              <td style="color:var(--danger);font-weight:600">$<?= number_format($fine, 2) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

    </div>

    <!-- QUICK ACTIONS -->
    <div class="card mt-3">
      <div class="card-header"><h3>⚡ Quick Actions</h3></div>
      <div class="card-body" style="display:flex;flex-wrap:wrap;gap:1rem">
        <a href="books.php" class="btn btn-primary">📚 Add New Book</a>
        <a href="users.php" class="btn btn-navy">👥 View All Users</a>
        <a href="issued.php" class="btn btn-warning">📤 View Issued Books</a>
        <a href="overdue.php" class="btn btn-danger">⚠️ Handle Overdue</a>
        <a href="returned.php" class="btn btn-success">↩ View Returns</a>
      </div>
    </div>

  </main>
</div>

<div class="toast-container"></div>
<script src="../js/main.js"></script>
</body>
</html>
