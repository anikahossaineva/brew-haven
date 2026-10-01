<?php
declare(strict_types=1);
require '../config/database.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Refresh the page and try again.']);
    exit;
}
$email = trim((string) ($_POST['email'] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Enter a valid email address.']);
    exit;
}
$insert = $conn->prepare('INSERT IGNORE INTO newsletter (email) VALUES (?)');
$insert->execute([$email]);
echo json_encode(['success' => true, 'message' => $insert->rowCount() ? 'Thanks for subscribing.' : 'This email is already subscribed.']);
