<?php
require 'config/database.php';
$categories = $conn->query('SELECT id, category_name FROM categories WHERE is_active = 1 ORDER BY sort_order, category_name')->fetchAll();
$categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?: 0;
$search = trim(substr((string) ($_GET['q'] ?? ''), 0, 80));
$sortOptions = ['popular' => 'p.is_featured DESC, p.product_name', 'price-low' => 'p.price ASC', 'price-high' => 'p.price DESC', 'name' => 'p.product_name ASC'];
$sort = $_GET['sort'] ?? 'popular';
$orderBy = $sortOptions[$sort] ?? $sortOptions['popular'];
$sql = 'SELECT p.id, p.product_name, p.description, p.price, p.image, p.stock, c.category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.is_available = 1';
$params = [];
if ($categoryId) { $sql .= ' AND p.category_id = ?'; $params[] = $categoryId; }
if ($search !== '') { $sql .= ' AND (p.product_name LIKE ? OR p.description LIKE ?)'; $params[] = '%' . $search . '%'; $params[] = '%' . $search . '%'; }
$sql .= ' ORDER BY ' . $orderBy;
$query = $conn->prepare($sql);
$query->execute($params);
$products = $query->fetchAll();
include 'includes/header.php';
include 'includes/navbar.php';
?>
<main class="container py-5" style="margin-top:110px">
  <header class="text-center mb-5"><span class="section-tag">Made fresh, served with care</span><h1 class="section-title">The Menu</h1><p class="section-subtitle">Find a familiar favourite or discover something new.</p></header>
  <form class="row g-2 align-items-end mb-4" method="get" role="search">
    <div class="col-md-5"><label class="form-label" for="menu-search">Search menu</label><input class="form-control" id="menu-search" name="q" value="<?= e($search) ?>" placeholder="Coffee, cake, smoothie…"></div>
    <div class="col-md-3"><label class="form-label" for="menu-category">Category</label><select class="form-select" id="menu-category" name="category"><option value="0">All categories</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['category_name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label class="form-label" for="menu-sort">Sort by</label><select class="form-select" name="sort" id="menu-sort"><option value="popular">Featured</option><option value="price-low" <?= $sort === 'price-low' ? 'selected' : '' ?>>Price: low to high</option><option value="price-high" <?= $sort === 'price-high' ? 'selected' : '' ?>>Price: high to low</option><option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name</option></select></div>
    <div class="col-md-2"><button class="btn btn-coffee w-100" type="submit">Find items</button></div>
  </form>
  <div class="row g-4"><?php foreach ($products as $product): ?><div class="col-sm-6 col-lg-4"><article class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden"><img class="card-img-top" style="height:230px;object-fit:cover" loading="lazy" src="assets/images/menu/<?= e($product['image'] ?: 'latte.jpg') ?>" alt="<?= e($product['product_name']) ?>"><div class="card-body d-flex flex-column"><small class="text-uppercase text-muted"><?= e($product['category_name']) ?></small><h2 class="h5 mt-2"><?= e($product['product_name']) ?></h2><p class="text-muted flex-grow-1"><?= e($product['description']) ?></p><div class="d-flex justify-content-between align-items-center"><strong>৳<?= number_format((float) $product['price'], 2) ?></strong><button class="btn btn-coffee add-cart-btn" data-id="<?= (int) $product['id'] ?>" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>><?= (int) $product['stock'] ? 'Add to cart' : 'Sold out' ?></button></div></div></article></div><?php endforeach; ?>
  <?php if (!$products): ?><div class="col-12 text-center py-5"><h2 class="h4">No menu items found</h2><p>Try a different search or category.</p></div><?php endif; ?></div>
</main>
<?php include 'includes/footer.php'; include 'includes/scripts.php'; ?>
