<?php
declare(strict_types=1);
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Invalid or expired request. Please return to checkout and try again.');
}
if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Card options stay disabled until a verified payment provider integration exists.
if (($_POST['payment_method'] ?? '') !== 'Cash on Delivery') {
    http_response_code(422);
    exit('Online payments are not available yet. Choose Cash on Delivery.');
}

$userId = (int) $_SESSION['user_id'];
try {
    $conn->beginTransaction();

    $cartQuery = $conn->prepare(
        'SELECT c.product_id, c.quantity, p.product_name, p.price, p.stock, p.is_available
         FROM cart c JOIN products p ON p.id = c.product_id
         WHERE c.user_id = ? FOR UPDATE'
    );
    $cartQuery->execute([$userId]);
    $items = $cartQuery->fetchAll();
    if (!$items) {
        throw new RuntimeException('Your cart is empty. Add an item before checking out.');
    }

    $subtotal = 0.0;
    foreach ($items as $item) {
        if (!(int) $item['is_available'] || (int) $item['quantity'] > (int) $item['stock']) {
            throw new RuntimeException('One or more items are no longer available in the requested quantity. Update your cart and try again.');
        }
        $subtotal += (float) $item['price'] * (int) $item['quantity'];
    }

    $userQuery = $conn->prepare('SELECT address FROM users WHERE id = ?');
    $userQuery->execute([$userId]);
    $address = $userQuery->fetchColumn();
    if (!$address) {
        throw new RuntimeException('Add a delivery address to your profile before ordering.');
    }

    $deliveryFee = 60.00;
    $tax = 0.00; // Set the applicable rate from store settings before enabling tax collection.
    $total = $subtotal + $deliveryFee + $tax;
    $order = $conn->prepare(
        "INSERT INTO orders (user_id, subtotal, delivery_fee, tax, total_price, payment_method, status, delivery_address)
         VALUES (?, ?, ?, ?, ?, 'Cash on Delivery', 'Pending', ?)"
    );
    $order->execute([$userId, $subtotal, $deliveryFee, $tax, $total, $address]);
    $orderId = (int) $conn->lastInsertId();

    $addItem = $conn->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)');
    $reduceStock = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
    foreach ($items as $item) {
        $addItem->execute([$orderId, $item['product_id'], $item['product_name'], $item['quantity'], $item['price']]);
        $reduceStock->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
        if ($reduceStock->rowCount() !== 1) {
            throw new RuntimeException('Stock changed while placing the order. Please retry checkout.');
        }
    }

    $payment = $conn->prepare("INSERT INTO payments (order_id, provider, amount, status) VALUES (?, 'cod', ?, 'Pending')");
    $payment->execute([$orderId, $total]);
    $conn->prepare('DELETE FROM cart WHERE user_id = ?')->execute([$userId]);
    $conn->commit();

    $_SESSION['last_order_id'] = $orderId;
    header('Location: ../order-success.php');
    exit;
} catch (Throwable $exception) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    if ($exception instanceof RuntimeException) {
        http_response_code(422);
        exit(e($exception->getMessage()));
    }
    error_log('Brew Haven checkout failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('We could not place the order. Please try again.');
}
