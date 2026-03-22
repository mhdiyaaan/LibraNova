<?php
require_once '../includes/config.php';
require_once '../includes/books.php';

header('Content-Type: application/json');

if (!isLoggedIn() || isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$bookId = (int)($_POST['book_id'] ?? 0);
if (!$bookId) {
    echo json_encode(['success' => false, 'message' => 'Invalid book.']);
    exit;
}

$result = issueBook($_SESSION['user_id'], $bookId);
echo json_encode($result);
?>
