# Quick Start Guide - UIShop PHP Backend

## 30-Second Setup with SQLite ⭐

**SQLite requires zero configuration!** Perfect for development.

```bash
cd backend
cp .env.sqlite.example .env
composer install
php setup.php
php -S localhost:8000 -t public/
```

✅ API ready at `http://localhost:8000`

---

## Setup with MySQL

If you prefer MySQL, follow these steps:

```bash
cd backend
cp .env.example .env
```

Edit `.env` with your database credentials:
```env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=uishop
DB_USER=root
DB_PASS=yourpassword
```

Then run:
```bash
composer install
php setup.php
php -S localhost:8000 -t public/
```

✅ API ready at `http://localhost:8000`

---

## Need SQLite or MySQL Later?

See [SQLITE_SETUP.md](../SQLITE_SETUP.md) for detailed database switching instructions.

---

## Test the API

### Register a New User
```bash
curl -X POST http://localhost:8000/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "demo",
    "password": "demo123",
    "email": "demo@example.com"
  }'
```

Expected Response:
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "username": "demo",
    "email": "demo@example.com",
    "role": "user"
  }
}
```

### Login
```bash
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "demo",
    "password": "demo123"
  }'
```

---

## Project Features

✅ **PSR Compliant**
- PSR-1, PSR-4, PSR-12 standards
- Clean namespace organization
- Autoloading via Composer

✅ **Secure Authentication**
- JWT tokens (HS256)
- Bcrypt password hashing
- Token expiration

✅ **Error Handling**
- Consistent error responses
- Proper HTTP status codes
- Debug mode for development

✅ **Database Integration**
- PDO with prepared statements
- Connection singleton pattern
- Automatic table creation

---

## File Structure

```
backend/
├── public/index.php              # Entry point
├── src/
│   ├── Core/                    # Framework classes
│   ├── Controllers/             # Business logic
│   └── Models/                  # Database models
├── config/routes.php            # Route definitions
├── .env.example                 # Configuration template
├── composer.json               # Dependencies
└── setup.php                   # Database setup script
```

---

## Environment Variables

```bash
# Database Configuration
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=uishop
DB_USER=root
DB_PASS=

# JWT Configuration
JWT_SECRET=your-secret-key
JWT_EXPIRY=86400

# Application Settings
APP_ENV=development
APP_DEBUG=true
```

---

## Available Endpoints

### Authentication

| Method | Endpoint         | Description       |
| ------ | ---------------- | ----------------- |
| `POST` | `/auth/login`    | Login user        |
| `POST` | `/auth/register` | Register new user |

**Request Body (Login):**
```json
{
  "username": "string",
  "password": "string"
}
```

**Request Body (Register):**
```json
{
  "username": "string",
  "password": "string",
  "email": "string (optional)"
}
```

**Response:**
```json
{
  "token": "string (JWT)",
  "user": {
    "id": "number",
    "username": "string",
    "email": "string",
    "role": "string"
  }
}
```

---

## Common Tasks

### Change JWT Secret
```bash
# Generate a strong secret
php -r 'echo bin2hex(random_bytes(32));'

# Update in .env
JWT_SECRET=<your-generated-secret>
```

### Enable CORS for Specific Domain
Edit `public/index.php`:
```php
header('Access-Control-Allow-Origin: https://yourdomain.com');
```

### Add Database Index
Modify `User::createTable()` in `src/Models/User.php`:
```php
// Add more indexes as needed
```

### Create New API Endpoint

1. Create controller in `src/Controllers/`
2. Register route in `config/routes.php`
3. Use `Request` to get data, `Response` to send data

Example:
```php
$router->route('GET', '/status', function (\App\Core\Request $request) {
    (new \App\Core\Response())->success(['status' => 'ok']);
});
```

---

## Debugging

### Enable Debug Mode
In `.env`:
```
APP_DEBUG=true
```

### Check Database Connection
```php
php -r "
require 'vendor/autoload.php';
\App\Core\Environment::load();
\App\Core\Database::getConnection();
echo 'Connected!';
"
```

### Verify Token
```php
php -r "
require 'vendor/autoload.php';
\App\Core\Environment::load();
\$token = 'your-jwt-token';
\$payload = \App\Core\JwtHandler::decode(\$token);
var_dump(\$payload);
"
```

---

## Production Checklist

- [ ] Change `APP_DEBUG=false`
- [ ] Change `JWT_SECRET` to a strong random value
- [ ] Set `APP_ENV=production`
- [ ] Use HTTPS only
- [ ] Update CORS headers
- [ ] Configure database for production
- [ ] Set up error logging
- [ ] Implement rate limiting
- [ ] Run security scan

---

## Troubleshooting

**"Database connection failed"**
→ Check `.env` database credentials

**"Route not found"**
→ Verify path in `config/routes.php`

**"Class not found"**
→ Run `composer dump-autoload`

**"Invalid token"**
→ Check token hasn't expired or `JWT_SECRET` matches

---

## Next Steps

1. ✅ Backend API running locally
2. 📦 Configure Products endpoint (`src/Controllers/ProductController.php`)
3. 🗂️ Configure Orders endpoint (`src/Controllers/OrderController.php`)
4. 👤 Configure User Profile endpoint
5. 🚀 Deploy to production server

---

Need help? Check the full [Backend README](README.md) or the [API Endpoints](../API_ENDPOINTS.md) documentation.
