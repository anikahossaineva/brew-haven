<?php

require 'config/database.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $message = "<div class='alert alert-danger'>Your session expired. Please refresh and try again.</div>";
    } else {

    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if(empty($email) || empty($password)){

        $message = "<div class='alert alert-danger'>Please fill all fields.</div>";

    }else{

        $stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);

        $user = $stmt->fetch();
        if ($user) {

            if(password_verify($password,$user['password'])){

                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                header("Location:index.php");
                exit();

            }else{

                $message="<div class='alert alert-danger'>Email or password is incorrect.</div>";

            }

        }else{

            $message="<div class='alert alert-danger'>Email or password is incorrect.</div>";

        }

    }

    }

}

include 'includes/header.php';
include 'includes/navbar.php';

?>

<div class="container py-5" style="margin-top:120px;max-width:600px;">

<div class="card shadow-lg">

<div class="card-body p-5">

<h2 class="text-center mb-4">
Welcome Back
</h2>

<?= $message ?>

<form method="POST">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="mb-4">

<label>Password</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<button
class="btn btn-coffee w-100"
name="login">

Login

</button>

</form>

<p class="text-center mt-4">

Don't have an account?

<a href="register.php">

Register

</a>

</p>

</div>

</div>

</div>

<?php

include 'includes/footer.php';
include 'includes/scripts.php';

?>
