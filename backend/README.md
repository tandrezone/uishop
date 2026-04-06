# UIShop Backend API

A PSR-compliant PHP REST API for UIShop Dashboard with JWT authentication.

**Supports both MySQL and SQLite** - Choose your database!

## Architecture & PSR Compliance

This backend follows PHP Standards Recommendations (PSR):
- **PSR-1**: Basic Coding Standard (file organization, naming conventions)
- **PSR-4**: Autoloading Standard (namespace to file mapping via Composer)
- **PSR-12**: Extended Coding Style (code formatting and structure)

## Project Structure

```
backend/
├── public/
│   └── index.php              # Application entry point
├── src/
│   ├── Core/
│   │   ├── Environment.php    # .env loader
│   │   ├── Database.php       # PDO connection (singleton)
│   │   ├── JwtHandler.php     # JWT encoding/decoding
│   │   ├── Request.php        # HTTP request handling
│   │   ├── Response.php       # HTTP response formatting
│   │   └── Router.php         # Route dispatcher
│   ├── Controllers/
│   │   └── AuthController.php # Authentication logic
│   └── Models/
│       └── User.php           # User model & database interactions
├── config/
│   └── routes.php             # Route definitions
├── .env.example               # Environment configuration template
├── composer.json              # PHP dependencies
└── README.md
```

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- PDO extension
- Composer

## Installation

### 1. Clone/Copy the Project

```bash
cd backend
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

Copy `.env.example` to `.env` and update configuration:

```bash
cp .env.example .env
```

Edit `.env`:
```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=uishop
DB_USER=root
DB_PASS=your_password

JWT_SECRET=your-super-secret-key-change-this-in-production
JWT_EXPIRY=86400

APP_ENV=development
APP_DEBUG=true
```

### 4. Create Database

Create MySQL database:

```sql
CREATE DATABASE uishop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run Database Setup (Optional)

Create a `setup.php` script in the root directory to initialize tables:

```php
<?php
require 'vendor/autoload.php';

\App\Core\Environment::load();

$user = new \App\Models\User();
$user->createTable();

echo "Database tables created successfully!";
```

Then run:
```bash
php setup.php
```

Or the tables will be created automatically on first login/register attempt.

### 6. Start Development Server

```bash
php -S localhost:8000 -t public/
```

The API will be available at `http://localhost:8000`

## API Usage

### Base URL
```
http://localhost:8000
```

### Authentication Endpoints

#### Register
```bash
curl -X POST http://localhost:8000/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "john_doe",
    "password": "password123",
    "email": "john@example.com"
  }'
```

Response (201 Created):
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "role": "user"
  }
}
```

#### Login
```bash
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "john_doe",
    "password": "password123"
  }'
```

Response (200 OK):
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "role": "user"
  }
}
```

## Error Responses

All errors follow this format:

```json
{
  "error": "Invalid username or password",
  "status": 401
}
```

### Common Error Codes
- `400` Bad Request - Validation failed
- `401` Unauthorized - Invalid credentials
- `409` Conflict - Username/email already exists
- `500` Internal Server Error - Server error

## JWT Token Format

Tokens are signed with HS256 and contain:

```json
{
  "id": 1,
  "username": "john_doe",
  "email": "john@example.com",
  "role": "user",
  "sub": 1,
  "iat": 1234567890,
  "exp": 1234654290
}
```

### Token Expiry

Token expiration time is configured via `JWT_EXPIRY` in `.env` (default: 86400 seconds = 24 hours).

## Security Best Practices

1. **Change JWT_SECRET in Production**
   - Generate a strong random key
   - Keep it secret and never commit to version control

2. **Use HTTPS in Production**
   - Always use HTTPS for API calls
   - Set secure CORS headers

3. **Rate Limiting**
   - Implement rate limiting on login/register endpoints
   - Prevent brute force attacks

4. **Password Storage**
   - Passwords are hashed with bcrypt
   - Never store plain passwords

5. **CORS Configuration**
   - Currently allows all origins (`Access-Control-Allow-Origin: *`)
   - In production, specify your frontend domain:
     ```php
     header('Access-Control-Allow-Origin: https://yourdomain.com');
     ```

## Extending the API

### Adding New Routes

Edit `config/routes.php`:

```php
$router->route('GET', '/products', function (\App\Core\Request $request) {
    // Handler logic
});
```

### Creating New Controllers

1. Create `src/Controllers/ProductController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

final class ProductController
{
    private Response $response;

    public function __construct()
    {
        $this->response = new Response();
    }

    public function list(Request $request): void
    {
        // Implementation
    }
}
```

2. Register routes in `config/routes.php`:

```php
$productController = new \App\Controllers\ProductController();

$router->route('GET', '/products', function (\App\Core\Request $request) use ($productController) {
    $productController->list($request);
});
```

### Creating New Models

Create `src/Models/Product.php` following the User model pattern:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Core\Database;

final class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Implement methods
}
```

## Testing

### Using cURL

Test registration:
```bash
curl -X POST http://localhost:8000/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"test123","email":"test@example.com"}'
```

Test login:
```bash
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"test123"}'
```

### Using VS Code REST Client Extension

Create `test.http`:

```http
### Register
POST http://localhost:8000/auth/register
Content-Type: application/json

{
  "username": "testuser",
  "password": "password123",
  "email": "test@example.com"
}

### Login
POST http://localhost:8000/auth/login
Content-Type: application/json

{
  "username": "testuser",
  "password": "password123"
}
```

## Troubleshooting

### "Database connection failed"
- Verify MySQL is running
- Check `DB_HOST`, `DB_USER`, `DB_PASS` in `.env`
- Ensure database exists

### "Route not found"
- Check route definition in `config/routes.php`
- Verify request path matches (e.g., `/auth/login` not `/api/auth/login`)

### "Invalid token"
- Verify `JWT_SECRET` matches on frontend
- Check token expiration time

### "Class not found"
- Run `composer dump-autoload`
- Verify namespace matches file structure

## Production Deployment

1. **Set Environment**
   ```
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Generate Strong JWT Secret**
   ```bash
   php -r 'echo bin2hex(random_bytes(32));'
   ```

3. **Configure Web Server (Apache/Nginx)**
   - Point DocumentRoot to `public/` directory
   - Ensure `.env` is outside web root

4. **Enable HTTPS**
   - Use SSL/TLS certificates
   - Redirect HTTP to HTTPS

5. **Implement Rate Limiting**
   - Use middleware or web server rules

6. **Set Up Logging**
   - Log API errors and access
   - Monitor suspicious activity

## License

This project is provided as-is for development purposes.
