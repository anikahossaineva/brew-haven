<?php

include 'config/database.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'includes/navbar.php';

$user_id = $_SESSION['user_id'];

$sql = "SELECT
            cart.id,
            cart.quantity,
            products.product_name,
            products.price,
            products.image
        FROM cart
        INNER JOIN products
        ON cart.product_id = products.id
        WHERE cart.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);

$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;

?>

<div class="container py-5" style="margin-top:120px;">

<h1 class="mb-5 fw-bold">

🛒 Shopping Cart

</h1>

<?php if(count($cartItems)>0): ?>

<div class="row">

<div class="col-lg-8">

<?php foreach($cartItems as $item):

$subtotal = $item['price'] * $item['quantity'];

$total += $subtotal;

?>

<div class="card mb-4 shadow-sm border-0 rounded-4 cart-item">

<div class="card-body">

<div class="row align-items-center">

<div class="col-md-3">

<img
src="assets/images/menu/<?php echo e($item['image']); ?>"
class="img-fluid rounded">

</div>

<div class="col-md-5">

<h4>

<?php echo htmlspecialchars($item['product_name']); ?>

</h4>

<h4 class="text-success">
৳<?php echo number_format($item['price'],2); ?>
</h4>

<p class="subtotal mt-2">
Subtotal:
<strong>
 ৳
<?php echo number_format($subtotal,2); ?>
</strong>
</p>

</div>

<div class="col-md-4 text-end">

<div class="d-flex justify-content-end align-items-center gap-2">

<button
class="btn btn-outline-secondary decrease-btn"
data-id="<?php echo $item['id']; ?>">

−

</button>

<span class="fw-bold quantity">
    <?php echo $item['quantity']; ?>
</span>

<button
class="btn btn-outline-secondary increase-btn"
data-id="<?php echo $item['id']; ?>">

+

</button>

</div>

<button
class="btn btn-danger mt-3 remove-btn"
data-id="<?php echo $item['id']; ?>">

Remove

</button>

</div>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

<div class="col-lg-4">

<div class="card shadow-lg border-0 rounded-4">

<div class="card-body">

<h3>

Order Summary

</h3>

<hr>

<h4>

Total

<span class="float-end grand-total">

৳

<?php echo number_format($total,2); ?>

</span>

</h4>

<a
href="checkout.php"
class="btn btn-coffee w-100 mt-4">

Proceed to Checkout

</a>

</div>

</div>

</div>

</div>

<?php else: ?>

<div class="text-center py-5">

<h3>

Your cart is empty ☕

</h3>

<a href="index.php"
class="btn btn-coffee mt-4">

Continue Shopping

</a>

</div>

<?php endif; ?>

</div>

<?php

include 'includes/footer.php';
include 'includes/scripts.php';

?>
