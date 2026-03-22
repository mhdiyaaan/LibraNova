<?php
require_once '../includes/config.php';

if (!isAdmin()) redirect('../login.php');

$conn = getDBConnection();
$search = sanitize($_GET['search'] ?? '');

$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM issued_books ib WHERE ib.user_id = u.id AND ib.status = 'issued') as active_books,
        (SELECT COUNT(*) FROM issued_books ib WHERE ib.user_id = u.id) as total_books
        FROM users u WHERE u.role = 'user'";

if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
}

$sql .= " ORDER BY u.created_at DESC";

if (!empty($search)) {
    $stmt = $conn->prepare($sql);
    $sp = "%$search%";
    $stmt->bind_param("sss", $sp, $sp, $sp);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $users = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users — LibraNova Admin</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="../index.php" class="navbar-brand"><div class="logo-icon">📚</div>Libra<span>Nova</span> <small style="font-size:0.65rem;background:var(--gold);padding:2px 6px;border-radius:4px;margin-left:6px">ADMIN</small></a>
  <button class="navbar-toggle">☰</button>
  <ul class="navbar-nav">
    <li><a href="../index.php" class="nav-link">🏠 Site</a></li>
    <li>
      <form method="POST" action="../includes/auth.php" style="display:inline">
        <input type="hidden" name="action" value="logout">
        <button type="submit" class="btn btn-outline-primary btn-sm">Logout</button>
      </form>
    </li>
  </ul>
</nav>

<div class="admin-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Main</div>
      <a href="dashboard.php" class="sidebar-link"><span class="icon">📊</span> Dashboard</a>
      <a href="books.php" class="sidebar-link"><span class="icon">📚</span> Manage Books</a>
      <a href="users.php" class="sidebar-link active"><span class="icon">👥</span> Manage Users</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Circulation</div>
      <a href="issued.php" class="sidebar-link"><span class="icon">📤</span> Issued Books</a>
      <a href="returned.php" class="sidebar-link"><span class="icon">↩️</span> Returned Books</a>
      <a href="overdue.php" class="sidebar-link"><span class="icon">⚠️</span> Overdue Books</a>
    </div>
  </aside>

  <main class="admin-content">
    <div style="margin-bottom:1.5rem">
      <h2>👥 Manage Users</h2>
      <p><?= count($users) ?> registered member(s)</p>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <!-- SEARCH -->
    <div class="search-section">
      <form method="GET" style="display:flex;gap:1rem;align-items:flex-end">
        <div class="form-group" style="margin:0;flex:1">
          <input type="text" name="search" id="table-search" class="form-control"
            placeholder="🔍 Search by name, email, or phone..."
            value="<?= htmlspecialchars($search) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if ($search): ?>
          <a href="users.php" class="btn btn-outline-primary">Clear</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- USERS TABLE -->
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Active Books</th>
            <th>Total Borrowed</th>
            <th>Member Since</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($users)): ?>
          <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--text-light)">No users found.</td></tr>
          <?php endif; ?>
          <?php foreach ($users as $u): ?>
          <tr class="searchable-row">
            <td style="color:var(--text-light)">#<?= $u['id'] ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:0.6rem">
                <div style="width:32px;height:32px;background:var(--navy-mid);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:0.75rem;font-weight:700;flex-shrink:0">
                  <?= strtoupper(substr($u['name'],0,1)) ?>
                </div>
                <span style="font-weight:600"><?= htmlspecialchars($u['name']) ?></span>
              </div>
            </td>
            <td style="font-size:0.875rem"><?= htmlspecialchars($u['email']) ?></td>
            <td style="font-size:0.875rem"><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
            <td>
              <span class="badge <?= $u['active_books'] > 0 ? 'badge-info' : 'badge-success' ?>">
                <?= $u['active_books'] ?>
              </span>
            </td>
            <td><?= $u['total_books'] ?></td>
            <td style="font-size:0.85rem"><?= date('M j, Y', strtotime($u['membership_date'])) ?></td>
            <td>
              <span class="badge <?= $u['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                <?= ucfirst($u['status']) ?>
              </span>
            </td>
            <td>
              <div style="display:flex;gap:0.4rem">
                <button onclick="viewUser(<?= $u['id'] ?>, '<?= addslashes(htmlspecialchars($u['name'])) ?>')"
                  class="btn btn-navy btn-sm" title="View Details">👁️</button>
                <button onclick="toggleUserStatus(<?= $u['id'] ?>, '<?= $u['status'] ?>')"
                  class="btn <?= $u['status'] === 'active' ? 'btn-warning' : 'btn-success' ?> btn-sm"
                  title="<?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?>">
                  <?= $u['status'] === 'active' ? '🔒' : '🔓' ?>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>

<!-- VIEW USER MODAL -->
<div class="modal-overlay" id="view-user-modal">
  <div class="modal">
    <div class="modal-header">
      <h3 id="modal-user-name">User Details</h3>
      <button class="modal-close" onclick="closeModal('view-user-modal')">✕</button>
    </div>
    <div class="modal-body" id="modal-user-body">
      <div class="spinner"></div>
    </div>
    <div class="modal-footer">
      <button onclick="closeModal('view-user-modal')" class="btn btn-outline-primary">Close</button>
    </div>
  </div>
</div>

<div class="toast-container"></div>
<script src="../js/main.js"></script>
<script>
async function viewUser(userId, userName) {
  document.getElementById('modal-user-name').textContent = '👤 ' + userName;
  document.getElementById('modal-user-body').innerHTML = '<div class="spinner"></div>';
  openModal('view-user-modal');

  try {
    const res = await fetch(`../actions/get_user.php?id=${userId}`);
    const data = await res.json();
    if (data.success) {
      const u = data.user;
      const books = data.books;
      let booksHtml = books.length === 0
        ? '<p style="color:var(--text-light);text-align:center;padding:1rem">No borrowing history.</p>'
        : books.map(b => `
          <div style="padding:0.75rem;border:1px solid var(--border);border-radius:var(--radius);margin-bottom:0.5rem">
            <div style="font-weight:600;font-size:0.875rem">${b.title}</div>
            <div style="font-size:0.8rem;color:var(--text-light)">
              Issued: ${b.issue_date} | Due: ${b.due_date} |
              Status: <span class="badge ${b.status === 'returned' ? 'badge-success' : b.due_date < '<?= date('Y-m-d') ?>' ? 'badge-danger' : 'badge-info'}">${b.status}</span>
              ${b.fine_amount > 0 ? `| Fine: <strong style="color:var(--danger)">$${parseFloat(b.fine_amount).toFixed(2)}</strong>` : ''}
            </div>
          </div>`).join('');

      document.getElementById('modal-user-body').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem">
          <div><div style="font-size:0.8rem;color:var(--text-light)">Email</div><div style="font-size:0.9rem;font-weight:500">${u.email}</div></div>
          <div><div style="font-size:0.8rem;color:var(--text-light)">Phone</div><div style="font-size:0.9rem">${u.phone || '—'}</div></div>
          <div><div style="font-size:0.8rem;color:var(--text-light)">Status</div><div><span class="badge ${u.status === 'active' ? 'badge-success' : 'badge-danger'}">${u.status}</span></div></div>
          <div><div style="font-size:0.8rem;color:var(--text-light)">Member Since</div><div style="font-size:0.9rem">${u.membership_date}</div></div>
        </div>
        <div style="font-weight:600;margin-bottom:0.75rem;color:var(--navy)">📚 Borrowing History (${books.length})</div>
        <div style="max-height:300px;overflow-y:auto">${booksHtml}</div>`;
    }
  } catch(e) {
    document.getElementById('modal-user-body').innerHTML = '<p style="color:var(--danger)">Failed to load user data.</p>';
  }
}
</script>
</body>
</html>
