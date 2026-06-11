<?php
session_start();
require_once 'inc/db.php';
include 'inc/styleHelper.php'; 

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$item = ($id) ? getProductById($id) : null;

if (!$item) {
    die("Товар не найден.");
}

$isOutOfStock = !empty($item['out_of_stock']);
include 'header.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?= asset('public/css/style.css') ?>">

    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="public/js/shop.js" defer></script>
</head>

<main class="product-main-container" id="product-page-<?= $item['id'] ?>">
    <h1 class="product-header-title" id="product-title"><?= htmlspecialchars($item['title']) ?></h1>

    <div class="product-top-section">
        <?php if (!empty($item['images'])): ?>
            <div class="product-gallery" id="gallery">
                <div class="main-image-view">
                    <img id="current-main-img" src="public/<?= ltrim($item['images'][0], '/') ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                </div>

                <div class="thumbnail-list" id="thumb-list">
                    <?php foreach ($item['images'] as $index => $img): ?>
                        <img src="public/<?= ltrim($img, '/') ?>" 
                             id="thumb-<?= $index ?>"
                             class="thumb-item <?= $index === 0 ? 'active' : '' ?>" 
                             onclick="changeMainImage(this.src, this)">
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

 <aside class="product-sidebar">
    <div class="price-buy-card">
        <div class="price-buy-content">
            <a href="index.php" class="back-btn-custom sidebar-back-btn" id="product-back-btn" onclick="goBack(event)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:24px; height:24px;">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>

            <div class="price-display">
                <div class="price-tag" id="product-price"><?= number_format($item['price'], 0, '.', ' ') ?> р.</div>
            </div>

            <button type="button" 
                    id="add-to-cart-btn"
                    class="main-buy-btn-green <?= $isOutOfStock ? 'btn-disabled' : '' ?>" 
                    <?= $isOutOfStock ? 'disabled' : "onclick=\"addToCart(event, '{$item['id']}')\"" ?>>
                <?= $isOutOfStock ? 'Нет в наличии' : 'Купить'; ?>
            </button>
        </div>
    </div>
</aside>

    <section class="product-bottom-section">
        <div class="description-card">
            <h3>Описание</h3>
            <div class="description-content" id="product-description"><?= nl2br(htmlspecialchars($item['description'])) ?></div>
        </div>
    </section>
</main>

<script>
    function changeMainImage(src, thumbElement) {
        const mainImg = document.getElementById('current-main-img');
        if (mainImg) mainImg.src = src;
        document.querySelectorAll('.thumb-item').forEach(img => img.classList.remove('active'));
        thumbElement.classList.add('active');
    }
    function goBack(event) {
        if (window.history.length > 1) {
            event.preventDefault();
            window.history.back();
        }
    }
</script>