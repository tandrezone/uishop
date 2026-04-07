<?php
declare(strict_types=1);

/**
 * Authentication pages controller
 */

$pageTitle = $page === 'login' ? 'Login' : 'Register';

ob_start();
?>

<div id="login-page" class="login-page">
    <div class="login-background-decoration">
        <div class="floating-orb orb-1"></div>
        <div class="floating-orb orb-2"></div>
        <div class="floating-orb orb-3"></div>
    </div>
    <div class="login-card">
        <div class="login-inner">
            <div class="login-header">
                <div class="login-logo-wrapper">
                    <div class="login-logo">
                        <img src="assets/logo.png" alt="UIShop Logo" width="100">
                    </div>
                </div>
                <h1>UIShop</h1>
                <p class="login-subtitle"><?= $page === 'login' ? 'Sign in to your account' : 'Create a new account' ?></p>
            </div>

            <div class="login-body">
                <?php if ($page === 'login'): ?>
                <div id="login-mode">
                    <form method="POST" action="index.php?page=auth&action=login">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <div class="input-wrapper">
                                <input type="text" id="username" name="username" placeholder="Enter username" required autocomplete="username">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <input type="password" id="password" name="password" placeholder="Enter password" required autocomplete="current-password">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full btn-animated">
                            <span class="btn-text">Sign In</span>
                            <span class="btn-shimmer"></span>
                        </button>
                    </form>
                    
                    <p class="auth-toggle">
                        Don't have an account?
                        <a href="index.php?page=register" class="auth-link">Register here</a>
                    </p>
                </div>
                <?php else: ?>
                <div id="register-mode">
                    <form method="POST" action="index.php?page=auth&action=register">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <div class="input-wrapper">
                                <input type="text" id="username" name="username" placeholder="Choose a username" required autocomplete="username">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <input type="password" id="password" name="password" placeholder="Create a password" required autocomplete="new-password">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm-password">Confirm Password</label>
                            <div class="input-wrapper">
                                <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm your password" required autocomplete="new-password">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email (optional)</label>
                            <div class="input-wrapper">
                                <input type="email" id="email" name="email" placeholder="your@email.com" autocomplete="email">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full btn-animated">
                            <span class="btn-text">Create Account</span>
                            <span class="btn-shimmer"></span>
                        </button>
                    </form>
                    
                    <p class="auth-toggle">
                        Already have an account?
                        <a href="index.php?page=login" class="auth-link">Sign in here</a>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once VIEWS_PATH . '/layout.php';
