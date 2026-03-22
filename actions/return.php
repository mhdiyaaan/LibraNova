<?php
require_once '../includes/config.php';
require_once '../includes/books.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$issueId = (int)($_POST['issue_id'] ?? 0);
if (!$issueId) {
    echo json_encode(['success' => false, 'message' => 'Invalid issue ID.']);
    exit;
}

// If admin, allow any return; if user, restrict to their own
$userId = isAdmin() ? null : $_SESSION['user_id'];
$result = returnBook($issueId, $userId);
echo json_encode($result);
?>
