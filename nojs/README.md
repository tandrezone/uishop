# UIShop NoJS - Server-Side Rendered Version

This is a no-JavaScript version of the UIShop application that provides all the functionality of the backend API through server-side rendered HTML pages.

## Features

- **No JavaScript Required**: Pure HTML and CSS interface with PHP backend
- **Full API Integration**: Connects to the existing UIShop backend API
- **Complete Functionality**:
  - User authentication (login/register)
  - Product browsing and search
  - Shopping cart management
  - Order placement and tracking
  - User profile management
  - Admin features (product CRUD, order management)

## Setup

1. **Requirements**:
   - PHP 7.4 or higher
   - Access to the UIShop backend API (running on port 8000)
   - Web server (Apache, Nginx, or PHP built-in server)

2. **Configuration**:
   - The application expects the API to be available at `http://localhost:8000/api`
   - This can be modified in `public/index.php` by changing the `API_BASE_URL` constant

3. **Running**:

   Using PHP built-in server:
   ```bash
   cd nojs/public
   php -S localhost:3000
   ```
   
   Then access the application at `http://localhost:3000`

   Using Apache/Nginx:
   - Set the document root to `nojs/public`
   - Ensure `.htaccess` or appropriate rewrite rules are configured

## Structure

```
nojs/
├── public/           # Public web root
│   └── index.php     # Main entry point and router
├── controllers/      # Page controllers
│   ├── auth.php          # Login/Register pages
│   ├── auth_handler.php  # Authentication form processing
│   ├── cart.php          # Shopping cart page
│   ├── orders.php        # Orders page
│   ├── products.php      # Products listing page
│   └── profile.php       # User profile page
├── views/            # View templates
│   ├── layout.php        # Main layout template
│   └── partials/         # Reusable view components
│       └── sidebar.php   # Navigation sidebar
├── includes/         # Helper files
│   ├── api.php           # API wrapper functions
│   └── helpers.php       # Utility functions
└── assets/           # Static assets
    └── css/
        └── styles.css    # Copied from frontend

```

## Design

The NoJS version maintains the same visual design as the JavaScript frontend:

- **Neo-Shamanic Glamour Theme**: Purple/magenta color scheme with glassmorphism effects
- **Responsive Layout**: Dashboard with sidebar navigation
- **Clean UI**: Modern card-based design for products and orders
- **Consistent Styling**: All CSS from the original frontend is preserved

## How It Works

1. **Session-Based Authentication**: 
   - User credentials are sent to the API
   - JWT token is stored in PHP session
   - Token is included in all subsequent API requests

2. **Server-Side Rendering**:
   - All pages are rendered on the server
   - Forms use POST requests for actions
   - Page refreshes show updated data

3. **API Communication**:
   - PHP cURL/file_get_contents makes requests to backend API
   - Responses are parsed and rendered as HTML
   - Errors are displayed as flash messages

4. **No Client-Side JavaScript**:
   - All interactions use standard HTML forms
   - Navigation uses regular links
   - CSRF protection for form submissions

## Pages

### Authentication
- **Login** (`?page=login`): User login form
- **Register** (`?page=register`): New user registration form

### Dashboard (Authenticated)
- **Products** (`?page=products`): Browse and search products, add to cart
- **Cart** (`?page=cart`): View cart items, update quantities, checkout
- **Orders** (`?page=orders`): View order history and details
- **Profile** (`?page=profile`): Edit user profile and change password

### Admin Features
- Create, update, and delete products
- View all orders (not just own orders)
- Update order status

## Security

- **CSRF Protection**: All forms include CSRF tokens
- **Session Security**: PHP sessions for authentication state
- **HTML Escaping**: All output is escaped to prevent XSS
- **Password Handling**: Passwords are only sent to the API, never stored

## Limitations

- No real-time updates (requires page refresh)
- No client-side validation (only server-side)
- No AJAX functionality
- Basic form handling only

## Browser Compatibility

Works on all browsers that support:
- HTML5
- CSS3 (including flexbox and grid)
- Form handling

No JavaScript required, so works even with JavaScript disabled!
