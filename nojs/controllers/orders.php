<?php
declare(strict_types=1);

/**
 * Orders page controller
 */

// Handle order actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAdmin()) {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request');
        header('Location: index.php?page=orders');
        exit;
    }
    
    if ($action === 'update_status') {
        $id = $_POST['id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        $result = apiUpdateOrder($id, ['status' => $status]);
        
        if ($result['success']) {
            setFlashMessage('success', 'Order status updated');
        } else {
            setFlashMessage('error', $result['error'] ?? 'Failed to update order');
        }
        header('Location: index.php?page=orders');
        exit;
    }
}

// Get orders
$statusFilter = $_GET['status'] ?? null;

if (isAdmin()) {
    $result = apiGetOrders($statusFilter);
} else {
    $result = apiGetMyOrders($statusFilter);
}

$orders = [];
if ($result['success'] && isset($result['data']['orders'])) {
    $orders = $result['data']['orders'];
}

$pageTitle = 'Orders';

ob_start();
?>

<div id="dashboard" class="dashboard">
    <div class="dashboard-layout">
        <?php require_once VIEWS_PATH . '/partials/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="main-inner">
                <div id="content-area">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <h2 style="color: var(--text-gold); font-size: 2rem;">
                            <?= isAdmin() ? 'All Orders' : 'My Orders' ?>
                        </h2>
                        
                        <form method="GET" action="index.php" style="margin: 0; display: flex; gap: 0.5rem; align-items: center;">
                            <input type="hidden" name="page" value="orders">
                            <select name="status" 
                                    style="padding: 0.75rem 1rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);">
                                <option value="">All Statuses</option>
                                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="processing" <?= $statusFilter === 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-small">Filter</button>
                        </form>
                    </div>
                    
                    <?php if (empty($orders)): ?>
                    <div class="nojs-empty-state">
                        <div class="nojs-empty-state-icon">📦</div>
                        <p>No orders found</p>
                    </div>
                    <?php else: ?>
                    
                    <?php foreach ($orders as $order): ?>
                    <div class="nojs-order-card">
                        <div class="nojs-order-header">
                            <div>
                                <div style="font-weight: 600; color: var(--text-primary); font-size: 1.125rem;">
                                    Order #<?= escape($order['id']) ?>
                                </div>
                                <div style="color: var(--text-tertiary); font-size: 0.875rem; margin-top: 0.25rem;">
                                    <?= formatDate($order['createdAt']) ?>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span class="nojs-status-badge status-<?= escape($order['status']) ?>">
                                    <?= escape($order['status']) ?>
                                </span>
                                <?php if (isAdmin()): ?>
                                <form method="POST" action="index.php?page=orders&action=update_status" style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= escape($order['id']) ?>">
                                    <select name="status" 
                                            style="padding: 0.5rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary); font-size: 0.875rem;">
                                        <option value="">Update Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="processing">Processing</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-small">Update</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 1rem;">
                            <h4 style="color: var(--text-gold); margin-bottom: 0.5rem;">Products</h4>
                            <table class="nojs-table">
                                <thead>
                                    <tr>
                                        <th>Product ID</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['products'] as $product): ?>
                                    <tr>
                                        <td><?= escape($product['productId']) ?></td>
                                        <td><?= escape($product['quantity']) ?></td>
                                        <td style="color: var(--accent-gold);"><?= formatPrice($product['price']) ?></td>
                                        <td style="color: var(--text-gold);"><?= formatPrice($product['price'] * $product['quantity']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (!empty($order['shippingAddress'])): ?>
                        <div style="margin-bottom: 1rem;">
                            <h4 style="color: var(--text-gold); margin-bottom: 0.5rem;">Shipping Address</h4>
                            <p style="color: var(--text-secondary);"><?= nl2br(escape($order['shippingAddress'])) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($order['notes'])): ?>
                        <div style="margin-bottom: 1rem;">
                            <h4 style="color: var(--text-gold); margin-bottom: 0.5rem;">Notes</h4>
                            <p style="color: var(--text-secondary);"><?= nl2br(escape($order['notes'])) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div style="text-align: right; padding-top: 1rem; border-top: 1px solid var(--border-glass);">
                            <div style="font-size: 1.5rem; color: var(--text-gold); font-weight: 700;">
                                Total: <?= formatPrice($order['totalAmount']) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once VIEWS_PATH . '/layout.php';
