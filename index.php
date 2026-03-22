<?php
require_once 'includes/config.php';
require_once 'includes/books.php';

$stats = getDashboardStats();
$recentBooks = getBooks();
$recentBooks = array_slice($recentBooks, 0, 8);
$categories = getCategories();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LibraNova — Modern Library Management</title>
<link rel="stylesheet" href="css/style.css">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <div class="logo-icon">📚</div>
    Libra<span>Nova</span>
  </a>
  <button class="navbar-toggle" onclick="document.querySelector('.navbar-nav').classList.toggle('open')">☰</button>
  <ul class="navbar-nav">
    <li><a href="index.php" class="nav-link active"><span class="icon">🏠</span> Home</a></li>
    <li><a href="books.php" class="nav-link"><span class="icon">📖</span> Books</a></li>
    <?php if (isLoggedIn()): ?>
      <li><a href="profile.php" class="nav-link"><span class="icon">👤</span> My Profile</a></li>
      <?php if (isAdmin()): ?>
        <li><a href="admin/dashboard.php" class="nav-link"><span class="icon">⚙️</span> Admin</a></li>
      <?php endif; ?>
      <li>
        <form method="POST" action="includes/auth.php" style="display:inline">
          <input type="hidden" name="action" value="logout">
          <button type="submit" class="btn btn-outline-primary btn-sm">Logout</button>
        </form>
      </li>
    <?php else: ?>
      <li><a href="login.php" class="nav-link"><span class="icon">🔑</span> Login</a></li>
      <li><a href="register.php" class="btn btn-primary btn-sm">Register</a></li>
    <?php endif; ?>
  </ul>
</nav>

<?php if ($flash): ?>
<div class="container mt-2">
  <div class="alert alert-<?= $flash['type'] ?>">
    <?= htmlspecialchars($flash['message']) ?>
  </div>
</div>
<?php endif; ?>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <div class="hero-content">
      <div>
        <h1>Your Knowledge,<br><span>Organized.</span></h1>
        <p>Discover thousands of books, manage your borrowings, and track your reading journey with LibraNova — the modern library experience.</p>
        <div style="display:flex;gap:1rem;flex-wrap:wrap">
          <a href="books.php" class="btn btn-primary btn-lg">Browse Books</a>
          <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-white btn-lg">Join Free</a>
          <?php else: ?>
            <a href="profile.php" class="btn btn-white btn-lg">My Books</a>
          <?php endif; ?>
        </div>
        <div class="hero-stats">
          <div>
            <div class="hero-stat-number"><?= number_format($stats['total_books']) ?>+</div>
            <div class="hero-stat-label">Books</div>
          </div>
          <div>
            <div class="hero-stat-number"><?= number_format($stats['total_users']) ?>+</div>
            <div class="hero-stat-label">Members</div>
          </div>
          <div>
            <div class="hero-stat-number"><?= number_format($stats['total_returned']) ?>+</div>
            <div class="hero-stat-label">Borrowed</div>
          </div>
        </div>
      </div>
      <div class="hero-visual">
        <div class="book-stack">
          <div class="book-item"></div>
          <div class="book-item"></div>
          <div class="book-item"></div>
          <div class="book-item"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<section style="padding:3rem 0;background:white;border-bottom:1px solid var(--border)">
  <div class="container">
    <div style="text-align:center;margin-bottom:2rem">
      <h2>Browse by Category</h2>
      <p>Explore our collection across diverse genres and topics</p>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:0.75rem;justify-content:center">
      <a href="books.php" class="btn btn-outline-primary">All Books</a>
      <?php foreach ($categories as $cat): ?>
        <a href="books.php?category=<?= urlencode($cat) ?>" class="btn btn-outline-primary"><?= htmlspecialchars($cat) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FEATURED BOOKS -->
<section class="main-content">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
      <div>
        <h2>Featured Books</h2>
        <p>Recently added to our collection</p>
      </div>
      <a href="books.php" class="btn btn-navy">View All →</a>
    </div>

    <div class="book-grid">
      <?php foreach ($recentBooks as $book): ?>
      <div class="book-card">
        <div class="book-card-spine" style="background:<?= ['#c0392b','#2980b9','#27ae60','var(--gold)','#8e44ad','#16a085'][crc32($book['category']) % 6] ?>"></div>
        <div class="book-card-body">
          <span class="book-category"><?= htmlspecialchars($book['category']) ?></span>
          <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
          <div class="book-author">by <?= htmlspecialchars($book['author']) ?></div>
          <div class="book-year">📅 <?= $book['publication_year'] ?></div>
          <?php if ($book['description']): ?>
          <p style="font-size:0.82rem;margin-top:0.75rem;color:var(--text-mid);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
            <?= htmlspecialchars($book['description']) ?>
          </p>
          <?php endif; ?>
        </div>
        <div class="book-card-footer" style="display:flex;align-items:center;justify-content:space-between">
          <span class="availability-badge <?= $book['available_copies'] > 0 ? 'badge-available' : 'badge-unavailable' ?>">
            <?= $book['available_copies'] > 0 ? '✓ Available' : '✗ Unavailable' ?>
          </span>
          <?php if (isLoggedIn() && !isAdmin() && $book['available_copies'] > 0): ?>
            <button onclick="issueBook(<?= $book['id'] ?>, '<?= addslashes(htmlspecialchars($book['title'])) ?>')" class="btn btn-primary btn-sm">Borrow</button>
          <?php elseif (!isLoggedIn()): ?>
            <a href="login.php" class="btn btn-navy btn-sm">Login to Borrow</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section style="background:var(--navy);padding:4rem 0;color:white">
  <div class="container">
    <div style="text-align:center;margin-bottom:3rem">
      <h2 style="color:white">How It Works</h2>
      <p style="color:rgba(255,255,255,0.65)">Simple steps to start your reading journey</p>
    </div>
    <div class="grid-3" style="gap:2rem">
      <div style="text-align:center;padding:1.5rem">
        <div style="font-size:2.5rem;margin-bottom:1rem">📝</div>
        <h3 style="color:var(--gold);margin-bottom:0.5rem">1. Register</h3>
        <p style="color:rgba(255,255,255,0.65)">Create your free account and become a library member in seconds.</p>
      </div>
      <div style="text-align:center;padding:1.5rem">
        <div style="font-size:2.5rem;margin-bottom:1rem">🔍</div>
        <h3 style="color:var(--gold);margin-bottom:0.5rem">2. Discover</h3>
        <p style="color:rgba(255,255,255,0.65)">Search and browse our extensive collection by title, author, or category.</p>
      </div>
      <div style="text-align:center;padding:1.5rem">
        <div style="font-size:2.5rem;margin-bottom:1rem">📚</div>
        <h3 style="color:var(--gold);margin-bottom:0.5rem">3. Borrow</h3>
        <p style="color:rgba(255,255,255,0.65)">Issue books instantly and return them when done. Track everything from your profile.</p>
      </div>
    </div>
  </div>
</section>

<footer>
  <p>© <?= date('Y') ?> <span>LibraNova</span> — Modern Library Management System. Built with ❤️</p>
</footer>

<script src="js/main.js"></script>
</body>
</html>
