<?php

session_start();

include 'config/database.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'includes/navbar.php';

$user = $_SESSION['user_id'];

/* Total Orders */

$stmt = $conn->prepare("
SELECT COUNT(*) 
FROM orders
WHERE user_id=?
");

$stmt->execute([$user]);

$totalOrders = $stmt->fetchColumn();

/* Cart */

$stmt = $conn->prepare("
SELECT SUM(quantity)
FROM cart
WHERE user_id=?
");

$stmt->execute([$user]);

$cartItems = $stmt->fetchColumn();

if(!$cartItems){
    $cartItems=0;
}

/* Reviews */

$stmt = $conn->prepare("
SELECT COUNT(*)
FROM reviews
WHERE user_id=?
");

$stmt->execute([$user]);

$totalReviews = $stmt->fetchColumn();

?>

<div class="container py-5" style="margin-top:120px;">

<h1 class="fw-bold">

Welcome,

<?php

echo htmlspecialchars($_SESSION['full_name']);

?>

👋

</h1>

<div class="row mt-5">

<div class="col-lg-4">

<div class="dashboard-card">

<h2>

<?php echo $totalOrders; ?>

</h2>

<p>Total Orders</p>

</div>

</div>

<div class="col-lg-4">

<div class="dashboard-card">

<h2>

<?php echo $cartItems; ?>

</h2>

<p>Cart Items</p>

</div>

</div>

<div class="col-lg-4">

<div class="dashboard-card">

<h2>

<?php echo $totalReviews; ?>

</h2>

<p>Reviews</p>

</div>

</div>

</div>

<hr class="my-5">

<h2>

Recent Orders

</h2>

<?php

$stmt=$conn->prepare("
SELECT *
FROM orders
WHERE user_id=?
ORDER BY id DESC
LIMIT 5
");

$stmt->execute([$user]);

while($row=$stmt->fetch(PDO::FETCH_ASSOC)){

?>

<div class="card shadow-sm border-0 rounded-4 mb-3">

<div class="card-body d-flex justify-content-between">

<div>

<h5>

Order #

<?php echo $row['id']; ?>

</h5>

<p>

<?php echo $row['payment_method']; ?>

</p>

</div>

<div>

<h5>

৳

<?php echo number_format($row['total_price'],2); ?>

</h5>

<span class="badge bg-warning">

<?php echo $row['status']; ?>

</span>

</div>

</div>

</div>

<?php

}

?>

</div>

<?php

include 'includes/footer.php';
include 'includes/scripts.php';

?>
