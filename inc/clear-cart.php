<?php
session_start();

// Очищаем массив корзины в сессии
if (isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Возвращаем JSON ответ, если это потребуется для отладки
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'Cart cleared']);
exit;