<?php
session_start();
require_once 'inc/db.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['save_settings'])) {
    updateSetting('manager_username', htmlspecialchars($_POST['manager_username']));
    updateSetting('checkout_message', htmlspecialchars($_POST['checkout_message']));
    header('Location: admin5577.php?settings_saved=1');
    exit;
}

$manager_username = getSetting('manager_username') ?: 'ponyashka5577';
$checkout_message = getSetting('checkout_message') ?: '';

$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

$itemsPerPage = 100; 
$offset = ($currentPage - 1) * $itemsPerPage;
$products = getProducts($itemsPerPage, $offset, $search); 
$totalItems = getTotalProductsCount($search);
$totalPages = ceil($totalItems / $itemsPerPage);

if (isset($_POST['add_category'])) {
    $name = htmlspecialchars($_POST['new_cat_name']);
    if (!empty($name)) {
        addCategory($name);
    }
    header('Location: admin5577.php');
    exit;
}

if (isset($_POST['delete_cat_id'])) {
    deleteCategory($_POST['delete_cat_id']);
    header('Location: admin5577.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title']) && !isset($_POST['add_category'])) {
    $title = htmlspecialchars($_POST['title']);
    $category_id = $_POST['category_id'] !== "" ? (int)$_POST['category_id'] : null; 
    $price = (int)$_POST['price'];
    $old_price = !empty($_POST['old_price']) ? (int)$_POST['old_price'] : null;
    $desc = htmlspecialchars($_POST['desc']);
    $images = [];

    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = 'public/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $fileType = $_FILES['images']['type'][$key];
                if (in_array($fileType, $allowedTypes)) {
                    $fileName = time() . '_' . $key . '_' . $_FILES['images']['name'][$key];
                    if (move_uploaded_file($tmpName, $uploadDir . $fileName)) {
                        $images[] = '/uploads/' . $fileName;
                    }
                }
            }
        }
    }
    addProduct($title, $category_id, $price, $old_price, $desc, $images); 
    header('Location: admin5577.php');
    exit;
}

if (isset($_POST['delete_id'])) {
    deleteProduct($_POST['delete_id']);
    header("Location: admin5577.php?page=$currentPage&search=" . urlencode($search));
    exit;
}

$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Панель управления</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/styleAdmin.css">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="public/js/shop.js" defer></script>
    <script>
        const tg = window.Telegram.WebApp;
        tg.expand();
    </script>
</head>
<body>

    <h1>Админ-панель</h1>
    <details class="card" <?= isset($_GET['settings_saved']) ? 'open' : '' ?>>
        <summary>Настройки менеджера</summary>
        <div class="settings-form">
            <?php if(isset($_GET['settings_saved'])): ?>
                <p class="success-msg">Настройки успешно сохранены!</p>
            <?php endif; ?>
            
            <form method="POST">
                <label>Username менеджера:</label>
                <input type="text" name="manager_username" value="<?= $manager_username ?>" required>
                
                <label>Текст при оформлении заказа:</label>
                <textarea name="checkout_message" rows="3"><?= $checkout_message ?></textarea>
                
                <button type="submit" name="save_settings" class="btn btn-full btn-accent">
                    Сохранить настройки
                </button>
            </form>
        </div>
    </details>

    <details class="card" open>
        <summary>Управление категориями</summary>
        <div class="category-manager">
            <form method="POST" class="category-manager-form">
             <div class="input-group">
                <input type="text" name="new_cat_name" id="cat_input" placeholder="Название категории" required oninput="handleCatInput(this)">
                <div id="cat_error" class="error-hint" style="display: none;">Максимум 25 символов</div>
            </div>
                <button type="submit" name="add_category" id="cat_submit" class="btn">Создать</button>
            </form>
            
            <div class="cat-tags-container">
                <?php foreach ($categories as $cat): ?>
                    <div class="cat-tag">
                        <?= htmlspecialchars($cat['name']) ?>
                        <form method="POST" onsubmit="return confirm('Удалить категорию?');">
                            <input type="hidden" name="delete_cat_id" value="<?= $cat['id'] ?>">
                            <button type="submit" class="cat-delete-btn">&times;</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </details>

    <div class="card">
        <h3>Добавить товар</h3>
        <form id="productForm" action="admin5577.php" method="POST" enctype="multipart/form-data" onsubmit="handleFormSubmit(this)">
           <div class="input-group">
    <input type="text" name="title" id="product_title" placeholder="Название товара" required>
    <div id="product_error" class="error-hint" style="display: none;">Максимум 60 символов</div>
</div>
            
            <select name="category_id">
                <option value="">-- Выберите категорию --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="price-grid">
                <input type="number" name="price" id="main_price" placeholder="Цена Руб" required oninput="syncPrices('main')">
                <input type="number" name="old_price" id="old_price" placeholder="Старая Руб" disabled oninput="syncPrices('old')">
                <input type="number" id="discount_percent" placeholder="Скидка %" disabled oninput="syncPrices('percent')">
            </div>

            <textarea name="desc" placeholder="Описание товара" rows="7"></textarea>
            
            <label class="input-hint-label">Фото товара:</label>
            <input type="file" name="images[]" multiple>
            
            <button type="submit" id="submitBtn" class="btn btn-full">Опубликовать</button>
        </form>
    </div>

    <hr class="admin-hr">

    <h3>Список товаров</h3>
    <form method="GET" class="search-container">
        <input type="text" name="search" placeholder="Поиск по названию..." value="<?= $search ?>">
        <button type="submit" class="btn">Найти</button>
        <?php if(!empty($search)): ?>
            <a href="admin5577.php" class="btn btn-reset">Сброс</a>
        <?php endif; ?>
    </form>

    <div class="search-stats">
        Найдено: <strong><?= $totalItems ?></strong> | Страница <?= $currentPage ?> из <?= $totalPages ?: 1 ?>
    </div>

    <div class="items-list card">
        <?php if(empty($products)): ?>
            <div class="empty-state">Товары не найдены</div>
        <?php endif; ?>
        
        <?php foreach ($products as $item): 
            $imgData = $item['images']; 
            $firstImgPath = '';
            if (!empty($imgData) && is_array($imgData)) {
                $checkPath = 'public/' . ltrim($imgData[0], '/');
                if (file_exists($checkPath)) $firstImgPath = $checkPath;
            }
        ?>
            <div class="item">
                <div class="item-main-info">
                    <div class="admin-product-img-container">
                        <?php if ($firstImgPath): ?>
                            <img src="<?= $firstImgPath ?>" alt="" class="admin-product-img">
                        <?php endif; ?>
                    </div>
                    <div class="item-details">
                        <span class="item-title"><?= htmlspecialchars($item['title']) ?></span><br>
                        <span class="item-meta">Категория: <?= htmlspecialchars($item['category_name'] ?? 'Общее') ?></span>
                        <div class="price-tag">
                            <?= number_format($item['price'], 0, '', ' ') ?> Руб
                            <?php if (!empty($item['old_price'])): ?>
                                <span class="old-price-tag"><?= number_format($item['old_price'], 0, '', ' ') ?> Руб</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="actions">
                    <a href="edit-product.php?id=<?= $item['id'] ?>" class="btn btn-warning">Изменить</a>
                    <button type="button" class="btn btn-danger" onclick="deleteProductAjax(<?= $item['id'] ?>, this)">Удалить</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): 
                $params = ['page' => $i];
                if (!empty($search)) $params['search'] = $search;
                $link = "?" . http_build_query($params);
            ?>
                <a href="<?= $link ?>" class="<?= ($i == $currentPage) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <div class="admin-action-bar">
        <a href="index.php" class="back-btn-admin">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:20px; height:20px;">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
        </a>
    </div>

    <script>
    function handleCatInput(input) {
        const error = document.getElementById('cat_error');
        const submit = document.getElementById('cat_submit');
        if (input.value.length > 25) {
            error.style.display = 'block';
            submit.disabled = true;
            submit.style.opacity = '0.6';
        } else {
            error.style.display = 'none';
            submit.disabled = false;
            submit.style.opacity = '1';
        }
    }

    function handleFormSubmit(form) {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('btn-loading');
        btn.innerText = 'Публикация...';
    }

    function syncPrices(type) {
        const mainPriceInput = document.getElementById('main_price');
        const oldPriceInput = document.getElementById('old_price');
        const percentInput = document.getElementById('discount_percent');

        let mainP = parseFloat(mainPriceInput.value) || 0;
        let oldP = parseFloat(oldPriceInput.value) || 0;
        let perc = parseFloat(percentInput.value) || 0;

        if (mainP > 0) {
            oldPriceInput.disabled = false;
            percentInput.disabled = false;
        } else {
            oldPriceInput.disabled = true;
            percentInput.disabled = true;
            return;
        }

        if (type === 'main' || type === 'old') {
            if (oldP > mainP) {
                percentInput.value = Math.round(((oldP - mainP) / oldP) * 100);
            } else {
                percentInput.value = '';
            }
        } else if (type === 'percent') {
            if (perc > 0 && perc < 100) {
                oldPriceInput.value = Math.round(mainP / (1 - perc / 100));
            }
        }
    }

    async function deleteProductAjax(id, button) {
        if (!confirm('Вы уверены, что хотите удалить этот товар?')) return;
        const itemElement = button.closest('.item');
        button.disabled = true;
        button.innerText = '...';

        try {
            const response = await fetch('delete-product.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&ajax=1`
            });
            const result = await response.json();
            if (result.success) {
                itemElement.style.opacity = '0';
                setTimeout(() => itemElement.remove(), 300);
            } else {
                alert('Ошибка: ' + result.error);
                button.disabled = false;
            }
        } catch (e) {
            alert('Ошибка сети');
            button.disabled = false;
        }
    }
    </script>
</body>
</html>
