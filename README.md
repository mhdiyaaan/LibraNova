# LibraNova — Library Management System

A complete, modern library management system built with PHP, MySQL, HTML, CSS, and JavaScript.

---

## 🚀 Quick Setup

### Requirements
- PHP 7.4+ (with `mysqli` extension)
- MySQL 5.7+ or MariaDB 10+
- A web server: Apache (XAMPP/WAMP) or Nginx

### Step 1 — Create the Database
1. Open **phpMyAdmin** or your MySQL client
2. Import the `database.sql` file:
   ```sql
   SOURCE /path/to/library/database.sql;
   ```
   This creates `library_db` and seeds it with 12 sample books + an admin account.

### Step 2 — Configure Database Connection
Edit `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password
define('DB_NAME', 'library_db');
```

### Step 3 — Place Files on Web Server
- **XAMPP**: Copy the `library/` folder to `C:/xampp/htdocs/`
- **WAMP**: Copy to `C:/wamp64/www/`
- Access at: `http://localhost/library/`

### Step 4 — Update the Admin Password
After importing the SQL, run this in MySQL to set the admin password to `Admin@123`:
```sql
USE library_db;
UPDATE users 
SET password = '$2y$10$TKh8H1.PfDffdUw0CNgK4OlHFRKBa9B3MHFGFKGCdBLMPgEwHRFKi' 
WHERE email = 'admin@library.com';
```

> **Or** generate your own hash in PHP:
> ```php
> echo password_hash('YourPassword', PASSWORD_BCRYPT);
> ```

---

## 🔐 Default Login Credentials

| Role  | Email                  | Password  |
|-------|------------------------|-----------|
| Admin | admin@library.com      | Admin@123 |

---

## 📁 File Structure

```
library/
├── index.php              ← Home page
├── login.php              ← Login page
├── register.php           ← Registration page
├── books.php              ← Book listing with search/filter
├── profile.php            ← User profile & borrowing history
├── database.sql           ← Database schema & seed data
│
├── includes/
│   ├── config.php         ← DB config, session helpers
│   ├── auth.php           ← Login/register/logout handlers
│   └── books.php          ← All book/issue/return logic
│
├── actions/
│   ├── issue.php          ← AJAX: issue a book
│   ├── return.php         ← AJAX: return a book
│   ├── book_action.php    ← Add/edit/delete books (admin)
│   ├── user_action.php    ← Suspend/activate users (admin)
│   └── get_user.php       ← AJAX: get user details (admin)
│
├── admin/
│   ├── dashboard.php      ← Admin dashboard with stats
│   ├── books.php          ← Manage books (CRUD)
│   ├── users.php          ← Manage users
│   ├── issued.php         ← View & process issued books
│   ├── returned.php       ← View returned books
│   └── overdue.php        ← Overdue books & fines
│
├── css/
│   └── style.css          ← Complete responsive stylesheet
│
└── js/
    └── main.js            ← Frontend interactivity
```

---

## ✨ Features

### User Features
- Register & login with secure password hashing (bcrypt)
- Browse and search books by title, author, category, availability
- Borrow up to **3 books** at a time
- 14-day loan period per book
- Return books from profile page
- View full borrowing history with dates and fines
- Automatic fine calculation for overdue returns

### Admin Features
- Dashboard with live statistics (books, users, issued, returned, overdue, fines)
- Full book management: Add / Edit / Delete
- User management: View, suspend, or reactivate accounts
- Issue books manually to any user
- Process returns for any user
- View all overdue books with accruing fines
- Complete return history

---

## ⚙️ Configuration Options (`includes/config.php`)

| Constant     | Default | Description                     |
|--------------|---------|---------------------------------|
| FINE_PER_DAY | 2.00    | Late fine per day (USD)         |
| LOAN_DAYS    | 14      | Default loan duration in days   |
| SITE_NAME    | LibraNova | Application name              |

---

## 🔒 Security Features
- Passwords hashed with **bcrypt** (PHP `password_hash`)
- PHP session-based authentication
- SQL injection prevention with **prepared statements**
- XSS protection via `htmlspecialchars()` on all output
- Role-based access control (admin vs. user)
- CSRF-resistant actions (POST required for all mutations)

---

## 📐 Tech Stack
- **Frontend**: HTML5, CSS3 (custom design system), Vanilla JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL / MariaDB
- **Fonts**: Playfair Display + DM Sans (Google Fonts)
- **No framework dependencies** — pure PHP & JS

---

## 🐛 Troubleshooting

**"Database connection failed"**
- Check `DB_USER` and `DB_PASS` in `config.php`
- Ensure MySQL is running

**"Call to undefined function password_verify"**
- Requires PHP 5.5+ — check your PHP version

**Blank page / 500 error**
- Enable PHP error display: add `ini_set('display_errors', 1);` to `config.php`

**Admin password not working**
- Re-run the UPDATE SQL query from Step 4 above

---

*Built with ❤️ — LibraNova Library Management System*
