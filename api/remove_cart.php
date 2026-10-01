<?php
declare(strict_types=1);
require '../config/database.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request. Refresh and try again.']);
    exit;
}
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in.']);
    exit;
}
$id = filter_input(INPUT_POST, 'cart_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid cart item.']);
    exit;
}
$delete = $conn->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?');
$delete->execute([$id, $_SESSION['user_id']]);
echo json_encode(['success' => $delete->rowCount() === 1]);
