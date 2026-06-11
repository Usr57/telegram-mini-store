<?php
session_start();
require_once 'inc/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $productId = (int)$_POST['id'];
    
    $foundProduct = getProductById($productId);

    if ($foundProduct) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $image = (!empty($foundProduct['images']) && isset($foundProduct['images'][0])) 
                 ? $foundProduct['images'][0] 
                 : 'uploads/no-photo.png';

        $_SESSION['cart'][] = [
            'cartId' => uniqid(), 
            'id'     => $foundProduct['id'],
            'title'  => $foundProduct['title'],
            'price'  => $foundProduct['price'],
            'image'  => $image
        ];

        echo json_encode(['newTotal' => count($_SESSION['cart'])]);
        exit;
    }
}
http_response_code(400);
echo json_encode(['error' => 'Товар не найден']);