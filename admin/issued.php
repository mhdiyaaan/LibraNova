<?php
require_once '../includes/config.php';
require_once '../includes/books.php';

if (!isAdmin()) redirect('../login.php');

$issuedBooks = getAllIssuedBooks('issued');
$flash = getFlash();

// Manual issue book form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'admin_issue') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $bookId = (int)($_POST['book_id'] ?? 0);
    if ($userId && $bookId) {
        $result = issueBook($userId, $bookId);
        setFlash($result['success'] ? 'success' : 'error', $result['message']);
    }
    redirect('issued.php');
}

// Get all users and books for the issue form
$conn = getDBConnection();
$allUsers = $conn->query("SELECT id, name, email FROM users WHERE role = 'user' AND status = 'active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$allBooks = $conn->query("SELECT id, title, author, available_copies FROM books WHERE available_copies > 0 ORDER BY title")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Issued Books — LibraNova Admin</title>
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
      <a href="issued.php" class="sidebar-link active"><span class="icon">📤</span> Issued Books</a>
      <a href="returned.php" class="sidebar-link"><span class="icon">↩️</span> Returned Books</a>
      <a href="overdue.php" class="sidebar-link"><span class="icon">⚠️</span> Overdue Books</a>
    </div>
  </aside>

  <main class="admin-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
      <div>
        <h2>📤 Currently Issued Books</h2>
        <p><?= count($issuedBooks) ?> book(s) currently out</p>
      </div>
      <button onclick="openModal('issue-book-modal')" class="btn btn-primary">+ Issue Book</button>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <div class="search-section">
      <input type="text" id="table-search" class="form-control" placeholder="🔍 Filter by user, book, or email...">
    </div>

    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Book</th>
            <th>Issue Date</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($issuedBooks)): ?>
          <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-light)">No books currently issued.</td></tr>
          <?php endif; ?>
          <?php foreach ($issuedBooks as $ib): ?>
          <?php $isOverdue = $ib['due_date'] < date('Y-m-d'); ?>
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
              <span class="badge <?= $isOverdue ? 'badge-danger' : 'badge-info' ?>">
                <?= date('M j, Y', strtotime($ib['due_date'])) ?>
              </span>
              <?php if ($isOverdue): ?>
                <div style="font-size:0.72rem;color:var(--danger);margin-top:2px">
                  <?= (int)((time() - strtotime($ib['due_date'])) / 86400) ?> days overdue
                </div>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge <?= $isOverdue ? 'badge-danger' : 'badge-info' ?>">
                <?= $isOverdue ? '⚠️ Overdue' : '📤 Issued' ?>
              </span>
            </td>
            <td>
              <button onclick="returnBook(<?= $ib['id'] ?>, '<?= addslashes(htmlspecialchars($ib['book_title'])) ?>')"
                class="btn btn-success btn-sm">↩ Return</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- ISSUE BOOK MODAL -->
<div class="modal-overlay" id="issue-book-modal">
  <div class="modal">
    <div class="modal-header">
      <h3>📤 Issue Book to User</h3>
      <button class="modal-close" onclick="closeModal('issue-book-modal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="admin_issue">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Select User *</label>
          <select name="user_id" class="form-control" required>
            <option value="">— Choose a user —</option>
            <?php foreach ($allUsers as $u): ?>
              <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Select Book *</label>
          <select name="book_id" class="form-control" required>
            <option value="">— Choose a book —</option>
            <?php foreach ($allBooks as $b): ?>
              <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['title']) ?> by <?= htmlspecialchars($b['author']) ?> (<?= $b['available_copies'] ?> available)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="background:var(--cream);border-radius:var(--radius);padding:1rem;font-size:0.875rem;color:var(--text-mid)">
          📅 Due date will be set to <strong><?= date('F j, Y', strtotime('+'.LOAN_DAYS.' days')) ?></strong> (<?= LOAN_DAYS ?> days from today)
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('issue-book-modal')" class="btn btn-outline-primary">Cancel</button>
        <button type="submit" class="btn btn-primary">Issue Book</button>
      </div>
    </form>
  </div>
</div>

<div class="toast-container"></div>
<script src="../js/main.js"></script>
</body>
</html>
