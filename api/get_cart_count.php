<?php
declare(strict_types=1);
require '../config/database.php';
header('Content-Type: application/json; charset=utf-8');
if (empty($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}
$query = $conn->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?');
$query->execute([$_SESSION['user_id']]);
echo json_encode(['count' => (int) $query->fetchColumn()]);
