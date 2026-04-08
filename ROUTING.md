# Global Routing Configuration

The UIShop application now uses a unified entry point that routes requests to three different modules based on the URL path.

## Routes

### 1. Frontend (Root `/`)
- **Path**: `/` or any unmatched route
- **Location**: `frontend/`
- **Type**: Single Page Application (SPA) with JavaScript
- **Example**: `http://localhost:8000/`

The frontend is a modern SPA built with vanilla JavaScript. All frontend assets (CSS, JS, images) are automatically served from the `frontend/` directory.

### 2. Backend API (`/api`)
- **Path**: `/api/*`
- **Location**: `backend/public/`
- **Type**: REST API (JSON responses)
- **Example**: `http://localhost:8000/api/products`

All API endpoints are prefixed with `/api`. The backend provides RESTful JSON endpoints for:
- Authentication (`/api/auth/*`)
- Products (`/api/products/*`)
- Cart (`/api/cart/*`)
- Orders (`/api/orders/*`)
- User profile (`/api/user/*`)

### 3. NoJS Version (`/nojs`)
- **Path**: `/nojs/*`
- **Location**: `nojs/public/`
- **Type**: Server-side rendered HTML (no JavaScript)
- **Example**: `http://localhost:8000/nojs`

A fully functional version of the application that works without JavaScript, using server-side rendering and traditional HTML forms.

## How It Works

### Apache/htaccess Configuration
The `.htaccess` file in the root directory:
1. Allows direct access to real files in the `frontend/` directory
2. Routes all other requests to `index.php`

### PHP Router (index.php)
The `index.php` file:
1. Parses the request URI
2. Routes based on the path prefix:
   - `/` → Serves `frontend/index.html`
   - `/api/*` → Includes `backend/public/index.php`
   - `/nojs/*` → Includes `nojs/public/index.php`
   - Frontend assets → Serves files from `frontend/` with appropriate MIME types

## Development Setup

### Using PHP Built-in Server

Run from the project root:

```bash
php -S localhost:8000 router.php
```

The `router.php` file is required for PHP's built-in server because it doesn't support `.htaccess` files. This router script:
- Serves frontend assets (CSS, JS, images) directly from the `frontend/` directory with proper MIME types
- Routes all other requests through `index.php` for processing

Then access:
- Frontend: `http://localhost:8000/`
- API: `http://localhost:8000/api/products`
- NoJS: `http://localhost:8000/nojs`

### Using Apache

1. Configure Apache virtual host to point to the project root directory
2. Ensure `mod_rewrite` is enabled
3. The `.htaccess` file will handle routing automatically

Example Apache configuration:

```apache
<VirtualHost *:80>
    DocumentRoot "/path/to/uishop"
    ServerName uishop.local
    
    <Directory "/path/to/uishop">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Using Nginx

Example Nginx configuration:

```nginx
server {
    listen 80;
    server_name uishop.local;
    root /path/to/uishop;
    index index.php index.html;

    # Frontend assets
    location ~* ^/(css|js|assets)/ {
        try_files /frontend$uri =404;
    }

    # API routes
    location /api {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # NoJS routes
    location /nojs {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Frontend SPA (default)
    location / {
        try_files /frontend$uri /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## Migration Notes

### From Previous Setup

If you were running the modules separately:

**Before:**
- Backend: `http://localhost:8000/` (from `backend/public/`)
- Frontend: `http://localhost:3000/` (separate server)
- NoJS: `http://localhost:8080/` (from `nojs/public/`)

**After:**
- Backend API: `http://localhost:8000/api/`
- Frontend: `http://localhost:8000/`
- NoJS: `http://localhost:8000/nojs`

### Configuration Updates

The following files were updated to use the new routing:

1. **frontend/js/api.js**
   - Changed `BASE_URL` from `http://localhost:8000/api` to `/api`
   - Now uses relative URLs for API calls

2. **nojs/public/index.php**
   - Changed from hardcoded `http://localhost:8000/api` to dynamic host detection
   - Automatically constructs API URL based on current server

## Benefits

1. **Single Entry Point**: One server, one port, one URL
2. **Unified Deployment**: All modules deploy together
3. **CORS-Free**: No cross-origin issues between frontend and API
4. **Simplified Development**: Start one server instead of three
5. **Flexible Routing**: Easy to add new modules or routes

## Troubleshooting

### 404 Errors

If you get 404 errors:
1. Ensure `.htaccess` is in the root directory
2. Verify `mod_rewrite` is enabled in Apache
3. Check that the `AllowOverride All` directive is set in Apache config

### API Calls Failing

If API calls from frontend fail:
1. Check browser console for errors
2. Verify the backend database is set up (run `php backend/setup.php`)
3. Check that `.env` file exists in `backend/` directory

### Assets Not Loading

If CSS/JS files don't load:
1. Check the browser network tab for 404s
2. Verify files exist in `frontend/` directory
3. Check file permissions

## Testing the Routes

```bash
# Test frontend (should return HTML)
curl http://localhost:8000/

# Test API (should return JSON)
curl http://localhost:8000/api/products

# Test NoJS (should return HTML)
curl http://localhost:8000/nojs

# Test frontend asset
curl http://localhost:8000/css/styles.css
```
