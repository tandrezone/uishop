#!/usr/bin/env php
<?php

/**
 * Database Setup Script
 * Initialize database tables for UIShop API
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
\App\Core\Environment::load();

echo "🔄 Setting up UIShop API database...\n\n";

try {
    // Initialize tables
    $user = new \App\Models\User();
    $product = new \App\Models\Product();
    $order = new \App\Models\Order();

    $user->createTable();
    $product->createTable();
    $order->createTable();

    echo "✅ Users, products, and orders tables created successfully!\n";
    echo "\n📝 Next steps:\n";
    echo "   1. Update .env with your database credentials\n";
    echo "   2. Change JWT_SECRET to a strong random value\n";
    echo "   3. Run: php -S localhost:8000 -t public/\n";
    echo "   4. Test: curl -X POST http://localhost:8000/auth/register \\\n";
    echo "            -H 'Content-Type: application/json' \\\n";
    echo "            -d '{\"username\":\"test\",\"password\":\"test123\"}'\n\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
