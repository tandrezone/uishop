<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo-container">
            <span class="sidebar-logo">🛍</span>
            <div class="logo-glow"></div>
        </div>
        <h2>UIShop</h2>
        <?php if (isset($_SESSION['user'])): ?>
        <span class="role-badge" id="user-role-badge" style="display: inline-block; padding: 0.25rem 0.75rem; background: rgba(255, 0, 255, 0.2); color: var(--accent); border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
            <?= strtoupper(escape($_SESSION['user']['role'] ?? 'user')) ?>
        </span>
        <?php endif; ?>
    </div>

    <nav class="sidebar-nav" aria-label="Main navigation">
        <a href="index.php?page=products" class="nav-link <?= ($page === 'products' ? 'active' : '') ?>">
            <span class="nav-icon" aria-hidden="true">📦</span>
            <span class="nav-text">Products</span>
            <span class="nav-indicator"></span>
        </a>
        <a href="index.php?page=cart" class="nav-link <?= ($page === 'cart' ? 'active' : '') ?>">
            <span class="nav-icon" aria-hidden="true">🛒</span>
            <span class="nav-text">Cart</span>
            <span class="nav-indicator"></span>
        </a>
        <a href="index.php?page=orders" class="nav-link <?= ($page === 'orders' ? 'active' : '') ?>">
            <span class="nav-icon" aria-hidden="true">🗒️</span>
            <span class="nav-text">Orders</span>
            <span class="nav-indicator"></span>
        </a>
        <a href="index.php?page=profile" class="nav-link <?= ($page === 'profile' ? 'active' : '') ?>">
            <span class="nav-icon" aria-hidden="true">👤</span>
            <span class="nav-text">Profile</span>
            <span class="nav-indicator"></span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <?php if (isset($_SESSION['user'])): ?>
        <div class="sidebar-user" id="sidebar-user-info" style="color: var(--text-secondary); margin-bottom: 1rem; padding: 0.75rem; background: rgba(255, 255, 255, 0.05); border-radius: 8px;">
            <div style="font-weight: 600; color: var(--text-primary);"><?= escape($_SESSION['user']['username']) ?></div>
            <?php if (!empty($_SESSION['user']['email'])): ?>
            <div style="font-size: 0.875rem;"><?= escape($_SESSION['user']['email']) ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <a href="index.php?page=logout" class="btn btn-danger btn-full btn-animated" style="text-decoration: none;">
            <span class="btn-text"><span aria-hidden="true">⏻</span> Logout</span>
            <span class="btn-shimmer"></span>
        </a>
    </div>
</aside>

<style>
    .nav-link.active {
        background: rgba(255, 0, 255, 0.1);
        border-left: 3px solid var(--accent);
        color: var(--accent);
    }
    
    .nav-link.active .nav-indicator {
        background: var(--accent);
        box-shadow: 0 0 10px var(--accent);
    }
</style>
