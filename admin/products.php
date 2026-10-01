<?php
require '../config/database.php';
if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
$message = '';
$images = ['latte.jpg', 'mocha.jpg', 'cappuccino.jpg'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $message = 'Session expired. Refresh and try again.';
    } elseif ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id) {
            $delete = $conn->prepare('DELETE FROM products WHERE id = ?');
            $delete->execute([$id]);
            $message = $delete->rowCount() ? 'Product deleted.' : 'Product not found.';
        }
    } elseif ($action === 'save') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        $name = trim((string) ($_POST['product_name'] ?? ''));
        $category = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $description = trim((string) ($_POST['description'] ?? ''));
        $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
        $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $image = (string) ($_POST['image'] ?? '');
        if ($name === '' || mb_strlen($name) > 160 || !$category || $description === '' || mb_strlen($description) > 5000 || $price === false || $price < 0 || $stock === false || !in_array($image, $images, true)) {
            $message = 'Check the product fields and try again.';
        } else {
            $slugBase = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $name), '-')) ?: 'item';
            $slug = $id ? $slugBase . '-' . $id : $slugBase . '-' . bin2hex(random_bytes(3));
            if ($id) {
                $save = $conn->prepare('UPDATE products SET category_id=?,product_name=?,slug=?,description=?,price=?,image=?,stock=?,is_available=?,is_featured=? WHERE id=?');
                $save->execute([$category,$name,$slug,$description,$price,$image,$stock,isset($_POST['is_available'])?1:0,isset($_POST['is_featured'])?1:0,$id]);
            } else {
                $save = $conn->prepare('INSERT INTO products (category_id,product_name,slug,description,price,image,stock,is_available,is_featured) VALUES (?,?,?,?,?,?,?,?,?)');
                $save->execute([$category,$name,$slug,$description,$price,$image,$stock,isset($_POST['is_available'])?1:0,isset($_POST['is_featured'])?1:0]);
            }
            $message = 'Product saved.';
        }
    }
}
$categories = $conn->query('SELECT id,category_name FROM categories WHERE is_active=1 ORDER BY sort_order,category_name')->fetchAll();
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$editing = null;
if ($editId) { $query = $conn->prepare('SELECT * FROM products WHERE id=?'); $query->execute([$editId]); $editing = $query->fetch(); }
$products = $conn->query('SELECT p.id,p.product_name,p.price,p.stock,p.is_available,c.category_name FROM products p JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC')->fetchAll();
include '../includes/header.php';
?>
<style>body{background:#f6f4f1}.admin-side{background:#241a15;min-height:100vh}.admin-side a{color:#f5eee8;text-decoration:none}</style>
<div class="container-fluid"><div class="row"><aside class="col-md-3 col-xl-2 admin-side text-white p-4"><a href="index.php" class="h4">Brew Haven</a><nav class="d-grid gap-3 mt-4"><a href="index.php">Overview</a><a href="products.php">Products</a><a href="../index.php">View storefront</a><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="btn btn-link p-0 text-white text-decoration-none">Sign out</button></form></nav></aside><main class="col-md-9 col-xl-10 p-4 p-lg-5"><div class="d-flex justify-content-between align-items-center mb-4"><div><p class="text-muted mb-1">Catalogue</p><h1 class="h2">Products</h1></div><a class="btn btn-outline-dark" href="products.php">New product</a></div><?php if ($message): ?><div class="alert alert-info" role="status"><?= e($message) ?></div><?php endif; ?><div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4"><h2 class="h5"><?= $editing ? 'Edit product' : 'Add a product' ?></h2><form method="post" class="row g-3"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>"><div class="col-md-6"><label class="form-label" for="product-name">Name</label><input class="form-control" id="product-name" name="product_name" maxlength="160" required value="<?= e($editing['product_name'] ?? '') ?>"></div><div class="col-md-6"><label class="form-label" for="product-category">Category</label><select class="form-select" id="product-category" name="category_id" required><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) ($editing['category_id'] ?? 0)===(int)$category['id']?'selected':'' ?>><?= e($category['category_name']) ?></option><?php endforeach; ?></select></div><div class="col-md-8"><label class="form-label" for="product-description">Description</label><textarea class="form-control" id="product-description" name="description" maxlength="5000" required><?= e($editing['description'] ?? '') ?></textarea></div><div class="col-md-4"><label class="form-label" for="product-image">Image</label><select class="form-select" id="product-image" name="image"><?php foreach ($images as $image): ?><option value="<?= e($image) ?>" <?= ($editing['image'] ?? '')===$image?'selected':'' ?>><?= e($image) ?></option><?php endforeach; ?></select></div><div class="col-md-4"><label class="form-label" for="product-price">Price (৳)</label><input class="form-control" id="product-price" type="number" name="price" min="0" step="0.01" required value="<?= e((string)($editing['price'] ?? '')) ?>"></div><div class="col-md-4"><label class="form-label" for="product-stock">Stock</label><input class="form-control" id="product-stock" type="number" name="stock" min="0" required value="<?= e((string)($editing['stock'] ?? 0)) ?>"></div><div class="col-md-4 d-flex gap-3 align-items-end pb-2"><label><input type="checkbox" name="is_available" <?= !isset($editing['is_available']) || (int)$editing['is_available']===1?'checked':'' ?>> Available</label><label><input type="checkbox" name="is_featured" <?= (int)($editing['is_featured'] ?? 0)===1?'checked':'' ?>> Featured</label></div><div class="col-12"><button class="btn btn-coffee" type="submit">Save product</button></div></form></div></div><div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($products as $product): ?><tr><td><?= e($product['product_name']) ?></td><td><?= e($product['category_name']) ?></td><td>৳<?= number_format((float)$product['price'],2) ?></td><td><?= (int)$product['stock'] ?></td><td><?= (int)$product['is_available']?'Available':'Hidden' ?></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-dark" href="?edit=<?= (int)$product['id'] ?>">Edit</a><form method="post" class="d-inline" onsubmit="return confirm('Delete this product?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$product['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div></div></div></main></div></div>
<?php include '../includes/scripts.php'; ?>
