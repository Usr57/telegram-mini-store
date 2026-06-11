<?php
session_start();
require_once 'inc/db.php';
include 'inc/styleHelper.php';
$manager = getSetting('manager_username') ?: 'normgex';
$welcome_text = getSetting('checkout_message') ?: 'Привет! Хочу заказать:';

function pluralForm($number, $after) {
    $cases = array (2, 0, 1, 1, 1, 2);
    return $number . ' ' . $after[ ($number%100 > 4 && $number%100 < 20) ? 2 : $cases[min($number%10, 5)] ];
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

$orderText = $welcome_text . "\n\n";
if (!empty($cart)) {
    foreach ($cart as $item) {
        $cleanTitle = str_replace(['"', "'", '&', '#'], '', $item['title']);
        $orderText .= "• " . $cleanTitle . "\n";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Корзина — Магазин</title>

     <link rel="stylesheet" href="<?= asset('public/css/style.css') ?>">
      <link rel="stylesheet" href="<?= asset('public/css/styleCart.css') ?>">

    <script src="https://telegram.org/js/telegram-web-app.js"></script>
</head>
<body>
    <div class="container cart-page" id="cart-container">
        <header class="cart-header">
            <div class="header-actions">
                <h1 id="cart-header-title">Ваша корзина</h1>
            </div>
            <span class="count-badge" id="cart-count"><?= pluralForm(count($cart), ['товар', 'товара', 'товаров']) ?></span>
        </header>

        <?php if (!empty($cart)): ?>
            <div class="cart-list" id="cart-items-wrapper">
                <?php 
                $total = 0;
                foreach ($cart as $item): 
                    $price = (float)$item['price'];
                    $total += $price;
                ?>
                <div class="cart-item" id="cart-item-<?= $item['cartId'] ?>">
                    <div class="item-preview">
                        <?php 
                        $cleanPath = ltrim($item['image'], '/');
                        $fullPath = 'public/' . $cleanPath;
                        if (!empty($item['image']) && file_exists($fullPath)): 
                        ?>
                            <img src="<?= $fullPath ?>" alt="<?= htmlspecialchars($item['title']) ?>" id="cart-img-<?= $item['cartId'] ?>">
                        <?php endif; ?>
                    </div>
                    
                    <div class="item-info">
                        <div class="item-name" id="cart-title-<?= $item['cartId'] ?>"><?= htmlspecialchars($item['title']) ?></div>
                        <div class="item-price" id="cart-price-<?= $item['cartId'] ?>"><?= number_format($price, 0, '.', ' ') ?> руб.</div>
                    </div>
                    
                    <form action="inc/remove-from-cart.php" method="POST" class="remove-form" id="remove-form-<?= $item['cartId'] ?>" onsubmit="tg.HapticFeedback.impactOccurred('medium')">
                        <input type="hidden" name="cartId" value="<?= $item['cartId'] ?>">
                        <button type="submit" class="delete-btn" id="delete-btn-<?= $item['cartId'] ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                            </svg>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="total-section" id="total-section">
                <div class="total-row">
                    <span>Итого к оплате</span>
                    <strong id="total-amount"><?= number_format($total, 0, '.', ' ') ?> руб.</strong>
                </div>
            </div>
            
            <div class="checkout-card" id="checkout-card">
                <button onclick="sendOrder()" class="main-order-btn" id="submit-order-btn">Оформить заказ</button>
            </div>

        <?php else: ?>
            <div class="empty-cart" id="empty-cart-msg">
                <div class="empty-icon">🛒</div>
                <p>В корзине пока ничего нет</p>
                <a href="index.php" class="btn-primary" id="go-back-btn-empty">В каталог</a>
            </div>
        <?php endif; ?>

        <a href="index.php" class="floating-back" id="floating-back-btn" onclick="goBack(event)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:20px; height:20px;">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span class="btn-text" style="margin-left: 8px;">В каталог</span>
                </a>
        
    </div>

    <script>
    document.querySelectorAll('.remove-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        const formData = new FormData(this);
        const cartId = formData.get('cartId');
        const itemElement = document.getElementById(`cart-item-${cartId}`);

        fetch('inc/remove-from-cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                itemElement.style.opacity = '0';
                itemElement.style.transform = 'translateX(20px)';
                
                setTimeout(() => {
                    itemElement.remove();
                    
                    document.getElementById('cart-count').innerText = data.count + ' шт.';
                    document.getElementById('total-amount').innerText = data.total;

                    if (data.count === 0) {
                        location.reload();
                    }
                }, 300);
                
                tg.HapticFeedback.impactOccurred('medium');
            }
        });
    });
});
        const tg = window.Telegram.WebApp;
        tg.expand();

const welcomeText = <?php echo json_encode($welcome_text, JSON_UNESCAPED_UNICODE); ?>;

 function goBack(event) {
        if (window.history.length > 1) {
            event.preventDefault();
            window.history.back();
        }
    }

async function sendOrder() {
    const items = document.querySelectorAll('.cart-item');
    let itemsList = "";
    
    items.forEach(item => {
        const title = item.querySelector('.item-name').innerText;
        itemsList += "• " + title + "\n";
    });

    const message = welcomeText + "\n\n" + itemsList;
    const manager = <?php echo json_encode($manager, JSON_UNESCAPED_UNICODE); ?>;
    
    const webUrl = "https://t.me/" + manager + "?text=" + encodeURIComponent(message);
    const fallbackUrl = "tg://resolve?domain=" + manager + "&text=" + encodeURIComponent(message);
    
    tg.HapticFeedback.notificationOccurred('success');

    try {
        await fetch('inc/clear-cart.php');
    } catch (e) {
        console.error("Ошибка при очистке корзины:", e);
    }

    try {
        tg.openTelegramLink(webUrl);
    } catch (e) {
        window.location.href = fallbackUrl;
    }

    setTimeout(() => {
        location.reload();
    }, 1000);
}
    </script>
    
</body>
</html>