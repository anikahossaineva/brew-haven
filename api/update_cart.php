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
$action = $_POST['action'] ?? '';
if (!$id || !in_array($action, ['increase', 'decrease'], true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid cart change.']);
    exit;
}

$query = $conn->prepare('SELECT c.quantity, p.price, p.stock FROM cart c JOIN products p ON p.id = c.product_id WHERE c.id = ? AND c.user_id = ?');
$query->execute([$id, $_SESSION['user_id']]);
$item = $query->fetch();
if (!$item) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
    exit;
}

$quantity = (int) $item['quantity'];
if ($action === 'increase') {
    if ($quantity >= (int) $item['stock']) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'No more stock is available.']);
        exit;
    }
    $quantity++;
} else {
    $quantity = max(1, $quantity - 1);
}
$conn->prepare('UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?')->execute([$quantity, $id, $_SESSION['user_id']]);

$totalQuery = $conn->prepare('SELECT COALESCE(SUM(c.quantity * p.price), 0) FROM cart c JOIN products p ON p.id = c.product_id WHERE c.user_id = ?');
$totalQuery->execute([$_SESSION['user_id']]);
$countQuery = $conn->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?');
$countQuery->execute([$_SESSION['user_id']]);
echo json_encode([
    'success' => true,
    'quantity' => $quantity,
    'subtotal' => number_format($quantity * (float) $item['price'], 2, '.', ''),
    'total' => number_format((float) $totalQuery->fetchColumn(), 2, '.', ''),
    'cart_count' => (int) $countQuery->fetchColumn(),
]);
