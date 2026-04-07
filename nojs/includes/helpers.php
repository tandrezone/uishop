<?php
declare(strict_types=1);

/**
 * Helper functions for NoJS UIShop
 */

/**
 * Escape HTML output
 */
function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Format price
 */
function formatPrice(float $price): string
{
    return '$' . number_format($price, 2);
}

/**
 * Format date
 */
function formatDate(string $date): string
{
    return date('M d, Y H:i', strtotime($date));
}

/**
 * Get flash message and clear it
 */
function getFlashMessage(): ?array
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Set flash message
 */
function setFlashMessage(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Check if user is admin
 */
function isAdmin(): bool
{
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

/**
 * Render view
 */
function renderView(string $viewName, array $data = []): void
{
    extract($data);
    require_once VIEWS_PATH . '/' . $viewName . '.php';
}

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
