<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cartId'])) {
    $cartId = $_POST['cartId'];

    if (isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($cartId) {
            return $item['cartId'] !== $cartId;
        });
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += (float)$item['price'];
        }

        echo json_encode([
            'status' => 'success',
            'count' => count($_SESSION['cart']),
            'total' => number_format($total, 0, '.', ' ') . ' руб.'
        ]);
        exit;
    }
}

echo json_encode(['status' => 'error']);