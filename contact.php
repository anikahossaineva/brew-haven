<?php
require 'config/database.php';
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $notice = 'Your session expired. Refresh the page and try again.';
    } elseif ($name === '' || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190 || $subject === '' || mb_strlen($subject) > 180 || $message === '' || mb_strlen($message) > 5000) {
        $notice = 'Please check the form fields and try again.';
    } else {
        $insert = $conn->prepare("INSERT INTO contact_messages (name,email,subject,message) VALUES (?,?,?,?)");
        $insert->execute([$name, $email, $subject, $message]);
        $notice = 'Thanks for reaching out. Your message has been received.';
    }
}
include 'includes/header.php';
include 'includes/navbar.php';
?>
<main class="container py-5" style="margin-top:110px"><div class="row justify-content-center g-5"><div class="col-lg-5"><span class="section-tag">We’d love to hear from you</span><h1 class="section-title">Get in touch</h1><p>Questions about the menu or planning a visit? Send us a note and our café team will get back to you.</p><h2 class="h5 mt-4">Opening hours</h2><p class="mb-1">Monday–Sunday</p><p>8:00 AM – 10:00 PM</p><p class="small text-muted">Add the café’s verified phone, email and address in Settings before publishing contact details.</p></div><div class="col-lg-6"><div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4 p-lg-5"><?php if ($notice): ?><div class="alert alert-info" role="status"><?= e($notice) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="mb-3"><label class="form-label" for="contact-name">Name</label><input class="form-control" id="contact-name" name="name" maxlength="120" required></div><div class="mb-3"><label class="form-label" for="contact-email">Email</label><input class="form-control" id="contact-email" type="email" name="email" maxlength="190" required></div><div class="mb-3"><label class="form-label" for="contact-subject">Subject</label><input class="form-control" id="contact-subject" name="subject" maxlength="180" required></div><div class="mb-3"><label class="form-label" for="contact-message">Message</label><textarea class="form-control" id="contact-message" name="message" rows="5" maxlength="5000" required></textarea></div><button class="btn btn-coffee" type="submit">Send message</button></form></div></div></div></div></main>
<?php include 'includes/footer.php'; include 'includes/scripts.php'; ?>
