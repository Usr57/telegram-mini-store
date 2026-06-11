<?php
$config = require 'config.php';

$host    = $config['db_host'];
$db      = $config['db_name'];
$user    = $config['db_user'];
$pass    = $config['db_pass'];
$charset = $config['db_charset'];

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

function getCategories() {
    global $pdo;
    return $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
}

function addCategory($name) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$name]);
}

function deleteCategory($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
}

function getProducts($limit = 20, $offset = 0, $search = '', $category = 'all', $sort = 'newest', $stock_only = false) {
    global $pdo;
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE 1=1";
            
    $params = [];

    if ($stock_only) {
        $sql .= " AND (p.out_of_stock = 0 OR p.out_of_stock IS NULL)";
    }

    if ($category !== 'all') {
        $sql .= " AND c.name = :category";
        $params[':category'] = $category;
    }

    if (!empty($search)) {
        $sql .= " AND p.title LIKE :search";
        $params[':search'] = "%$search%";
    }

    $orderSql = "p.id DESC";
    if ($sort === 'cheap') $orderSql = "p.price ASC";
    elseif ($sort === 'expensive') $orderSql = "p.price DESC";

    $sql .= " ORDER BY $orderSql LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    foreach ($products as &$p) {
        $p['images'] = json_decode($p['images'], true) ?: [];
    }
    return $products;
}

function getTotalProductsCount($search = '', $category = 'all', $stock_only = false) {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
    $params = [];

    if ($stock_only) {
        $sql .= " AND (p.out_of_stock = 0 OR p.out_of_stock IS NULL)";
    }

    if ($category !== 'all') {
        $sql .= " AND c.name = :category";
        $params[':category'] = $category;
    }
    if (!empty($search)) {
        $sql .= " AND p.title LIKE :search";
        $params[':search'] = "%$search%";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if ($product) {
        $product['images'] = json_decode($product['images'], true) ?: [];
    }
    return $product;
}

function addProduct($title, $category_id, $price, $old_price, $desc, $images) {
    global $pdo;
    $cat_id = !empty($category_id) ? $category_id : null;
    $old_p = !empty($old_price) ? $old_price : null;

    $sql = "INSERT INTO products (title, category_id, price, old_price, description, images) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $cat_id, $price, $old_p, $desc, json_encode($images)]);
}



function deleteProduct($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT images FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product) {
        $images = json_decode($product['images'], true) ?: [];
        
        foreach ($images as $imagePath) {
            $fullPath = __DIR__ . '/public' . $imagePath;
            
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    return $stmt->execute([$id]);
}

function updateProduct($id, $title, $category_id, $price, $old_price, $desc, $images, $out_of_stock) {
    global $pdo;
    $cat_id = !empty($category_id) ? (int)$category_id : null;
    $old_p = !empty($old_price) ? (int)$old_price : null;
    $stock_status = !empty($out_of_stock) ? 1 : 0;
    $imagesJson = json_encode($images, JSON_UNESCAPED_UNICODE);

    $sql = "UPDATE products SET 
            title = ?, 
            category_id = ?, 
            price = ?, 
            old_price = ?, 
            description = ?, 
            images = ?,
            out_of_stock = ? 
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    

    return $stmt->execute([
        $title, 
        $cat_id, 
        (int)$price, 
        $old_p, 
        $desc, 
        $imagesJson,
        $stock_status,
        (int)$id
    ]);
}

function getUploadedFiles() {
    $dir = 'public/uploads/';
    if (!is_dir($dir)) return [];
    $allFiles = scandir($dir);
    
    $cleanFiles = [];
    foreach ($allFiles as $file) {
        if ($file === '.' || $file === '..' || strpos($file, '.') === 0) continue;
        if (is_file($dir . $file)) {
            $cleanFiles[] = $file;
        }
    }
    return array_values($cleanFiles);
}
function getSetting($key) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = ?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn();
}

function updateSetting($key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
    return $stmt->execute([$key, $value]);
}
?>