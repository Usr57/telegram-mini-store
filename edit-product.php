<?php
session_start();
require_once 'inc/db.php';
include 'inc/styleHelper.php';
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php'); exit;
}

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: admin5577.php'); exit; }

$product = getProductById($id);
$categories = getCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $category_id = $_POST['category_id'] !== "" ? (int)$_POST['category_id'] : null;
    $price = (int)$_POST['price'];
    $old_price = !empty($_POST['old_price']) ? (int)$_POST['old_price'] : null;
    $desc = htmlspecialchars($_POST['desc']);
    $out_of_stock = isset($_POST['out_of_stock']) ? 1 : 0;


    $images = [];
    if (!empty($_POST['existing_images'])) {
        foreach ($_POST['existing_images'] as $img) {
            $images[] = $img; 
        }
    }


    if (!empty($_FILES['new_images']['name'][0])) {
        $uploadDir = 'public/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        foreach ($_FILES['new_images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['new_images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = time() . '_' . $key . '_' . $_FILES['new_images']['name'][$key];
                
                if (move_uploaded_file($tmpName, $uploadDir . $fileName)) {
                    $images[] = '/uploads/' . $fileName;
                }
            }
        }
    }

    updateProduct($id, $title, $category_id, $price, $old_price, $desc, $images, $out_of_stock);
    header("Location: admin5577.php?success=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"> 
    <title>Редактирование: <?= htmlspecialchars($product['title']) ?></title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="public/js/shop.js" defer></script>
    <style>
        :root {
            --bg-color: var(--tg-theme-bg-color, #f4f7f6);
            --secondary-bg: var(--tg-theme-secondary-bg-color, #ffffff);
            --text-color: var(--tg-theme-text-color, #2d3436);
            --hint-color: var(--tg-theme-hint-color, #8e8e93);
            --button-color: var(--tg-theme-button-color, #007bff);
            --button-text: var(--tg-theme-button-text-color, #ffffff);
            --input-border: rgba(128, 128, 128, 0.2);
            --danger: #ff4757;
        }

        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            max-width: 600px; 
            margin: 0 auto; 
            background: var(--bg-color); 
            color: var(--text-color);
            padding: 15px; 
            padding-bottom: 100px;
        }
        
        .card { 
            background: var(--secondary-bg); 
            padding: 25px; 
            border-radius: 16px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
        }

        h3 { margin-top: 0; color: var(--text-color); }
        label { display: block; margin-bottom: 8px; font-size: 14px; color: var(--hint-color); font-weight: 600; }

        input, textarea, select { 
            width: 100%; 
            margin-bottom: 20px; 
            display: block; 
            padding: 12px; 
            background: var(--bg-color);
            color: var(--text-color);
            border: 1px solid var(--input-border); 
            border-radius: 10px; 
            box-sizing: border-box; 
            font-size: 16px;
            outline: none;
        }

        .edit-photos-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); 
            gap: 10px; 
            margin-bottom: 20px; 
        }
        .photo-item { 
            position: relative; 
            aspect-ratio: 1/1; 
            border-radius: 10px; 
            overflow: hidden; 
            border: 1px solid var(--input-border); 
        }
        .photo-item img { width: 100%; height: 100%; object-fit: cover; }
        .remove-photo {
            position: absolute; top: 4px; right: 4px;
            background: var(--danger); color: white;
            border: none; border-radius: 50%; width: 22px; height: 22px;
            cursor: pointer; font-weight: bold; font-size: 14px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .file-upload-wrapper {
            background: var(--bg-color);
            border: 2px dashed var(--input-border);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }

        .btn { 
            background: var(--button-color); 
            color: var(--button-text); 
            border: none; 
            padding: 16px; 
            border-radius: 12px; 
            cursor: pointer; 
            font-weight: bold; 
            width: 100%; 
            font-size: 16px;
        }

        .admin-action-bar { position: fixed; bottom: 20px; left: 20px; z-index: 9999; }
        .back-btn-admin {
            display: flex; align-items: center; justify-content: center;
            background-color: var(--secondary-bg); color: var(--text-color);
            width: 50px; height: 50px; border-radius: 50%;
            border: 1px solid var(--input-border); box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    
    <div class="card" id="edit-product-card">
        <h3>Редактировать товар</h3>
        <form method="POST" enctype="multipart/form-data" id="edit-product-form">
           <label for="edit-title">Название:</label>
<input type="text" name="title" id="edit-title" value="<?= htmlspecialchars($product['title']) ?>" required>
<div id="title_error" style="color: var(--danger); font-size: 12px; margin-top: -15px; margin-bottom: 15px; display: none;">
    Максимум 60 символов
</div>
            <label for="edit-category">Категория:</label>
            <select name="category_id" id="edit-category">
                <option value="">-- Общее --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
    <div>
        <label for="edit-price">Цена:</label>
        <input type="number" name="price" id="edit-price" value="<?= $product['price'] ?>" required>
    </div>
    <div>
        <label for="edit-old-price">Старая цена:</label>
        <input type="number" name="old_price" id="edit-old-price" value="<?= $product['old_price'] ?>">
    </div>
</div>

<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
    <input type="checkbox" name="out_of_stock" id="out_of_stock" value="1" 
           <?= !empty($product['out_of_stock']) ? 'checked' : '' ?> 
           style="width: 20px; height: 20px; margin: 0; cursor: pointer;">
    <label for="out_of_stock" style="margin: 0; cursor: pointer; color: var(--text-color); font-size: 16px;">
        Нет в наличии
    </label>
</div>

            

            <label for="edit-desc">Описание:</label>
            <textarea name="desc" id="edit-desc" rows="7"><?= htmlspecialchars($product['description']) ?></textarea>

            <label>Текущие фото:</label>
            <div class="edit-photos-grid" id="current-photos-grid">
                <?php 
                $currentImages = is_array($product['images']) ? $product['images'] : json_decode($product['images'] ?? '[]', true);
                $imgIdx = 0;
                foreach ($currentImages as $path): 
                ?>
                    <div class="photo-item" id="photo-item-<?= $imgIdx ?>">
                        <img src="public/<?= ltrim($path, '/') ?>" alt="">
                        <input type="hidden" name="existing_images[]" value="<?= $path ?>">
                        <button type="button" class="remove-photo" id="remove-photo-<?= $imgIdx ?>" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                <?php $imgIdx++; endforeach; ?>
            </div>

            <label for="new-images-input">Добавить новые фото:</label>
            <div class="file-upload-wrapper" id="upload-wrapper">
                <input type="file" name="new_images[]" id="new-images-input" multiple accept="image/*" style="margin-bottom:0; border:none; padding:0; background:none;">
            </div>

            <button type="submit" class="btn" id="submit-edit-btn">Сохранить изменения</button>
        </form>
    </div>

    <div class="admin-action-bar">
        <a href="admin5577.php" class="back-btn-admin" id="back-to-admin-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:20px; height:20px;">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
        </a>
    </div>

    <script>
        const tg = window.Telegram.WebApp;
        tg.ready();
        tg.expand();

document.addEventListener('DOMContentLoaded', function() {
    const setupLimit = (inputId, errorId, limit) => {
        const input = document.getElementById(inputId);
        const error = document.getElementById(errorId);
        // В edit-product.php кнопка называется submit-edit-btn
        const submitBtn = document.getElementById('submit-edit-btn'); 
        
        if (!input || !error) return; 

        const validate = () => {
            if (input.value.length > limit) {
                error.style.display = 'block';
                input.style.borderColor = 'var(--danger)';
                if (submitBtn) submitBtn.disabled = true;
            } else {
                error.style.display = 'none';
                input.style.borderColor = '';
                // Проверяем, нет ли ошибок в других полях перед включением кнопки
                if (submitBtn) submitBtn.disabled = false; 
            }
        };

        input.addEventListener('input', validate);
        validate(); // Проверка при загрузке страницы
    };

    // Применяем к названию (60 симв.)
    setupLimit('edit-title', 'title_error', 60);
    
    // Применяем к новому элементу — Описанию (например, 1000 симв.)
    setupLimit('edit-desc', 'desc_error', 1000);
});
</script>
</body>
</html>