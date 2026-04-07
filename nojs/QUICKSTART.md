# UIShop NoJS - Quick Start Guide

## What is this?

This is a **completely JavaScript-free** version of UIShop. It provides the full shopping experience using only HTML, CSS, and PHP server-side rendering.

## Quick Start

### Prerequisites
- PHP 7.4+ installed
- UIShop backend API running (typically on port 8000)

### Step 1: Start the Backend API

First, make sure the backend API is running:

```bash
cd backend
php -S localhost:8000 -t public
```

### Step 2: Start the NoJS Frontend

In a new terminal:

```bash
cd nojs/public
php -S localhost:3000
```

### Step 3: Access the Application

Open your browser to: **http://localhost:3000**

### Step 4: Test with JavaScript Disabled

To truly experience the no-JS nature:

**Chrome/Edge:**
1. Open DevTools (F12)
2. Press Ctrl+Shift+P (Cmd+Shift+P on Mac)
3. Type "Disable JavaScript"
4. Select the option
5. Reload the page

**Firefox:**
1. Type `about:config` in address bar
2. Search for `javascript.enabled`
3. Toggle to false
4. Reload the page

## What You Can Do

✅ **Register a new account** - Create your user account
✅ **Login** - Sign in with your credentials
✅ **Browse products** - See all available items
✅ **Search products** - Find specific items
✅ **Add to cart** - Build your shopping cart
✅ **Manage cart** - Update quantities or remove items
✅ **Checkout** - Place orders with shipping info
✅ **View orders** - See your order history
✅ **Update profile** - Change your user info
✅ **Admin features** - Manage products and orders (admin only)

## Default Credentials

If you have seeded the database:

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Regular User:**
- Username: `user`
- Password: `user123`

Or register a new account!

## Architecture Overview

```
Browser ←→ NoJS App (PHP) ←→ Backend API ←→ Database
         (HTML/CSS)       (JSON)
```

**How it works:**
1. You submit an HTML form
2. PHP processes the form data
3. PHP calls the backend API
4. API returns JSON response
5. PHP renders new HTML page
6. Browser shows updated page

**No JavaScript involved at any step!**

## Troubleshooting

**Issue: "Failed to connect to API"**
- Solution: Make sure backend is running on port 8000
- Check: `http://localhost:8000/api/products` should return JSON

**Issue: "Session expired" or redirects to login**
- Solution: Your PHP session may have expired
- Check: Cookies are enabled in your browser

**Issue: Images not loading**
- Solution: Check that assets/logo.png exists
- Check: Product images use valid URLs

**Issue: CSS not applying**
- Solution: Make sure assets/css/styles.css exists
- Check: Browser cache (try Ctrl+F5 to hard reload)

## Features by Page

### Login/Register (`?page=login`, `?page=register`)
- Clean login form
- Registration with email (optional)
- CSRF protection
- Session-based authentication

### Products (`?page=products`)
- Product grid display
- Search functionality
- Add to cart with quantity
- Admin: Create/delete products
- Stock validation

### Cart (`?page=cart`)
- View all cart items
- Update quantities
- Remove items
- Clear cart
- Checkout with shipping address
- Order notes

### Orders (`?page=orders`)
- Order history
- Status filtering
- Order details (products, totals)
- Admin: Update order status

### Profile (`?page=profile`)
- View account info
- Update name, email, avatar
- Change password
- Account creation date

## API Configuration

The NoJS app expects the API at `http://localhost:8000/api` by default.

To change this, edit `nojs/public/index.php`:

```php
define('API_BASE_URL', 'http://your-api-url/api');
```

## Production Deployment

For production use:

1. **Use a real web server** (Apache/Nginx)
   - Set document root to `nojs/public`
   - Enable `.htaccess` (Apache) or configure rewrites (Nginx)

2. **Configure HTTPS**
   - Use SSL certificates
   - Update API_BASE_URL to use https://

3. **Session Security**
   - Set `session.cookie_secure = 1` in php.ini
   - Set `session.cookie_httponly = 1`
   - Set `session.cookie_samesite = Strict`

4. **Error Handling**
   - Disable display_errors in production
   - Log errors to files instead

## Browser Compatibility

Works on **ALL** browsers:
- Chrome, Firefox, Safari, Edge (all versions)
- Internet Explorer 11
- Mobile browsers
- Text-only browsers
- Screen readers

**No JavaScript required!**

## Performance

**Advantages:**
- Fast initial load (no JS to parse)
- Works on slow connections
- Low bandwidth usage
- Simple caching strategy

**Trade-offs:**
- Full page reloads on interactions
- No real-time updates
- More server processing

## Enjoy Your JavaScript-Free Shopping Experience! 🛍️

No frameworks. No build tools. No npm. No webpack. No babel.

Just pure HTML, CSS, and PHP.

**That's it!** 🎉
