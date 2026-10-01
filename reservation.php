<?php
require 'config/database.php';
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['customer_name'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $guests = filter_input(INPUT_POST, 'guests', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20]]);
    $date = (string) ($_POST['date'] ?? '');
    $time = (string) ($_POST['time'] ?? '');
    $request = trim((string) ($_POST['special_request'] ?? ''));
    $reservationAt = DateTime::createFromFormat('!Y-m-d H:i', $date . ' ' . $time);
    $dateErrors = DateTime::getLastErrors();
    $dateValid = $reservationAt && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0));
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $notice = 'Your session expired. Refresh the page and try again.';
    } elseif ($name === '' || mb_strlen($name) > 120 || $phone === '' || mb_strlen($phone) > 30 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$guests || !$dateValid || $reservationAt < new DateTime('now') || mb_strlen($request) > 1000) {
        $notice = 'Please provide valid reservation details. Reservations must be in the future.';
    } else {
        $insert = $conn->prepare('INSERT INTO reservations (user_id,customer_name,phone,email,guests,reservation_at,special_request) VALUES (?,?,?,?,?,?,?)');
        $insert->execute([$_SESSION['user_id'] ?? null, $name, $phone, $email, $guests, $reservationAt->format('Y-m-d H:i:s'), $request ?: null]);
        $notice = 'Your reservation request has been received. We’ll contact you to confirm it.';
    }
}
include 'includes/header.php';
include 'includes/navbar.php';
?>
<main class="container py-5" style="margin-top:110px;max-width:850px"><header class="text-center mb-4"><span class="section-tag">Save your seat</span><h1 class="section-title">Make a Reservation</h1><p class="section-subtitle">Send a request and our team will confirm availability.</p></header><div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4 p-lg-5"><?php if ($notice): ?><div class="alert alert-info" role="status"><?= e($notice) ?></div><?php endif; ?><form method="post" class="row g-3"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="col-md-6"><label for="guest-name" class="form-label">Customer name</label><input class="form-control" id="guest-name" name="customer_name" maxlength="120" value="<?= e($_SESSION['full_name'] ?? '') ?>" required></div><div class="col-md-6"><label for="guest-phone" class="form-label">Phone</label><input class="form-control" id="guest-phone" name="phone" maxlength="30" required></div><div class="col-md-6"><label for="guest-email" class="form-label">Email</label><input class="form-control" id="guest-email" name="email" type="email" maxlength="190" required></div><div class="col-md-6"><label for="guest-count" class="form-label">Guests</label><input class="form-control" id="guest-count" name="guests" type="number" min="1" max="20" value="2" required></div><div class="col-md-6"><label for="reservation-date" class="form-label">Date</label><input class="form-control" id="reservation-date" name="date" type="date" min="<?= date('Y-m-d') ?>" required></div><div class="col-md-6"><label for="reservation-time" class="form-label">Time</label><input class="form-control" id="reservation-time" name="time" type="time" min="08:00" max="21:30" required></div><div class="col-12"><label for="special-request" class="form-label">Special request</label><textarea class="form-control" id="special-request" name="special_request" rows="3" maxlength="1000"></textarea></div><div class="col-12"><button class="btn btn-coffee" type="submit">Request a table</button></div></form></div></div></main>
<?php include 'includes/footer.php'; include 'includes/scripts.php'; ?>
