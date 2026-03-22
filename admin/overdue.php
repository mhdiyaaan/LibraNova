<?php
require_once '../includes/config.php';
require_once '../includes/books.php';

if (!isAdmin()) redirect('../login.php');

$overdueBooks = getAllIssuedBooks('overdue');
$flash = getFlash();

$totalPendingFines = 0;
foreach ($overdueBooks as $b) {
    $daysLate = max(0, (strtotime(date('Y-m-d')) - strtotime($b['due_date'])) / 86400);
    $totalPendingFines += $daysLate * FINE_PER_DAY;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Overdue Books — LibraNova Admin</title>
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
      <a href="returned.php" class="sidebar-link"><span class="icon">↩️</span> Returned Books</a>
      <a href="overdue.php" class="sidebar-link active"><span class="icon">⚠️</span> Overdue Books</a>
    </div>
  </aside>

  <main class="admin-content">
    <div style="margin-bottom:1.5rem">
      <h2>⚠️ Overdue Books</h2>
      <p><?= count($overdueBooks) ?> book(s) past due date</p>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <?php if (count($overdueBooks) > 0): ?>
    <div style="background:linear-gradient(135deg,#c0392b,#e74c3c);border-radius:var(--radius-lg);padding:1.5rem;margin-bottom:1.5rem;color:white;display:flex;gap:2rem;flex-wrap:wrap">
      <div>
        <div style="font-size:2rem;font-weight:700;font-family:'Playfair Display',serif"><?= count($overdueBooks) ?></div>
        <div style="font-size:0.85rem;opacity:0.85">Overdue Books</div>
      </div>
      <div>
        <div style="font-size:2rem;font-weight:700;font-family:'Playfair Display',serif">$<?= number_format($totalPendingFines, 2) ?></div>
        <div style="font-size:0.85rem;opacity:0.85">Pending Fines (accumulating)</div>
      </div>
      <div>
        <div style="font-size:2rem;font-weight:700;font-family:'Playfair Display',serif">$<?= number_format(FINE_PER_DAY, 2) ?></div>
        <div style="font-size:0.85rem;opacity:0.85">Fine Per Day</div>
      </div>
    </div>
    <?php endif; ?>

    <div class="search-section">
      <input type="text" id="table-search" class="form-control" placeholder="🔍 Filter overdue books...">
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
            <th>Days Overdue</th>
            <th>Accrued Fine</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($overdueBooks)): ?>
          <tr>
            <td colspan="8" style="text-align:center;padding:3rem;color:var(--text-light)">
              <div style="font-size:2.5rem;margin-bottom:0.5rem">✅</div>
              <div>No overdue books! All returns are on time.</div>
            </td>
          </tr>
          <?php endif; ?>
          <?php foreach ($overdueBooks as $ib): ?>
          <?php
            $daysLate = max(0, (int)((strtotime(date('Y-m-d')) - strtotime($ib['due_date'])) / 86400));
            $fine = $daysLate * FINE_PER_DAY;
          ?>
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
            <td>
              <span class="badge badge-danger"><?= date('M j, Y', strtotime($ib['due_date'])) ?></span>
            </td>
            <td>
              <span class="badge badge-danger" style="font-size:0.9rem;font-weight:700">
                <?= $daysLate ?> day<?= $daysLate !== 1 ? 's' : '' ?>
              </span>
            </td>
            <td>
              <span style="color:var(--danger);font-weight:700;font-size:1rem">$<?= number_format($fine, 2) ?></span>
              <div style="font-size:0.72rem;color:var(--text-light)">+$<?= number_format(FINE_PER_DAY, 2) ?>/day</div>
            </td>
            <td>
              <button onclick="returnBook(<?= $ib['id'] ?>, '<?= addslashes(htmlspecialchars($ib['book_title'])) ?>')"
                class="btn btn-success btn-sm">↩ Process Return</button>
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
