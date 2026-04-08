<?php
declare(strict_types=1);

/**
 * Authentication handler - Process login/register forms
 */

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    setFlashMessage('error', 'Invalid request');
    header('Location: ' . NOJS_BASE . '/index.php?page=login');
    exit;
}

$action = $_GET['action'] ?? 'login';

if ($action === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        setFlashMessage('error', 'Username and password are required');
        header('Location: ' . NOJS_BASE . '/index.php?page=login');
        exit;
    }
    
    $result = apiLogin($username, $password);
    
    if ($result['success']) {
        $_SESSION['token'] = $result['data']['token'];
        $_SESSION['user'] = $result['data']['user'];
        setFlashMessage('success', 'Login successful!');
        header('Location: ' . NOJS_BASE . '/index.php?page=products');
        exit;
    } else {
        setFlashMessage('error', $result['error'] ?? 'Login failed');
        header('Location: ' . NOJS_BASE . '/index.php?page=login');
        exit;
    }
} elseif ($action === 'register') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $email = $_POST['email'] ?? null;
    
    if (empty($username) || empty($password)) {
        setFlashMessage('error', 'Username and password are required');
        header('Location: ' . NOJS_BASE . '/index.php?page=register');
        exit;
    }
    
    if ($password !== $confirmPassword) {
        setFlashMessage('error', 'Passwords do not match');
        header('Location: ' . NOJS_BASE . '/index.php?page=register');
        exit;
    }
    
    $result = apiRegister($username, $password, $email);
    
    if ($result['success']) {
        $_SESSION['token'] = $result['data']['token'];
        $_SESSION['user'] = $result['data']['user'];
        setFlashMessage('success', 'Registration successful!');
        header('Location: ' . NOJS_BASE . '/index.php?page=products');
        exit;
    } else {
        setFlashMessage('error', $result['error'] ?? 'Registration failed');
        header('Location: ' . NOJS_BASE . '/index.php?page=register');
        exit;
    }
} else {
    header('Location: ' . NOJS_BASE . '/index.php?page=login');
    exit;
}
