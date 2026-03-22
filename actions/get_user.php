<?php
require_once '../includes/config.php';
require_once '../includes/books.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, name, email, phone, address, status, membership_date FROM users WHERE id = ? AND role = 'user'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$stmt = $conn->prepare("
    SELECT ib.*, b.title, b.author 
    FROM issued_books ib 
    JOIN books b ON ib.book_id = b.id 
    WHERE ib.user_id = ? 
    ORDER BY ib.created_at DESC
    LIMIT 20
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$conn->close();

echo json_encode(['success' => true, 'user' => $user, 'books' => $books]);
?>
