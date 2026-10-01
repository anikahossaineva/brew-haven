<?php

require 'config/database.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $message = "<div class='alert alert-danger'>Your session expired. Please refresh and try again.</div>";
    } else {

    $full_name = is_string($_POST['full_name'] ?? null) ? trim($_POST['full_name']) : '';
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $phone = is_string($_POST['phone'] ?? null) ? trim($_POST['phone']) : '';
    $address = is_string($_POST['address'] ?? null) ? trim($_POST['address']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirm_password = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

    // Validation
    if ($full_name === '' || $email === '' || $phone === '' || $address === '' || $password === '' || $confirm_password === '') {

        $message = "<div class='alert alert-danger'>All fields are required.</div>";

    } elseif (mb_strlen($full_name) > 120 || mb_strlen($email) > 190 || mb_strlen($phone) > 30 || mb_strlen($address) > 500) {
        $message = "<div class='alert alert-danger'>One or more fields are too long.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert alert-danger'>Enter a valid email address.</div>";
    } elseif (strlen($password) < 10 || strlen($password) > 200) {
        $message = "<div class='alert alert-danger'>Use a password between 10 and 200 characters.</div>";
    } elseif (!hash_equals($password, $confirm_password)) {

        $message = "<div class='alert alert-danger'>Passwords do not match.</div>";

    }else{

        // Check email
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->execute([$email]);

        if($check->fetchColumn()){

            $message = "<div class='alert alert-warning'>Email already exists.</div>";

        }else{

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users(full_name,email,phone,password,address,role)
                    VALUES(?,?,?,?,?,?)";

            $stmt = $conn->prepare($sql);

            $stmt->execute([
                $full_name,
                $email,
                $phone,
                $hashedPassword,
                $address,
                'customer'
            ]);

            $message = "<div class='alert alert-success'>Registration successful. You can now log in.</div>";

        }

    }

    }

}

include 'includes/header.php';
include 'includes/navbar.php';

?>

<div class="container py-5" style="margin-top:120px; max-width:650px;">

    <div class="card shadow-lg border-0 rounded-4">

        <div class="card-body p-5">

            <h2 class="text-center mb-4">
                Create Your Account
            </h2>

            <?= $message ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <div class="mb-3">

                    <label class="form-label">
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="full_name"
                        class="form-control"
                        maxlength="120" required>

                </div>

                <div class="mb-3">

                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        maxlength="190" required>

                </div>

                <div class="mb-3">

                    <label>Phone</label>

                    <input
                        type="text"
                        name="phone"
                        class="form-control"
                        maxlength="30" required>

                </div>

                <div class="mb-3">

                    <label>Address</label>

                    <textarea
                        name="address"
                        class="form-control"
                        rows="3"
                        maxlength="500" required></textarea>

                </div>

                <div class="mb-3">

                    <label>Password</label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        minlength="10" required>

                </div>

                <div class="mb-4">

                    <label>Confirm Password</label>

                    <input
                        type="password"
                        name="confirm_password"
                        class="form-control"
                        required>

                </div>

                <button
                    type="submit"
                    name="register"
                    class="btn btn-coffee w-100">

                    Register

                </button>

            </form>

            <p class="text-center mt-4">

                Already have an account?

                <a href="login.php">
                    Login Here
                </a>

            </p>

        </div>

    </div>

</div>

<?php

include 'includes/footer.php';
include 'includes/scripts.php';

?>
