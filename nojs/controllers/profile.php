<?php
declare(strict_types=1);

/**
 * Profile page controller
 */

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request');
        header('Location: index.php?page=profile');
        exit;
    }
    
    $data = [];
    
    if (!empty($_POST['name'])) {
        $data['name'] = $_POST['name'];
    }
    
    if (!empty($_POST['email'])) {
        $data['email'] = $_POST['email'];
    }
    
    if (!empty($_POST['avatar'])) {
        $data['avatar'] = $_POST['avatar'];
    }
    
    // Handle password change
    if (!empty($_POST['new_password'])) {
        if (empty($_POST['current_password'])) {
            setFlashMessage('error', 'Current password is required to change password');
            header('Location: index.php?page=profile');
            exit;
        }
        
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            setFlashMessage('error', 'New passwords do not match');
            header('Location: index.php?page=profile');
            exit;
        }
        
        $data['currentPassword'] = $_POST['current_password'];
        $data['newPassword'] = $_POST['new_password'];
    }
    
    $result = apiUpdateProfile($data);
    
    if ($result['success']) {
        // Update session user data
        $_SESSION['user'] = $result['data'];
        setFlashMessage('success', 'Profile updated successfully');
    } else {
        setFlashMessage('error', $result['error'] ?? 'Failed to update profile');
    }
    
    header('Location: index.php?page=profile');
    exit;
}

// Get profile
$result = apiGetProfile();

$profile = [];
if ($result['success']) {
    $profile = $result['data'];
}

$pageTitle = 'Profile';

ob_start();
?>

<div id="dashboard" class="dashboard">
    <div class="dashboard-layout">
        <?php require_once VIEWS_PATH . '/partials/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="main-inner">
                <div id="content-area">
                    <h2 style="color: var(--text-gold); font-size: 2rem; margin-bottom: 2rem;">My Profile</h2>
                    
                    <div class="nojs-cart-summary">
                        <h3 style="color: var(--text-gold); margin-bottom: 1rem;">Profile Information</h3>
                        <form method="POST" action="index.php?page=profile">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" value="<?= escape($profile['username'] ?? '') ?>" disabled
                                       style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-tertiary); opacity: 0.6;">
                                <small style="color: var(--text-tertiary); font-size: 0.875rem;">Username cannot be changed</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="name" name="name" value="<?= escape($profile['name'] ?? '') ?>" placeholder="Your full name"
                                       style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?= escape($profile['email'] ?? '') ?>" placeholder="your@email.com"
                                       style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);">
                            </div>
                            
                            <div class="form-group">
                                <label for="avatar">Avatar URL</label>
                                <input type="text" id="avatar" name="avatar" value="<?= escape($profile['avatar'] ?? '') ?>" placeholder="https://example.com/avatar.jpg"
                                       style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);">
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Role</label>
                                <input type="text" id="role" value="<?= escape($profile['role'] ?? '') ?>" disabled
                                       style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-tertiary); opacity: 0.6;">
                            </div>
                            
                            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
                                <h4 style="color: var(--text-gold); margin-bottom: 1rem;">Change Password</h4>
                                
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                                           style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);">
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" autocomplete="new-password"
                                           style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);">
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password"
                                           style="width: 100%; padding: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-glass); border-radius: 8px; color: var(--text-primary);">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-full btn-animated" style="margin-top: 2rem;">
                                <span class="btn-text">Update Profile</span>
                                <span class="btn-shimmer"></span>
                            </button>
                        </form>
                    </div>
                    
                    <div class="nojs-cart-summary" style="margin-top: 2rem;">
                        <h3 style="color: var(--text-gold); margin-bottom: 1rem;">Account Information</h3>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                            <div>
                                <div style="color: var(--text-tertiary); font-size: 0.875rem;">Account Created</div>
                                <div style="color: var(--text-primary); font-weight: 600; margin-top: 0.25rem;">
                                    <?= isset($profile['createdAt']) ? formatDate($profile['createdAt']) : 'N/A' ?>
                                </div>
                            </div>
                            <div>
                                <div style="color: var(--text-tertiary); font-size: 0.875rem;">Last Updated</div>
                                <div style="color: var(--text-primary); font-weight: 600; margin-top: 0.25rem;">
                                    <?= isset($profile['updatedAt']) ? formatDate($profile['updatedAt']) : 'N/A' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once VIEWS_PATH . '/layout.php';
