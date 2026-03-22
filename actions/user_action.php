<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$userId = (int)($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$userId || !in_array($action, ['suspend', 'activate'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

if ($userId === $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Cannot modify your own account.']);
    exit;
}

$conn = getDBConnection();
$status = $action === 'suspend' ? 'suspended' : 'active';
$stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'user'");
$stmt->bind_param("si", $status, $userId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $conn->close();
    echo json_encode(['success' => true, 'message' => "User {$action}d successfully."]);
} else {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Failed to update user status.']);
}
?>
