<?php

session_start();

include 'config/database.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'includes/navbar.php';

$user_id=$_SESSION['user_id'];

$sql="
SELECT
products.product_name,
products.price,
products.image,
cart.quantity,
cart.product_id

FROM cart

JOIN products
ON cart.product_id=products.id

WHERE cart.user_id=?
";

$stmt=$conn->prepare($sql);
$stmt->execute([$user_id]);

$items=$stmt->fetchAll(PDO::FETCH_ASSOC);

$total=0;

?>

<div class="container py-5" style="margin-top:120px;">

<div class="row">

<div class="col-lg-7">

<div class="card shadow-lg border-0 rounded-4">

<div class="card-body p-4">

<h2 class="mb-4">

Checkout

</h2>

<form
action="api/checkout.php"
method="POST">

<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

<div class="mb-3">

<label class="form-label">

Full Name

</label>

<input
type="text"
class="form-control"
value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>"
readonly>

</div>

<div class="mb-3">

<label>

Payment Method

</label>

<select
name="payment_method"
class="form-select">

<option value="Cash on Delivery">

Cash on Delivery

</option>

<option value="Card (unavailable)" disabled>Card payments (coming later)</option>

</select>

</div>

<button
class="btn btn-coffee btn-lg w-100">

Place Order

</button>

</form>

</div>

</div>

</div>

<div class="col-lg-5">

<div class="card shadow-lg border-0 rounded-4">

<div class="card-body">

<h3>

Order Summary

</h3>

<hr>

<?php foreach($items as $item):

$subtotal=$item['price']*$item['quantity'];

$total+=$subtotal;

?>

<div class="d-flex justify-content-between mb-3">

<div>

<strong>

<?php echo htmlspecialchars($item['product_name']); ?>

</strong>

<br>

<small>

Qty:

<?php echo $item['quantity']; ?>

</small>

</div>

<div>

৳

<?php echo number_format($subtotal,2); ?>

</div>

</div>

<?php endforeach; ?>

<hr>

<div class="d-flex justify-content-between mb-2"><span>Delivery</span><span>৳60.00</span></div>

<h4>

Total

<span class="float-end">

৳

<?php echo number_format($total + 60,2); ?>

</span>

</h4>

</div>

</div>

</div>

</div>

</div>

<?php

include 'includes/footer.php';
include 'includes/scripts.php';

?>
