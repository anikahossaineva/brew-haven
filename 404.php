<?php
http_response_code(404);
require 'config/database.php';
include 'includes/header.php'; include 'includes/navbar.php';
?>
<main class="container text-center py-5" style="margin-top:130px;min-height:55vh"><span class="section-tag">404 · Page not found</span><h1 class="display-2 fw-bold">That page wandered off</h1><p class="lead text-muted">Let’s get you back to something freshly brewed.</p><a class="btn btn-coffee" href="index.php">Back to home</a></main>
<?php include 'includes/footer.php'; include 'includes/scripts.php'; ?>
