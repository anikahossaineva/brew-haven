<?php
include 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';

include 'includes/home/hero.php';
//include 'includes/home/features.php';
include 'includes/home/popular.php';
include 'includes/home/why-us.php';
include 'includes/home/discover.php';
// include 'includes/home/story.php';
//include 'includes/home/about.php';
include 'includes/home/categories.php';
//include 'includes/home/popular.php';
include 'includes/home/special.php';
include 'includes/home/testimonials.php';
include 'includes/home/gallery.php';

?>
<section class="py-5 text-center text-white" style="background:#4e342e">
    <div class="container py-3">
        <h2 class="fw-bold">Make a little time for good coffee</h2>
        <p class="mb-4">Come by for a table, a catch-up, or a cup to go.</p>
        <a class="btn btn-light" href="reservation.php">Request a reservation</a>
    </div>
</section>
<?php

include 'includes/footer.php';
include 'includes/scripts.php';
?>
