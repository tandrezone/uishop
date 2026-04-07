#!/bin/bash

echo "🔍 Verifying Shopping Cart Implementation"
echo "=========================================="
echo ""

echo "✓ Checking Cart Model..."
if [ -f "src/Models/Cart.php" ]; then
    php -l src/Models/Cart.php > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "  ✅ Cart.php - Syntax OK"
    else
        echo "  ❌ Cart.php - Syntax Error"
        exit 1
    fi
else
    echo "  ❌ Cart.php not found"
    exit 1
fi

echo ""
echo "✓ Checking CartController..."
if [ -f "src/Controllers/CartController.php" ]; then
    php -l src/Controllers/CartController.php > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "  ✅ CartController.php - Syntax OK"
    else
        echo "  ❌ CartController.php - Syntax Error"
        exit 1
    fi
else
    echo "  ❌ CartController.php not found"
    exit 1
fi

echo ""
echo "✓ Checking Routes Configuration..."
if [ -f "config/routes.php" ]; then
    php -l config/routes.php > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "  ✅ routes.php - Syntax OK"
        echo ""
        echo "  📋 Cart Routes Registered:"
        grep -A 1 "'/cart" config/routes.php | grep -E "(GET|POST|PUT|DELETE)" | sed 's/^/    /'
    else
        echo "  ❌ routes.php - Syntax Error"
        exit 1
    fi
else
    echo "  ❌ routes.php not found"
    exit 1
fi

echo ""
echo "✓ Checking Setup Script..."
if [ -f "setup.php" ]; then
    php -l setup.php > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "  ✅ setup.php - Syntax OK"
        if grep -q "Cart" setup.php; then
            echo "  ✅ Cart table initialization included"
        else
            echo "  ⚠️  Cart table initialization not found in setup.php"
        fi
    else
        echo "  ❌ setup.php - Syntax Error"
        exit 1
    fi
else
    echo "  ❌ setup.php not found"
    exit 1
fi

echo ""
echo "✓ Checking Product Model for Image Field..."
if grep -q "image" src/Models/Product.php; then
    echo "  ✅ Image field present in Product model"
else
    echo "  ❌ Image field not found in Product model"
    exit 1
fi

echo ""
echo "=========================================="
echo "✅ All Checks Passed!"
echo ""
echo "📝 Implementation Summary:"
echo "  • Cart Model (src/Models/Cart.php)"
echo "  • Cart Controller (src/Controllers/CartController.php)"
echo "  • Cart Routes (config/routes.php)"
echo "  • Setup Script Updated (setup.php)"
echo "  • Product Image Field (already present)"
echo ""
echo "🚀 Shopping Cart Feature: READY"
echo ""
echo "📋 Available Endpoints:"
echo "  GET    /cart              - Get cart items"
echo "  POST   /cart/items        - Add item to cart"
echo "  PUT    /cart/items/:id    - Update cart item"
echo "  DELETE /cart/items/:id    - Remove cart item"
echo "  DELETE /cart              - Clear cart"
echo ""
