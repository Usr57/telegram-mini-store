<?php
require_once 'inc/db.php';
include_once 'inc/styleHelper.php';
$manager_user = getSetting('manager_username');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Shop</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
</head>
<body>

<header class="main-header" id="main-header">
    <div class="header-content">
        <div class="header-main-row">
            
            <form action="index.php" method="GET" class="search-form-inline" id="filterForm">
                <button type="button" class="filter-icon-btn" id="btn-open-filters" onclick="toggleFilterMenu()">
                    <img src="public/img/filter.png" alt="Фильтры">
                </button>

                <div class="search-wrapper-inline">
                    <input type="text" name="search" id="search-input" class="search-input-mini" 
                           placeholder="Поиск..." 
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>

                <div id="filter-dropdown" class="filter-menu-dropdown">
                    <div class="filter-menu-content">
                        <div class="filter-group">
                            <label for="filter-category">Категория</label>
                            <select name="category" id="filter-category" class="filter-select">
                                <option value="all">Все</option>
                                <?php 
                                $categories = getCategories();
                                foreach ($categories as $cat): 
                                    $selected = (isset($_GET['category']) && $_GET['category'] == $cat['name']) ? 'selected' : '';
                                ?>
                                    <option value="<?= htmlspecialchars($cat['name']) ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter-sort">Сортировка</label>
                            <?php $currentSort = $_GET['sort'] ?? 'newest'; ?>
                            <select name="sort" id="filter-sort" class="filter-select">
                                <option value="newest" <?= $currentSort == 'newest' ? 'selected' : '' ?>>Сначала новые</option>
                                <option value="cheap" <?= $currentSort == 'cheap' ? 'selected' : '' ?>>Сначала дешевые</option>
                                <option value="expensive" <?= $currentSort == 'expensive' ? 'selected' : '' ?>>Сначала дорогие</option>
                            </select>
                        </div>
                        <div class="stock-filter-container">
                            <input type="checkbox" name="stock_only" id="stock_only" value="1" 
                                   <?= (isset($_GET['stock_only']) && $_GET['stock_only'] == '1') ? 'checked' : '' ?> 
                                   class="stock-checkbox">
                            <label for="stock_only" class="stock-label">Только в наличии</label>
                        </div>

                        <button type="submit" class="apply-filters-btn" id="btn-apply-filters">Применить</button>
                        <button type="button" class="close-filter-btn" id="btn-close-filters" onclick="toggleFilterMenu()">Закрыть</button>
                    </div>
                </div>
            </form>

            <div class="header-right-icons">
               <a href="https://t.me/<?= htmlspecialchars($manager_user) ?>" class="icon-btn" id="tg-contact-link">
               <img src="public/img/telega.png" alt="TG">
               </a>
                <a href="cart.php" class="icon-btn cart-link" id="cart-nav-link">
                    <img src="public/img/cart.png" alt="Cart">
                    <?php $totalCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                    <span id="cart-badge" class="badge-count" <?= ($totalCount <= 0) ? 'style="display: none;"' : '' ?>>
                        <?= $totalCount ?>
                    </span>
                </a>
            </div>

        </div>
    </div>
</header>

<div class="header-spacer" id="header-spacer"></div>

<script>
    const tg = window.Telegram.WebApp;
    tg.expand();

    if (tg.initData !== "") {
        document.body.classList.add('is-tg');
    }

    function toggleFilterMenu() {
        const menu = document.getElementById('filter-dropdown');
        menu.classList.toggle('show');
    }

    window.onclick = function(event) {
        if (!event.target.closest('.search-form-inline')) {
            const menu = document.getElementById('filter-dropdown');
            if (menu && menu.classList.contains('show')) {
                menu.classList.remove('show');
            }
        }
    }
</script>
</body>
</html>