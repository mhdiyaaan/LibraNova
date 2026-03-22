<?php
require_once 'includes/config.php';
require_once 'includes/books.php';

$search = sanitize($_GET['search'] ?? '');
$category = sanitize($_GET['category'] ?? '');
$availability = sanitize($_GET['availability'] ?? '');

$books = getBooks($search, $category, $availability);
$categories = getCategories();
$flash = getFlash();

$colorsMap = ['Fiction'=>'#c0392b','Dystopian'=>'#8e44ad','Fantasy'=>'#2980b9','Non-Fiction'=>'#27ae60','Business'=>'#e67e22','Technology'=>'#16a085','Psychology'=>'#d35400','Thriller'=>'#2c3e50'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Books — LibraNova</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <div class="logo-icon">📚</div>Libra<span>Nova</span>
  </a>
  <button class="navbar-toggle">☰</button>
  <ul class="navbar-nav">
    <li><a href="index.php" class="nav-link">🏠 Home</a></li>
    <li><a href="books.php" class="nav-link active">📖 Books</a></li>
    <?php if (isLoggedIn()): ?>
      <li><a href="profile.php" class="nav-link">👤 My Profile</a></li>
      <?php if (isAdmin()): ?>
        <li><a href="admin/dashboard.php" class="nav-link">⚙️ Admin</a></li>
      <?php endif; ?>
      <li>
        <form method="POST" action="includes/auth.php" style="display:inline">
          <input type="hidden" name="action" value="logout">
          <button type="submit" class="btn btn-outline-primary btn-sm">Logout</button>
        </form>
      </li>
    <?php else: ?>
      <li><a href="login.php" class="nav-link">🔑 Login</a></li>
      <li><a href="register.php" class="btn btn-primary btn-sm">Register</a></li>
    <?php endif; ?>
  </ul>
</nav>

<div class="page-header">
  <div class="container">
    <h1>📖 Book Collection</h1>
    <p><?= count($books) ?> book<?= count($books) !== 1 ? 's' : '' ?> found<?= $search ? " for \"$search\"" : '' ?></p>
  </div>
</div>

<div class="main-content">
  <div class="container">

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <!-- SEARCH & FILTERS -->
    <div class="search-section">
      <form id="search-form" method="GET" action="books.php">
        <div class="search-row">
          <div class="form-group" style="margin:0">
            <input type="text" id="search-input" name="search" class="form-control"
              placeholder="🔍 Search by title, author, or ISBN..."
              value="<?= htmlspecialchars($search) ?>">
          </div>
          <div class="form-group" style="margin:0">
            <select name="category" class="form-control auto-submit">
              <option value="">All Categories</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0">
            <select name="availability" class="form-control auto-submit">
              <option value="">All Books</option>
              <option value="available" <?= $availability === 'available' ? 'selected' : '' ?>>Available Only</option>
              <option value="unavailable" <?= $availability === 'unavailable' ? 'selected' : '' ?>>Unavailable Only</option>
            </select>
          </div>
          <div>
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search || $category || $availability): ?>
              <a href="books.php" class="btn btn-outline-primary" style="margin-left:0.5rem">Clear</a>
            <?php endif; ?>
          </div>
        </div>
      </form>
    </div>

    <!-- BOOKS GRID -->
    <?php if (empty($books)): ?>
    <div class="empty-state">
      <div class="empty-icon">📚</div>
      <h3>No books found</h3>
      <p>Try adjusting your search or filters.</p>
      <a href="books.php" class="btn btn-primary mt-2">Clear Filters</a>
    </div>
    <?php else: ?>
    <div class="book-grid">
      <?php foreach ($books as $book): ?>
      <?php $spineColor = $colorsMap[$book['category']] ?? 'var(--gold)'; ?>
      <div class="book-card">
        <div class="book-card-spine" style="background:<?= $spineColor ?>"></div>
        <div class="book-card-body">
          <span class="book-category"><?= htmlspecialchars($book['category']) ?></span>
          <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
          <div class="book-author">by <?= htmlspecialchars($book['author']) ?></div>
          <div style="display:flex;gap:1rem;margin-top:0.5rem">
            <?php if ($book['publication_year']): ?>
              <span class="book-year">📅 <?= $book['publication_year'] ?></span>
            <?php endif; ?>
            <?php if ($book['publisher']): ?>
              <span class="book-year">🏢 <?= htmlspecialchars($book['publisher']) ?></span>
            <?php endif; ?>
          </div>
          <?php if ($book['description']): ?>
          <p style="font-size:0.82rem;margin-top:0.75rem;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
            <?= htmlspecialchars($book['description']) ?>
          </p>
          <?php endif; ?>
          <div style="margin-top:0.75rem;font-size:0.8rem;color:var(--text-light)">
            📦 <?= $book['available_copies'] ?>/<?= $book['total_quantity'] ?> copies available
          </div>
        </div>
        <div class="book-card-footer" style="display:flex;align-items:center;justify-content:space-between">
          <span class="availability-badge <?= $book['available_copies'] > 0 ? 'badge-available' : 'badge-unavailable' ?>">
            <?= $book['available_copies'] > 0 ? '✓ Available' : '✗ Unavailable' ?>
          </span>
          <?php if (!isLoggedIn()): ?>
            <a href="login.php" class="btn btn-navy btn-sm">Login to Borrow</a>
          <?php elseif (!isAdmin() && $book['available_copies'] > 0): ?>
            <button onclick="issueBook(<?= $book['id'] ?>, '<?= addslashes(htmlspecialchars($book['title'])) ?>')" class="btn btn-primary btn-sm">
              📤 Borrow
            </button>
          <?php elseif (!isAdmin() && $book['available_copies'] == 0): ?>
            <span style="font-size:0.8rem;color:var(--text-light)">Not available</span>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</div>

<footer>
  <p>© <?= date('Y') ?> <span>LibraNova</span> — Modern Library Management System</p>
</footer>

<div class="toast-container"></div>
<script src="js/main.js"></script>
</body>
</html>
