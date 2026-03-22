<?php
require_once 'config.php';

// Get all books with optional search/filter
function getBooks($search = '', $category = '', $availability = '') {
    $conn = getDBConnection();
    
    $sql = "SELECT * FROM books WHERE 1=1";
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $sql .= " AND (title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'sss';
    }
    
    if (!empty($category)) {
        $sql .= " AND category = ?";
        $params[] = $category;
        $types .= 's';
    }
    
    if ($availability === 'available') {
        $sql .= " AND available_copies > 0";
    } elseif ($availability === 'unavailable') {
        $sql .= " AND available_copies = 0";
    }
    
    $sql .= " ORDER BY title ASC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $books = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    return $books;
}

// Get single book
function getBook($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $conn->close();
    return $book;
}

// Get categories
function getCategories() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT DISTINCT category FROM books ORDER BY category");
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    $conn->close();
    return $categories;
}

// Add book (admin)
function addBook($data) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("INSERT INTO books (title, author, category, isbn, publication_year, publisher, description, total_quantity, available_copies) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssssssii",
        $data['title'], $data['author'], $data['category'],
        $data['isbn'], $data['publication_year'], $data['publisher'],
        $data['description'], $data['total_quantity'], $data['total_quantity']
    );
    
    if ($stmt->execute()) {
        $conn->close();
        return ['success' => true, 'message' => 'Book added successfully.'];
    }
    
    $conn->close();
    return ['success' => false, 'message' => 'Failed to add book: ' . $stmt->error];
}

// Update book (admin)
function updateBook($id, $data) {
    $conn = getDBConnection();
    
    // Get current book to calculate available copies difference
    $current = getBook($id);
    $diff = $data['total_quantity'] - $current['total_quantity'];
    $newAvailable = max(0, $current['available_copies'] + $diff);
    
    $stmt = $conn->prepare("UPDATE books SET title=?, author=?, category=?, isbn=?, publication_year=?, publisher=?, description=?, total_quantity=?, available_copies=? WHERE id=?");
    $stmt->bind_param(
        "sssssssiiii",
        $data['title'], $data['author'], $data['category'],
        $data['isbn'], $data['publication_year'], $data['publisher'],
        $data['description'], $data['total_quantity'], $newAvailable, $id
    );
    
    if ($stmt->execute()) {
        $conn->close();
        return ['success' => true, 'message' => 'Book updated successfully.'];
    }
    
    $conn->close();
    return ['success' => false, 'message' => 'Failed to update book.'];
}

// Delete book (admin)
function deleteBook($id) {
    $conn = getDBConnection();
    
    // Check if book is currently issued
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM issued_books WHERE book_id = ? AND status = 'issued'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    
    if ($row['count'] > 0) {
        $conn->close();
        return ['success' => false, 'message' => 'Cannot delete: book is currently issued to users.'];
    }
    
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $conn->close();
        return ['success' => true, 'message' => 'Book deleted successfully.'];
    }
    
    $conn->close();
    return ['success' => false, 'message' => 'Failed to delete book.'];
}

// Issue book
function issueBook($userId, $bookId) {
    $conn = getDBConnection();
    
    // Check availability
    $stmt = $conn->prepare("SELECT available_copies FROM books WHERE id = ?");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    
    if (!$book || $book['available_copies'] <= 0) {
        $conn->close();
        return ['success' => false, 'message' => 'Book is not available.'];
    }
    
    // Check if user already has this book
    $stmt = $conn->prepare("SELECT id FROM issued_books WHERE user_id = ? AND book_id = ? AND status = 'issued'");
    $stmt->bind_param("ii", $userId, $bookId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $conn->close();
        return ['success' => false, 'message' => 'You already have this book issued.'];
    }
    
    // Check if user has more than 3 books
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM issued_books WHERE user_id = ? AND status = 'issued'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row['count'] >= 3) {
        $conn->close();
        return ['success' => false, 'message' => 'You can only borrow up to 3 books at a time.'];
    }
    
    $issueDate = date('Y-m-d');
    $dueDate = date('Y-m-d', strtotime('+' . LOAN_DAYS . ' days'));
    
    // Insert issue record
    $stmt = $conn->prepare("INSERT INTO issued_books (user_id, book_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, 'issued')");
    $stmt->bind_param("iiss", $userId, $bookId, $issueDate, $dueDate);
    
    if ($stmt->execute()) {
        // Decrease available copies
        $conn->query("UPDATE books SET available_copies = available_copies - 1 WHERE id = $bookId");
        $conn->close();
        return ['success' => true, 'message' => "Book issued successfully! Due date: $dueDate"];
    }
    
    $conn->close();
    return ['success' => false, 'message' => 'Failed to issue book.'];
}

// Return book
function returnBook($issueId, $userId = null) {
    $conn = getDBConnection();
    
    $sql = "SELECT ib.*, b.title FROM issued_books ib JOIN books b ON ib.book_id = b.id WHERE ib.id = ? AND ib.status = 'issued'";
    if ($userId) {
        $sql .= " AND ib.user_id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    if ($userId) {
        $stmt->bind_param("ii", $issueId, $userId);
    } else {
        $stmt->bind_param("i", $issueId);
    }
    $stmt->execute();
    $issue = $stmt->get_result()->fetch_assoc();
    
    if (!$issue) {
        $conn->close();
        return ['success' => false, 'message' => 'Issue record not found.'];
    }
    
    $returnDate = date('Y-m-d');
    $dueDate = $issue['due_date'];
    $fine = 0;
    
    if ($returnDate > $dueDate) {
        $lateDays = (strtotime($returnDate) - strtotime($dueDate)) / 86400;
        $fine = $lateDays * FINE_PER_DAY;
    }
    
    $stmt = $conn->prepare("UPDATE issued_books SET return_date = ?, fine_amount = ?, status = 'returned' WHERE id = ?");
    $stmt->bind_param("sdi", $returnDate, $fine, $issueId);
    
    if ($stmt->execute()) {
        $conn->query("UPDATE books SET available_copies = available_copies + 1 WHERE id = " . $issue['book_id']);
        $conn->close();
        
        $msg = "Book '{$issue['title']}' returned successfully!";
        if ($fine > 0) {
            $msg .= " Fine: $" . number_format($fine, 2);
        }
        return ['success' => true, 'message' => $msg, 'fine' => $fine];
    }
    
    $conn->close();
    return ['success' => false, 'message' => 'Failed to process return.'];
}

// Get user's issued books
function getUserIssuedBooks($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT ib.*, b.title, b.author, b.category,
               CASE WHEN ib.status = 'issued' AND ib.due_date < CURDATE() THEN 'overdue'
                    ELSE ib.status END as display_status,
               DATEDIFF(CURDATE(), ib.due_date) as days_overdue
        FROM issued_books ib 
        JOIN books b ON ib.book_id = b.id 
        WHERE ib.user_id = ?
        ORDER BY ib.created_at DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    return $books;
}

// Get dashboard stats
function getDashboardStats() {
    $conn = getDBConnection();
    
    $stats = [];
    
    $stats['total_books'] = $conn->query("SELECT COUNT(*) as c FROM books")->fetch_assoc()['c'];
    $stats['total_users'] = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'user'")->fetch_assoc()['c'];
    $stats['total_issued'] = $conn->query("SELECT COUNT(*) as c FROM issued_books WHERE status = 'issued'")->fetch_assoc()['c'];
    $stats['total_returned'] = $conn->query("SELECT COUNT(*) as c FROM issued_books WHERE status = 'returned'")->fetch_assoc()['c'];
    $stats['overdue_books'] = $conn->query("SELECT COUNT(*) as c FROM issued_books WHERE status = 'issued' AND due_date < CURDATE()")->fetch_assoc()['c'];
    $stats['total_fines'] = $conn->query("SELECT COALESCE(SUM(fine_amount),0) as c FROM issued_books")->fetch_assoc()['c'];
    
    $conn->close();
    return $stats;
}

// Get all issued books (admin)
function getAllIssuedBooks($status = '') {
    $conn = getDBConnection();
    
    $sql = "SELECT ib.*, u.name as user_name, u.email as user_email, b.title as book_title, b.author 
            FROM issued_books ib 
            JOIN users u ON ib.user_id = u.id 
            JOIN books b ON ib.book_id = b.id";
    
    if ($status === 'overdue') {
        $sql .= " WHERE ib.status = 'issued' AND ib.due_date < CURDATE()";
    } elseif (!empty($status)) {
        $sql .= " WHERE ib.status = '$status'";
    }
    
    $sql .= " ORDER BY ib.created_at DESC";
    
    $result = $conn->query($sql);
    $books = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    return $books;
}
?>
