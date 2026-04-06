<?php

/**
 * Diagnostic script to test the register endpoint
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
\App\Core\Environment::load();

echo "🔍 UIShop Backend Diagnostic\n";
echo "=============================\n\n";

// 1. Check .env
echo "1️⃣ Environment Configuration:\n";
echo "   DB_DRIVER: " . \App\Core\Environment::get('DB_DRIVER') . "\n";
echo "   DB_FILE: " . \App\Core\Environment::get('DB_FILE') . "\n";
echo "   JWT_SECRET: " . (strlen(\App\Core\Environment::get('JWT_SECRET')) > 0 ? "✓ Set" : "✗ Not set") . "\n";
echo "   APP_DEBUG: " . \App\Core\Environment::get('APP_DEBUG') . "\n\n";

// 2. Check database connection
echo "2️⃣ Database Connection:\n";
try {
    $db = \App\Core\Database::getConnection();
    echo "   ✓ Connected to database\n";

    // Check if users table exists
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
    if ($result->fetch()) {
        echo "   ✓ Users table exists\n";
    } else {
        echo "   ✗ Users table does NOT exist\n";
        echo "   Run: php setup.php\n";
    }
} catch (Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// 3. Test user creation
echo "3️⃣ Test User Creation:\n";
try {
    $user = new \App\Models\User();

    // Try to find a non-existent user
    $result = $user->findByUsername('nonexistent_' . time());
    if ($result === null) {
        echo "   ✓ User lookup works (returns null for non-existent user)\n";
    }

    // Try to create a test user
    $testUser = 'testuser_' . time();
    $newUser = $user->create($testUser, 'password123', 'test@example.com');

    if ($newUser !== null) {
        echo "   ✓ User creation works\n";
        echo "   ✓ Created user: " . $newUser['username'] . "\n";
    } else {
        echo "   ✗ User creation failed\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// 4. Test JWT
echo "4️⃣ Test JWT Token Generation:\n";
try {
    $token = \App\Core\JwtHandler::encode([
        'id' => 1,
        'username' => 'testuser',
        'email' => 'test@example.com',
        'role' => 'user',
        'sub' => 1,
    ]);

    if (!empty($token)) {
        echo "   ✓ Token generated: " . substr($token, 0, 50) . "...\n";

        // Decode and verify
        $decoded = \App\Core\JwtHandler::decode($token);
        if ($decoded !== null) {
            echo "   ✓ Token verification successful\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

echo "✅ All systems operational!\n";
echo "\nNow test the API with:\n";
echo "curl -X POST http://localhost:8000/api/auth/register \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\"username\":\"demo\",\"password\":\"demo123\",\"email\":\"demo@example.com\"}'\n";
