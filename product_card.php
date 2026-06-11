<?php
include 'inc/styleHelper.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вейпшоп</title>

     <link rel="stylesheet" href="<?= asset('public/css/style.css') ?>">
     <link rel="stylesheet" href="<?= asset('public/css/styleProductCard.css') ?>">
    
     <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
</head>
<?php foreach ($products as $item): ?>
    <div class="product-card" 
         id="product-card-<?= $item['id']; ?>"
         data-title="<?= mb_strtolower(htmlspecialchars($item['title'])) ?>" 
         data-category="<?= htmlspecialchars($item['category_name'] ?? 'Общее') ?>">
        
        <?php 
        $discountPercentage = 0;
        if (!empty($item['old_price']) && $item['old_price'] > $item['price']) {
            $discountPercentage = round((($item['old_price'] - $item['price']) / $item['old_price']) * 100);
        }
        $isOutOfStock = !empty($item['out_of_stock']); 
        ?>

        <a href="product.php?id=<?= $item['id']; ?>" class="product-link" id="product-link-<?= $item['id']; ?>">
            <div class="product-image-container">
                <?php if ($discountPercentage > 0): ?>
                    <div class="discount-badge">-<?= $discountPercentage; ?>%</div>
                <?php endif; ?>

                <?php if (!empty($item['images']) && isset($item['images'][0])): ?>
                    <?php $imagePath = 'public/' . ltrim($item['images'][0], '/'); ?>
                    <img src="<?= htmlspecialchars($imagePath); ?>" class="product-image" loading="lazy">
                <?php else: ?>
                    <div class="card-image-placeholder">
                        <span>Без фото</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <h2 class="product-title" id="product-title-<?= $item['id']; ?>"><?= htmlspecialchars($item['title']); ?></h2>
        </a>

       <div class="product-footer">
            <div class="price-block">
                <?php if (!empty($item['old_price'])): ?>
                    <span class="old-price"><?= number_format($item['old_price'], 0, '', ' '); ?> Руб</span>
                <?php endif; ?>
                <span class="price"><?= number_format($item['price'], 0, '', ' '); ?> руб</span>
            </div>
            
            <button type="button" class="buy-btn" 
                id="buy-btn-<?= $item['id']; ?>"
                <?= $isOutOfStock ? 'disabled' : "onclick=\"addToCart(event, '{$item['id']}')\"" ?>
                style="<?= $isOutOfStock ? 'background-color: #b2bec3; border-color: #b2bec3;' : '' ?>">
                <span><?= $isOutOfStock ? 'Нет в наличии' : 'В корзину'; ?></span>
            </button>
        </div>
    </div>
<?php endforeach; ?>