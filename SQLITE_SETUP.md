# SQLite Setup Guide for UIShop Backend

**Yes!** You can absolutely use SQLite instead of MySQL. The backend now supports both!

## Why Use SQLite?

✅ **Advantages:**
- **Zero Configuration** - No database server to install
- **Portable** - Single file database
- **Development Friendly** - Perfect for local development
- **Lightweight** - Fast for small projects
- **Easy Backup** - Just copy the `.db` file
- **No Credentials** - No username/password needed

❌ **Limitations:**
- Not ideal for multi-user production workloads
- Limited concurrency compared to MySQL
- Better for development and small deployments

## Quick Setup with SQLite

### 1. Update `.env` File

Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

Edit `.env` and use SQLite configuration:
```env
# Database - SQLite
DB_DRIVER=sqlite
DB_FILE=./data/uishop.db

# JWT
JWT_SECRET=your-super-secret-key-change-this-in-production
JWT_EXPIRY=86400

# Environment
APP_ENV=development
APP_DEBUG=true
```

### 2. Run Setup

```bash
php setup.php
```

This will:
- Create `data/` directory
- Create `data/uishop.db` file
- Initialize database tables
- Done! ✅

### 3. Start Development Server

```bash
php -S localhost:8000 -t public/
```

## File Structure with SQLite

After setup:
```
backend/
├── data/
│   └── uishop.db         # ← SQLite database file (auto-created)
├── public/
├── src/
├── config/
└── .env
```

## Testing SQLite Setup

### Register a User
```bash
curl -X POST http://localhost:8000/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "password": "password123",
    "email": "test@example.com"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "password": "password123"
  }'
```

## Switching Between MySQL and SQLite

### Switch to MySQL
Edit `.env`:
```env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=uishop
DB_USER=root
DB_PASS=
```

Then run:
```bash
php setup.php
```

### Switch to SQLite
Edit `.env`:
```env
DB_DRIVER=sqlite
DB_FILE=./data/uishop.db
```

Then run:
```bash
php setup.php
```

## SQLite Database Management

### View Database with Command Line

```bash
# Access SQLite shell
sqlite3 ./data/uishop.db

# View tables
.tables

# View users table structure
.schema users

# Query users
SELECT * FROM users;

# Exit
.exit
```

### Export Database

```bash
# Backup database
cp ./data/uishop.db ./data/uishop.db.backup
```

### View with GUI Tools

Free SQLite GUI tools:
- **DB Browser for SQLite** - https://sqlitebrowser.org/
- **SQLiteStudio** - https://sqlitestudio.pl/
- **VS Code Extension** - SQLite

## Performance Tuning (SQLite)

For better performance with SQLite:

```php
// Already enabled in Database.php:
PRAGMA foreign_keys = ON;        // Enforce foreign keys
PRAGMA synchronous = NORMAL;     // Balance speed/safety
PRAGMA cache_size = -64000;      // More cache
PRAGMA temp_store = MEMORY;      // Temp in memory
```

## Production Recommendation

| Environment             | Database                 |
| ----------------------- | ------------------------ |
| 🖥️ **Local Development** | **SQLite** (recommended) |
| 🧪 **Testing**           | **SQLite**               |
| 🚀 **Production**        | **MySQL/PostgreSQL**     |

## Backup and Restore

### Backup SQLite Database
```bash
# Create backup
cp data/uishop.db data/uishop.db.`date +%Y%m%d_%H%M%S`.backup
```

### Restore SQLite Database
```bash
# Restore from backup
cp data/uishop.db.20240115_120000.backup data/uishop.db
```

## Troubleshooting

### "database is locked" Error
- SQLite uses file locks
- Reduce concurrent requests during testing
- Production MySQL handles this better

### Database File Not Created
- Check `data/` directory permissions
- Ensure PHP has write access to the `backend/` directory
- Try: `chmod 755 backend/`

### Can't Connect to Database
```bash
# Verify SQLite file exists
ls -la data/uishop.db

# Check file permissions
stat data/uishop.db

# Test connection
sqlite3 data/uishop.db ".tables"
```

## Migration Path: SQLite → MySQL

When ready to move to production with MySQL:

1. **Keep both databases in `.env`** - temporarily
2. **Write migration script** to copy data
3. **Test thoroughly** with MySQL
4. **Switch `.env` to MySQL**

## Code Changes Not Needed

✅ **Good news!** The following already work with both databases:
- ✅ `src/Core/Database.php` - Auto-detects driver
- ✅ `src/Models/User.php` - Works with both
- ✅ Database queries - Same SQL syntax
- ✅ All endpoints - No changes needed

Just update `.env` and run `php setup.php`!

## Configuration Reference

### SQLite Configuration
```env
DB_DRIVER=sqlite
DB_FILE=./data/uishop.db
```

### MySQL Configuration
```env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=uishop
DB_USER=root
DB_PASS=yourpassword
```

## One-Command SQLite Setup

```bash
# Choose SQLite, install, setup
cd backend && \
cp .env.example .env && \
echo "DB_DRIVER=sqlite" >> .env && \
echo "DB_FILE=./data/uishop.db" >> .env && \
composer install && \
php setup.php && \
echo "✅ Ready! Run: php -S localhost:8000 -t public/"
```

## Monitoring SQLite

Check database size and health:
```bash
# Database file size
ls -lh data/uishop.db

# Optimize database
sqlite3 data/uishop.db "VACUUM;"

# Check integrity
sqlite3 data/uishop.db "PRAGMA integrity_check;"
```

---

**Summary:** Yes, SQLite works perfectly! Perfect for development and testing. Just select SQLite in `.env` and you're good to go! 🎉
