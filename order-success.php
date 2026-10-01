<?php
require 'config/database.php';
$orderId = (int) ($_SESSION['last_order_id'] ?? 0);
$order = false;
if ($orderId > 0 && !empty($_SESSION['user_id'])) {
    $query = $conn->prepare('SELECT id, total_price, status FROM orders WHERE id = ? AND user_id = ?');
    $query->execute([$orderId, $_SESSION['user_id']]);
    $order = $query->fetch();
    unset($_SESSION['last_order_id']);
}
include 'includes/header.php';
include 'includes/navbar.php';
?>
<main class="container py-5 text-center" style="margin-top:140px;min-height:50vh"><?php if ($order): ?><i class="fa-solid fa-circle-check text-success" style="font-size:72px" aria-hidden="true"></i><h1 class="mt-4">Order received</h1><p class="lead">Order #<?= (int) $order['id'] ?> is <?= e($order['status']) ?>. Total: ৳<?= number_format((float) $order['total_price'], 2) ?>.</p><a class="btn btn-coffee" href="dashboard.php">View your orders</a><?php else: ?><h1>Thanks for visiting</h1><p class="lead">There is no recent order to display.</p><a class="btn btn-coffee" href="menu.php">Browse the menu</a><?php endif; ?></main>
<?php include 'includes/footer.php'; include 'includes/scripts.php'; ?>
