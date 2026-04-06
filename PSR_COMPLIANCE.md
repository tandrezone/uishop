# PSR Compliance Documentation

This backend PHP API adheres to PHP Standard Recommendations (PSR) for code quality, maintainability, and interoperability.

## PSR Standards Implemented

### PSR-1: Basic Coding Standard
**File and Class Conventions**

✅ **Implementation in UIShop API:**
- Files use `<?php` tag with `declare(strict_types=1);` for type safety
- Each class/file has exactly one primary class
- Class names use `StudlyCaps` (e.g., `AuthController`, `JwtHandler`)
- Method names use `camelCase` (e.g., `getUsername()`, `verifyPassword()`)
- Constants use `UPPER_SNAKE_CASE` (though minimal in this project)
- File names match class names exactly
- All source files end with a single newline

**Example:**
```php
<?php

declare(strict_types=1);

namespace App\Controllers;

final class AuthController
{
    public function login(Request $request): void
    {
        // Implementation
    }
}
```

---

### PSR-4: Autoloader Standard
**Namespace to File Structure**

✅ **Implementation in UIShop API:**
- `composer.json` defines PSR-4 autoloading:
  ```json
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
  ```

- Directory structure matches namespacing:
  ```
  src/
  ├── Core/Environment.php       → App\Core\Environment
  ├── Controllers/AuthController.php → App\Controllers\AuthController
  └── Models/User.php            → App\Models\User
  ```

- Fully qualified namespace on every file
- No underscore separators in namespaces
- Automatic class loading via Composer

**Usage:**
```php
require_once 'vendor/autoload.php';

// Classes auto-loaded via PSR-4
$user = new \App\Models\User();
$auth = new \App\Controllers\AuthController();
```

---

### PSR-12: Extended Coding Style Guide
**Code Formatting and Structure**

✅ **Implementation in UIShop API:**

**1. Opening/Closing Tags**
```php
<?php

declare(strict_types=1);

namespace App\Core;
// ... code ...
// End of file (no ?>)
```

**2. Indentation**
- Uses 4 spaces (not tabs)
- Consistent throughout all files

**3. Line Length**
- Soft limit around 120 characters
- Hard limit not enforced for readability

**4. Blank Lines**
- Single blank line between methods/functions
- Double blank line between class sections

**5. Type Declarations**
```php
public function login(Request $request): void
{
    // Parameter and return types always declared
}

private function setPath(string $path): self
{
    return $this;
}
```

**6. Visibility Keywords (Always Explicit)**
```php
private string $path;
public function getPath(): string
private function validate(string $input): bool
final class AuthController  // Use 'final' when appropriate
```

**7. Access Control Modifiers**
- Always specify `public`, `private`, `protected`
- Use `final` for classes that shouldn't be extended
- Use `static` only when needed for singleton patterns

**8. Namespace and Use Statements**
```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;         // One use per line
use App\Core\Response;
use App\Models\User;

// Code follows use statements
```

---

### Additional PSR Guidelines (Advisory)

#### PSR-3: Logger Interface
**Status:** Recommended but not implemented (simple error handling sufficient for this backend)

Could be added for production with logging framework like:
- Monolog
- Symfony Logger

#### PSR-7: HTTP Message Interfaces
**Status:** Partially implemented
- Custom `Request` and `Response` classes instead of full PSR-7
- Sufficient for REST API purposes

#### PSR-11: Container Interface
**Status:** Not implemented
- Dependency injection handled manually
- Could use a DI container (PHP-DI, Pimple) for larger projects

---

## Compliance Verification

### File Structure Check
```bash
# Verify all files follow naming conventions
find src -name "*.php" | while read f; do
  echo "File: $f"
  head -5 "$f" | grep "namespace"
done
```

### Coding Standards Check (PSR-12)
```bash
# Using PHP CodeSniffer (install via composer)
composer require --dev squizlabs/php_codesniffer

# Run PSR-12 check
./vendor/bin/phpcs --standard=PSR12 src/
```

### Autoloader Validation
```php
<?php
require 'vendor/autoload.php';

// All these should work if PSR-4 is correct
new \App\Core\Environment();
new \App\Core\Database();
new \App\Core\JwtHandler();
new \App\Core\Request();
new \App\Core\Response();
new \App\Core\Router();
new \App\Controllers\AuthController();
new \App\Models\User();

echo "✅ All classes auto-loaded successfully!";
```

---

## PSR Benefits in This Project

### 1. **Maintainability**
- Consistent structure across files
- Easy to locate classes and methods
- Predictable naming patterns

### 2. **Interoperability**
- Can integrate third-party PSR-compliant libraries
- Works with standard PHP tools and IDEs
- Future developers familiar with PSR standards

### 3. **Type Safety**
- Strict types declaration on all files
- Full type hints on methods
- IDE auto-completion supports

### 4. **Scalability**
- Can easily add new features/endpoints
- Clear separation of concerns (Core, Controllers, Models)
- Automatic class loading handles growth

### 5. **Quality Assurance**
- Consistent code format enables automated testing
- Easier code review and auditing
- Integration with CI/CD pipelines

---

## Extending While Maintaining PSR Compliance

### Adding a New Controller
```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Product;

final class ProductController
{
    private Product $productModel;
    private Response $response;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->response = new Response();
    }

    public function list(Request $request): void
    {
        // Implementation following PSR patterns
    }
}
```

### Adding a New Model
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

    // Methods following PSR-12 standards
}
```

### Registering New Routes
```php
// In config/routes.php

$productController = new \App\Controllers\ProductController();

$router->route('GET', '/products', function (\App\Core\Request $request) use ($productController) {
    $productController->list($request);
});
```

---

## CI/CD Integration

### Pre-commit Hook
```bash
#!/bin/bash
# .git/hooks/pre-commit

# Run PSR-12 check before committing
./vendor/bin/phpcs --standard=PSR12 src/

if [ $? -ne 0 ]; then
    echo "❌ Code style violations found. Please fix them."
    exit 1
fi
```

### GitHub Actions Example
```yaml
name: PSR Compliance Check

on: [push, pull_request]

jobs:
  phpcs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: php-actions/composer@v6
      - run: ./vendor/bin/phpcs --standard=PSR12 src/
```

---

## References

- [PSR-1: Basic Coding Standard](https://www.php-fig.org/psr/psr-1/)
- [PSR-4: Autoloader](https://www.php-fig.org/psr/psr-4/)
- [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/)
- [PHP Standards Recommendations](https://www.php-fig.org/)

---

## Summary

This UIShop backend demonstrates proper PSR compliance through:
- ✅ Strict file organization (PSR-1)
- ✅ Automatic Composer autoloading (PSR-4)
- ✅ Clean, readable code formatting (PSR-12)
- ✅ Type safety and declarations
- ✅ Consistent naming conventions
- ✅ Clear separation of concerns

This makes the codebase professional, maintainable, and ready for production use.
