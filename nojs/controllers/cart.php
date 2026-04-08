<?php
declare(strict_types=1);

/**
 * Cart page controller
 */

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request');
        header('Location: ' . NOJS_BASE . '/index.php?page=cart');
        exit;
    }
    
    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        
        $result = apiUpdateCartItem($id, $quantity);
        
        if ($result['success']) {
            setFlashMessage('success', 'Cart updated');
        } else {
            setFlashMessage('error', $result['error'] ?? 'Failed to update cart');
        }
        header('Location: ' . NOJS_BASE . '/index.php?page=cart');
        exit;
    } elseif ($action === 'remove') {
        $id = intval($_POST['id'] ?? 0);
        
        $result = apiRemoveFromCart($id);
        
        if ($result['success']) {
            setFlashMessage('success', 'Item removed from cart');
        } else {
            setFlashMessage('error', $result['error'] ?? 'Failed to remove item');
        }
        header('Location: ' . NOJS_BASE . '/index.php?page=cart');
        exit;
    } elseif ($action === 'clear') {
        $result = apiClearCart();
        
        if ($result['success']) {
            setFlashMessage('success', 'Cart cleared');
        } else {
            setFlashMessage('error', $result['error'] ?? 'Failed to clear cart');
        }
        header('Location: ' . NOJS_BASE . '/index.php?page=cart');
        exit;
    } elseif ($action === 'checkout') {
        $shippingAddress = $_POST['shipping_address'] ?? null;
        $notes = $_POST['notes'] ?? null;
        
        $result = apiCheckout($shippingAddress, $notes);
        
        if ($result['success']) {
            setFlashMessage('success', 'Order placed successfully!');
            header('Location: ' . NOJS_BASE . '/index.php?page=orders');
            exit;
        } else {
            setFlashMessage('error', $result['error'] ?? 'Failed to checkout');
            header('Location: ' . NOJS_BASE . '/index.php?page=cart');
            exit;
        }
    }
}

// Get cart
$result = apiGetCart();

$cartItems = [];
$totalAmount = 0;
$totalItems = 0;

if ($result['success'] && isset($result['data']['items'])) {
    $cartItems = $result['data']['items'];
    $totalAmount = $result['data']['totalAmount'] ?? 0;
    $totalItems = $result['data']['totalItems'] ?? 0;
}

$pageTitle = 'Shopping Cart';

ob_start();
?>

<div id="dashboard" class="dashboard">
    <div class="dashboard-layout">
        <?php require_once VIEWS_PATH . '/partials/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="main-inner">
                <div id="content-area">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <h2 style="color: var(--text-gold); font-size: 2rem;">Shopping Cart</h2>
                        
                        <?php if (!empty($cartItems)): ?>
                        <form method="POST" action="<?= NOJS_BASE ?>/index.php?page=cart&action=clear" style="margin: 0;">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <button type="submit" class="btn btn-danger btn-small">
                                Clear Cart
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($cartItems)): ?>
                    <div class="nojs-empty-state">
                        <div class="nojs-empty-state-icon">🛒</div>
                        <p>Your cart is empty</p>
                        <a href="<?= NOJS_BASE ?>/index.php?page=products" class="btn btn-primary" style="margin-top: 1rem; text-decoration: none;">Browse Products</a>
                    </div>
                    <?php else: ?>
                    
                    <div class="nojs-cart-summary" style="margin-bottom: 2rem;">
                        <table class="nojs-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-primary);">
                                            <?= escape($item['product']['name'] ?? 'Unknown Product') ?>
                                        </div>
                                        <?php if (!empty($item['product']['description'])): ?>
                                        <div style="font-size: 0.875rem; color: var(--text-tertiary); margin-top: 0.25rem;">
                                            <?= escape(substr($item['product']['description'], 0, 100)) ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color: var(--accent-gold);">
                                        <?= formatPrice($item['product']['price'] ?? 0) ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="<?= NOJS_BASE ?>/index.php?page=cart&action=update" style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                            <input type="hidden" name="id" value="<?= escape($item['id']) ?>">
                                            <input type="number" name="quantity" value="<?= escape($item['quantity']) ?>" 
                                                   min="1" max="<?= escape($item['product']['stock'] ?? 99) ?>" 
                                                   class="quantity-input">
                                            <button type="submit" class="btn btn-primary btn-small">Update</button>
                                        </form>
                                    </td>
                                    <td style="color: var(--text-gold); font-weight: 600;">
                                        <?= formatPrice(($item['product']['price'] ?? 0) * $item['quantity']) ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="<?= NOJS_BASE ?>/index.php?page=cart&action=remove" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                            <input type="hidden" name="id" value="<?= escape($item['id']) ?>">
                                            <button type="submit" class="btn btn-danger btn-small">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-glass); text-align: right;">
                            <div style="font-size: 1.25rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                Total Items: <span style="color: var(--text-primary);"><?= $totalItems ?></span>
                            </div>
                            <div style="font-size: 1.5rem; color: var(--text-gold); font-weight: 700;">
                                Total: <?= formatPrice($totalAmount) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Checkout Form -->
                    <div class="nojs-cart-summary">
                        <h3 style="color: var(--text-gold); margin-bottom: 1rem;">Checkout</h3>
                        <form method="POST" action="<?= NOJS_BASE ?>/index.php?page=cart&action=checkout">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            
                            <div class="form-group">
                                <label for="shipping_address">Shipping Address (optional)</label>
                                <textarea id="shipping_address" name="shipping_address" rows="3" 
                                          placeholder="Enter your shipping address"
                                          style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="notes">Order Notes (optional)</label>
                                <textarea id="notes" name="notes" rows="2" 
                                          placeholder="Any special instructions?"
                                          style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-full btn-animated">
                                <span class="btn-text">Place Order (<?= formatPrice($totalAmount) ?>)</span>
                                <span class="btn-shimmer"></span>
                            </button>
                        </form>
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
