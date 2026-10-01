<?php
require 'config/database.php';
$photos = $conn->query('SELECT image_path, title, category FROM gallery WHERE is_active = 1 ORDER BY sort_order, id DESC')->fetchAll();
include 'includes/header.php';
include 'includes/navbar.php';
?>
<main class="container py-5" style="margin-top:110px"><header class="text-center mb-5"><span class="section-tag">A glimpse inside</span><h1 class="section-title">Brew Haven Gallery</h1><p class="section-subtitle">Little moments, lovingly made.</p></header><div class="row g-4"><?php foreach ($photos as $photo): ?><div class="col-sm-6 col-lg-4"><figure class="card border-0 shadow-sm rounded-4 overflow-hidden"><img src="<?= e($photo['image_path']) ?>" class="img-fluid" style="height:280px;object-fit:cover" loading="lazy" alt="<?= e($photo['title']) ?>"><figcaption class="card-body"><small class="text-muted"><?= e($photo['category']) ?></small><h2 class="h5 mb-0"><?= e($photo['title']) ?></h2></figcaption></figure></div><?php endforeach; ?><?php if (!$photos): ?><p class="text-center">Our gallery is being updated. Please visit again soon.</p><?php endif; ?></div></main>
<?php include 'includes/footer.php'; include 'includes/scripts.php'; ?>
