<?php
declare(strict_types=1);

/**
 * Products page controller
 */

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request');
        header('Location: index.php?page=products');
        exit;
    }
    
    if ($action === 'create' && isAdmin()) {
        $result = apiCreateProduct([
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => floatval($_POST['price'] ?? 0),
            'stock' => intval($_POST['stock'] ?? 0),
            'category' => $_POST['category'] ?? '',
            'image' => $_POST['image'] ?? ''
        ]);
        
        if ($result['success']) {
            setFlashMessage('success', 'Product created successfully');
        } else {
            setFlashMessage('error', $result['error'] ?? 'Failed to create product');
        }
        header('Location: index.php?page=products');
        exit;
    } elseif ($action === 'update' && isAdmin()) {
        $id = $_POST['id'] ?? '';
        $result = apiUpdateProduct($id, [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => floatval($_POST['price'] ?? 0),
            'stock' => intval($_POST['stock'] ?? 0),
            'category' => $_POST['category'] ?? '',
            'image' => $_POST['image'] ?? ''
        ]);
        
        if ($result['success']) {
            setFlashMessage('success', 'Product updated successfully');
        } else {
            setFlashMessage('error', $result['error'] ?? 'Failed to update product');
        }
        header('Location: index.php?page=products');
        exit;
    } elseif ($action === 'delete' && isAdmin()) {
        $id = $_POST['id'] ?? '';
        $result = apiDeleteProduct($id);
        
        if ($result['success']) {
            setFlashMessage('success', 'Product deleted successfully');
        } else {
            setFlashMessage('error', $result['error'] ?? 'Failed to delete product');
        }
        header('Location: index.php?page=products');
        exit;
    } elseif ($action === 'add_to_cart') {
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        
        $result = apiAddToCart($productId, $quantity);
        
        if ($result['success']) {
            setFlashMessage('success', 'Added to cart');
        } else {
            setFlashMessage('error', $result['error'] ?? 'Failed to add to cart');
        }
        header('Location: index.php?page=products');
        exit;
    }
}

// Get products
$search = $_GET['search'] ?? null;
$result = apiGetProducts($search);

$products = [];
if ($result['success'] && isset($result['data']['products'])) {
    $products = $result['data']['products'];
}

$pageTitle = 'Products';

ob_start();
?>

<div id="dashboard" class="dashboard">
    <div class="dashboard-layout">
        <?php require_once VIEWS_PATH . '/partials/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="main-inner">
                <div id="content-area">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <h2 style="color: var(--text-gold); font-size: 2rem;">Products</h2>
                        
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <form method="GET" action="index.php" style="margin: 0;">
                                <input type="hidden" name="page" value="products">
                                <input type="text" name="search" placeholder="Search products..." value="<?= escape($search ?? '') ?>" 
                                       style="padding: 0.75rem 1rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);">
                                <button type="submit" class="btn btn-primary btn-small">Search</button>
                            </form>
                            
                            <?php if (isAdmin()): ?>
                            <a href="index.php?page=products&action=create_form" class="btn btn-primary">+ Add Product</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($action === 'create_form' && isAdmin()): ?>
                    <div class="nojs-cart-summary" style="margin-bottom: 2rem;">
                        <h3 style="color: var(--text-gold); margin-bottom: 1rem;">Create New Product</h3>
                        <form method="POST" action="index.php?page=products&action=create">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            
                            <div class="form-group">
                                <label for="name">Product Name</label>
                                <input type="text" id="name" name="name" required style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="3" style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="price">Price</label>
                                <input type="number" id="price" name="price" step="0.01" min="0" required style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);">
                            </div>
                            
                            <div class="form-group">
                                <label for="stock">Stock</label>
                                <input type="number" id="stock" name="stock" min="0" required style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);">
                            </div>
                            
                            <div class="form-group">
                                <label for="category">Category</label>
                                <input type="text" id="category" name="category" style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);">
                            </div>
                            
                            <div class="form-group">
                                <label for="image">Image URL</label>
                                <input type="text" id="image" name="image" style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);">
                            </div>
                            
                            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                <button type="submit" class="btn btn-primary">Create Product</button>
                                <a href="index.php?page=products" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (empty($products)): ?>
                    <div class="nojs-empty-state">
                        <div class="nojs-empty-state-icon">📦</div>
                        <p>No products found</p>
                    </div>
                    <?php else: ?>
                    <div class="nojs-grid">
                        <?php foreach ($products as $product): ?>
                        <div class="nojs-product-card">
                            <?php if (!empty($product['image'])): ?>
                            <img src="<?= escape($product['image']) ?>" alt="<?= escape($product['name']) ?>" class="nojs-product-image">
                            <?php else: ?>
                            <div class="nojs-product-image"></div>
                            <?php endif; ?>
                            
                            <div class="nojs-product-name"><?= escape($product['name']) ?></div>
                            
                            <?php if (!empty($product['description'])): ?>
                            <div class="nojs-product-description"><?= escape($product['description']) ?></div>
                            <?php endif; ?>
                            
                            <div class="nojs-product-price"><?= formatPrice($product['price']) ?></div>
                            <div class="nojs-product-stock">Stock: <?= escape($product['stock']) ?></div>
                            
                            <?php if (!empty($product['category'])): ?>
                            <div style="color: var(--text-tertiary); font-size: 0.875rem; margin-bottom: 1rem;">
                                Category: <?= escape($product['category']) ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="nojs-actions">
                                <form method="POST" action="index.php?page=products&action=add_to_cart">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="hidden" name="product_id" value="<?= escape($product['id']) ?>">
                                    <input type="number" name="quantity" value="1" min="1" max="<?= escape($product['stock']) ?>" class="quantity-input" style="margin-right: 0.5rem;">
                                    <button type="submit" class="btn btn-primary btn-small" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                        🛒 Add to Cart
                                    </button>
                                </form>
                                
                                <?php if (isAdmin()): ?>
                                <form method="POST" action="index.php?page=products&action=delete" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= escape($product['id']) ?>">
                                    <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once VIEWS_PATH . '/layout.php';
