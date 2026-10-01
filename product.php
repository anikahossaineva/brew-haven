<?php

include 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';

if(!isset($_GET['id'])){

    header("Location:index.php");
    exit();

}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$product){

    echo "<h2 class='text-center mt-5'>Product Not Found</h2>";

    include 'includes/footer.php';
    include 'includes/scripts.php';
    exit();

}

?>

<div class="container py-5" style="margin-top:120px;">

<div class="row align-items-center">

<div class="col-lg-6">

<img
src="assets/images/menu/<?php echo htmlspecialchars($product['image']); ?>"
class="img-fluid rounded shadow">

</div>

<div class="col-lg-6">

<h1 class="fw-bold">

<?php echo htmlspecialchars($product['product_name']); ?>

</h1>

<p class="text-muted mt-4">

<?php echo htmlspecialchars($product['description']); ?>

</p>

<h2 class="text-success my-4">

$<?php echo number_format($product['price'],2); ?>

</h2>

<p>

<strong>Stock:</strong>

<?php echo $product['stock']; ?>

</p>

<button class="btn btn-coffee btn-lg mt-3">

<i class="fa-solid fa-cart-shopping"></i>

Add to Cart

</button>

</div>

</div>

</div>

<?php

include 'includes/footer.php';
include 'includes/scripts.php';

?>