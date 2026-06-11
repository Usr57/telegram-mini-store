<?php
session_start();
require_once 'inc/db.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $productId = (int)$_POST['id'];
    if (deleteProduct($productId)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
exit;