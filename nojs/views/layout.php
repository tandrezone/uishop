<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? escape($pageTitle) . ' - ' : '' ?>UIShop</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* NoJS specific styles */
        .nojs-form-errors {
            background-color: rgba(255, 51, 102, 0.1);
            border: 1px solid var(--danger);
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1rem;
            color: var(--danger);
        }
        
        .nojs-form-success {
            background-color: rgba(0, 255, 204, 0.1);
            border: 1px solid var(--success);
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1rem;
            color: var(--success);
        }
        
        .nojs-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .nojs-table th,
        .nojs-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-glass);
        }
        
        .nojs-table th {
            color: var(--text-gold);
            font-weight: 600;
        }
        
        .nojs-table tr:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }
        
        .nojs-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .nojs-actions form {
            display: inline;
        }
        
        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .nojs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .nojs-product-card {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: var(--radius);
            padding: 1.5rem;
            transition: all var(--transition-fast);
            backdrop-filter: blur(10px);
        }
        
        .nojs-product-card:hover {
            border-color: var(--border-glow);
            box-shadow: var(--glow-soft);
            transform: translateY(-4px);
        }
        
        .nojs-product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--bg-secondary), var(--bg-primary));
        }
        
        .nojs-product-name {
            color: var(--text-primary);
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .nojs-product-description {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .nojs-product-price {
            color: var(--accent-gold);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .nojs-product-stock {
            color: var(--text-tertiary);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        
        .nojs-cart-summary {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-top: 2rem;
            backdrop-filter: blur(10px);
        }
        
        .nojs-empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-secondary);
        }
        
        .nojs-empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .quantity-input {
            width: 80px;
            padding: 0.5rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border-glass);
            border-radius: 8px;
            color: var(--text-primary);
            text-align: center;
        }
        
        .nojs-order-card {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
        }
        
        .nojs-order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-glass);
        }
        
        .nojs-status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: rgba(255, 215, 0, 0.2);
            color: var(--accent-gold);
        }
        
        .status-processing {
            background: rgba(0, 255, 255, 0.2);
            color: var(--accent-cyan);
        }
        
        .status-completed {
            background: rgba(0, 255, 204, 0.2);
            color: var(--success);
        }
        
        .status-cancelled {
            background: rgba(255, 51, 102, 0.2);
            color: var(--danger);
        }
    </style>
</head>
<body>
    <?php
    $flashMessage = getFlashMessage();
    if ($flashMessage):
    ?>
    <div class="nojs-form-<?= $flashMessage['type'] === 'error' ? 'errors' : 'success' ?>" style="position: fixed; top: 20px; right: 20px; z-index: 10000; max-width: 400px;">
        <?= escape($flashMessage['message']) ?>
    </div>
    <?php endif; ?>
    
    <?php echo $content ?? ''; ?>
</body>
</html>
