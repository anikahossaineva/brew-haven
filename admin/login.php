<?php
require '../config/database.php';
if (!empty($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Refresh and try again.';
    } else {
        $query = $conn->prepare('SELECT id,name,password FROM admins WHERE email = ? LIMIT 1');
        $query->execute([$email]);
        $admin = $query->fetch();
        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            header('Location: index.php');
            exit;
        }
        $error = 'Email or password is incorrect.';
    }
}
include '../includes/header.php';
?>
<main class="container py-5" style="max-width:520px;margin-top:100px"><div class="card border-0 shadow-sm rounded-4"><div class="card-body p-5"><p class="section-tag">Brew Haven</p><h1 class="h3 mb-4">Administrator sign in</h1><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="mb-3"><label class="form-label" for="admin-email">Email</label><input class="form-control" id="admin-email" type="email" name="email" maxlength="190" required></div><div class="mb-4"><label class="form-label" for="admin-password">Password</label><input class="form-control" id="admin-password" type="password" name="password" required></div><button class="btn btn-coffee w-100">Sign in</button></form></div></div></main>
<?php include '../includes/scripts.php'; ?>
