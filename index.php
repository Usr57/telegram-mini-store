<?php

session_start();
require_once 'inc/db.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$stock_only = isset($_GET['stock_only']) && $_GET['stock_only'] == '1';
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

$itemsPerPage = 26;
$offset = ($currentPage - 1) * $itemsPerPage;

$products = getProducts($itemsPerPage, $offset, $search, $category, $sort, $stock_only); 
$totalItems = getTotalProductsCount($search, $category, $stock_only);
$totalPages = ceil($totalItems / $itemsPerPage);

include 'header.php'; 
?>


<div id="no-results" style="display:none; text-align:center; margin-top:20px;">Товары не найдены</div>

<div class="shop-container" id="main-shop-container">
    <?php include 'product_card.php'; ?>
</div>

<?php if ($totalPages > 1): ?>
    <div class="pagination" id="pagination-nav">
        <?php
        $range = 2; 
        $show_dots = false;
        for ($i = 1; $i <= $totalPages; $i++):
            if ($i == 1 || $i == $totalPages || ($i >= $currentPage - $range && $i <= $currentPage + $range)):
               $link = "index.php?page=$i" 
      . ($search ? "&search=$search" : "") 
      . ($category != 'all' ? "&category=$category" : "")
      . ($sort != 'newest' ? "&sort=$sort" : "")
      . ($stock_only ? "&stock_only=1" : "");
                ?>
                <a href="<?= $link ?>" 
                   id="page-link-<?= $i ?>" 
                   class="<?= ($i == $currentPage) ? 'active' : '' ?>">
                   <?= $i ?>
                </a>
                <?php $show_dots = true;
            elseif ($show_dots): ?>
                <span class="pagination-dots" id="dots-after-<?= $i ?>" style="color: var(--tg-hint); padding: 8px 4px;">...</span>
                <?php $show_dots = false;
            endif;
        endfor; ?>
    </div>
<?php endif; ?>

<form action="order.php" method="POST" id="orderForm">
    <input type="hidden" name="user_id" id="user_id_input">
</form>


<script src="public/js/shop.js?v=<?= time(); ?>"></script>

<footer class="mini-admin-footer" id="main-footer">
    <a href="admin5577.php" class="admin-link" id="admin-panel-link">управление</a>
</footer>

<style>
.pagination { display: flex; justify-content: center; gap: 8px; margin: 30px 0; flex-wrap: wrap; }
.pagination a { padding: 10px 16px; border: 1px solid var(--tg-hint); border-radius: 8px; text-decoration: none; color: var(--tg-text); background: var(--tg-sec-bg); transition: 0.2s; font-weight: 500; }
.pagination a.active { background: var(--tg-button-color, #248bcf); color: var(--tg-button-text-color, #fff); border-color: var(--tg-button-color, #248bcf); }
.mini-admin-footer { padding: 20px 0 40px 0; text-align: center; background: transparent; }
.admin-link { font-size: 11px; color: var(--tg-hint-color, #dfe6e9); text-decoration: none; text-transform: lowercase; letter-spacing: 1px; transition: opacity 0.3s ease; opacity: 0.5; }
.admin-link:hover { opacity: 1; }
@media (max-width: 600px) { .mini-admin-footer { padding-bottom: 120px; } }
</style>