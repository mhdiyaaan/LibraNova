<?php
require_once '../includes/config.php';
require_once '../includes/books.php';

if (!isAdmin()) redirect('../login.php');

$search = sanitize($_GET['search'] ?? '');
$category = sanitize($_GET['category'] ?? '');
$books = getBooks($search, $category);
$categories = getCategories();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Books — LibraNova Admin</title>
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
      <a href="books.php" class="sidebar-link active"><span class="icon">📚</span> Manage Books</a>
      <a href="users.php" class="sidebar-link"><span class="icon">👥</span> Manage Users</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Circulation</div>
      <a href="issued.php" class="sidebar-link"><span class="icon">📤</span> Issued Books</a>
      <a href="returned.php" class="sidebar-link"><span class="icon">↩️</span> Returned Books</a>
      <a href="overdue.php" class="sidebar-link"><span class="icon">⚠️</span> Overdue Books</a>
    </div>
  </aside>

  <main class="admin-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
      <div>
        <h2>📚 Manage Books</h2>
        <p><?= count($books) ?> books in collection</p>
      </div>
      <button onclick="openModal('add-book-modal')" class="btn btn-primary">+ Add New Book</button>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <!-- SEARCH -->
    <div class="search-section">
      <form method="GET">
        <div class="search-row">
          <input type="text" name="search" class="form-control" placeholder="🔍 Search books..." value="<?= htmlspecialchars($search) ?>">
          <select name="category" class="form-control auto-submit">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
          </select>
          <div></div>
          <button type="submit" class="btn btn-primary">Search</button>
        </div>
      </form>
    </div>

    <!-- BOOKS TABLE -->
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Title / Author</th>
            <th>Category</th>
            <th>ISBN</th>
            <th>Year</th>
            <th>Stock</th>
            <th>Available</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($books)): ?>
          <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-light)">No books found.</td></tr>
          <?php endif; ?>
          <?php foreach ($books as $book): ?>
          <tr class="searchable-row">
            <td style="color:var(--text-light)">#<?= $book['id'] ?></td>
            <td>
              <div style="font-weight:600;color:var(--navy)"><?= htmlspecialchars($book['title']) ?></div>
              <div style="font-size:0.8rem;color:var(--text-light);font-style:italic"><?= htmlspecialchars($book['author']) ?></div>
            </td>
            <td><span class="book-category"><?= htmlspecialchars($book['category']) ?></span></td>
            <td style="font-size:0.82rem;color:var(--text-light)"><?= htmlspecialchars($book['isbn'] ?: '—') ?></td>
            <td><?= $book['publication_year'] ?: '—' ?></td>
            <td><?= $book['total_quantity'] ?></td>
            <td>
              <span class="badge <?= $book['available_copies'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                <?= $book['available_copies'] ?>
              </span>
            </td>
            <td>
              <div style="display:flex;gap:0.4rem">
                <button onclick='populateEditBook(<?= json_encode($book) ?>)' class="btn btn-warning btn-sm">✏️</button>
                <form method="POST" action="../actions/book_action.php" style="display:inline" onsubmit="return confirm('Delete &quot;<?= addslashes(htmlspecialchars($book['title'])) ?>&quot;?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- ADD BOOK MODAL -->
<div class="modal-overlay" id="add-book-modal">
  <div class="modal">
    <div class="modal-header">
      <h3>+ Add New Book</h3>
      <button class="modal-close" onclick="closeModal('add-book-modal')">✕</button>
    </div>
    <form method="POST" action="../actions/book_action.php">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Title *</label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Author *</label>
            <input type="text" name="author" class="form-control" required>
          </div>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Category *</label>
            <input type="text" name="category" class="form-control" required list="category-list">
            <datalist id="category-list">
              <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>">
              <?php endforeach; ?>
            </datalist>
          </div>
          <div class="form-group">
            <label class="form-label">ISBN</label>
            <input type="text" name="isbn" class="form-control">
          </div>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Publication Year</label>
            <input type="number" name="publication_year" class="form-control" min="1000" max="<?= date('Y') ?>" placeholder="e.g. 2023">
          </div>
          <div class="form-group">
            <label class="form-label">Publisher</label>
            <input type="text" name="publisher" class="form-control">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Total Quantity *</label>
          <input type="number" name="total_quantity" class="form-control" value="1" min="1" required>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" maxlength="500"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('add-book-modal')" class="btn btn-outline-primary">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Book</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT BOOK MODAL -->
<div class="modal-overlay" id="edit-book-modal">
  <div class="modal">
    <div class="modal-header">
      <h3>✏️ Edit Book</h3>
      <button class="modal-close" onclick="closeModal('edit-book-modal')">✕</button>
    </div>
    <form method="POST" action="../actions/book_action.php">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="book_id">
      <div class="modal-body">
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Title *</label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Author *</label>
            <input type="text" name="author" class="form-control" required>
          </div>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Category *</label>
            <input type="text" name="category" class="form-control" required list="category-list">
          </div>
          <div class="form-group">
            <label class="form-label">ISBN</label>
            <input type="text" name="isbn" class="form-control">
          </div>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Publication Year</label>
            <input type="number" name="publication_year" class="form-control" min="1000" max="<?= date('Y') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Publisher</label>
            <input type="text" name="publisher" class="form-control">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Total Quantity *</label>
          <input type="number" name="total_quantity" class="form-control" min="1" required>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" maxlength="500"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('edit-book-modal')" class="btn btn-outline-primary">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Book</button>
      </div>
    </form>
  </div>
</div>

<div class="toast-container"></div>
<script src="../js/main.js"></script>
</body>
</html>
