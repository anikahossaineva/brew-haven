<?php
declare(strict_types=1);
require '../config/database.php';
header('Content-Type: application/json; charset=utf-8');

function respond(array $body, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($body);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['success' => false, 'message' => 'Invalid request.'], 405);
if (!csrf_valid($_POST['csrf_token'] ?? null)) respond(['success' => false, 'message' => 'Refresh the page and try again.'], 419);
if (empty($_SESSION['user_id'])) respond(['success' => false, 'message' => 'Please log in to add items.'], 401);

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$productId) respond(['success' => false, 'message' => 'Invalid product.'], 422);

$product = $conn->prepare('SELECT id, stock, is_available FROM products WHERE id = ?');
$product->execute([$productId]);
$product = $product->fetch();
if (!$product || !(int) $product['is_available']) respond(['success' => false, 'message' => 'This item is unavailable.'], 404);

$find = $conn->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
$find->execute([$_SESSION['user_id'], $productId]);
$existing = $find->fetch();
if ((int) ($existing['quantity'] ?? 0) >= (int) $product['stock']) respond(['success' => false, 'message' => 'There is not enough stock for another item.'], 422);

if ($existing) {
    $conn->prepare('UPDATE cart SET quantity = quantity + 1 WHERE id = ? AND user_id = ?')->execute([$existing['id'], $_SESSION['user_id']]);
} else {
    $conn->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)')->execute([$_SESSION['user_id'], $productId]);
}

$count = $conn->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?');
$count->execute([$_SESSION['user_id']]);
respond(['success' => true, 'message' => 'Added to your cart.', 'cart_count' => (int) $count->fetchColumn()]);
