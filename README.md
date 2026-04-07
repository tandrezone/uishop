# UIShop Dashboard

A modern, modular web-based dashboard for managing products and orders with role-based access control.

## Quick Start

UIShop uses a unified routing system that serves three different versions from a single entry point:

- **Frontend (SPA)**: `http://localhost:8000/` - Modern JavaScript application
- **Backend API**: `http://localhost:8000/api/` - RESTful JSON API
- **NoJS Version**: `http://localhost:8000/nojs` - Server-side rendered (no JavaScript required)

To start the application:

```bash
# From the project root
php -S localhost:8000 router.php
```

Then open `http://localhost:8000/` in your browser.

📘 **For detailed routing documentation, see [ROUTING.md](ROUTING.md)**

## Features

- 🔐 **Authentication System** — Login with username/password, JWT token-based auth
- 👥 **Role-Based Access Control** — Admin and user roles with role badges
- 📦 **Product Management** — View, create, and manage products
- 🛒 **Shopping Cart** — Add products to cart, update quantities, and manage cart items
- 🗒️ **Order Management** — Track and manage orders
- 👤 **User Profile** — User account and profile management
- 💾 **State Management** — Centralized application state with localStorage persistence
- 🎨 **Responsive UI** — Clean, modern dashboard interface with sidebar navigation
- 🔄 **Token-Based API** — Automatic bearer token attachment to all API requests

## Project Structure

```
uishop/
├── index.php                # Global router (routes /, /api, /nojs)
├── .htaccess                # Apache URL rewriting configuration
├── backend/                 # Backend API (PHP)
│   ├── public/
│   │   └── index.php        # API entry point
│   ├── src/                 # API source code
│   └── config/              # API configuration
├── frontend/                # Frontend SPA (JavaScript)
│   ├── index.html           # Main HTML template
│   ├── css/
│   │   └── styles.css       # Global styles & layout
│   └── js/
│       ├── main.js          # Application entry point & bootstrap
│       ├── state.js         # Global state management
│       ├── auth.js          # Authentication & token handling
│       ├── api.js           # Centralized API client
│       ├── router.js        # Simple section router
│       ├── modal.js         # Modal dialog management
│       ├── products.js      # Products section rendering
│       ├── cart.js          # Shopping cart section rendering
│       ├── orders.js        # Orders section rendering
│       └── profile.js       # Profile section rendering
├── nojs/                    # NoJS version (server-side rendered)
│   ├── public/
│   │   └── index.php        # NoJS entry point
│   ├── views/               # HTML templates
│   └── controllers/         # Page controllers
└── ROUTING.md               # Routing documentation
```

## Getting Started

### Prerequisites

- PHP 8.0 or higher
- Modern web browser (ES6 module support required)
- Composer (for backend dependencies)
- SQLite or MySQL database

### Installation

1. Clone or download the project

2. Set up the backend:
   ```bash
   cd backend
   composer install
   cp .env.sqlite.example .env
   php setup.php
   ```

3. Start the application:
   ```bash
   # From the project root
   php -S localhost:8000 router.php
   ```

4. Open `http://localhost:8000` in your browser

The application will automatically route:
- `/` to the frontend SPA
- `/api` to the backend API
- `/nojs` to the NoJS version

## Usage

### Registration
1. On the login page, click "Register here"
2. Fill in the registration form:
   - Username (required)
   - Password (required)
   - Confirm Password (must match)
   - Email (optional)
3. Click "Create Account"
4. On successful registration, you'll be logged in and redirected to the dashboard

### Login
1. From the registration form, click "Sign in here" to return to login, or
2. Enter your username and password on the login page
3. Click "Sign In"
4. On successful authentication, you'll be redirected to the dashboard

### Dashboard Navigation
The sidebar contains four main sections:
- **Products** (📦) — Browse and manage the product catalog
- **Cart** (🛒) — View and manage shopping cart items
- **Orders** (🗒️) — View and manage orders
- **Profile** (👤) — View/edit user account information

### Logout
Click the "Logout" button in the sidebar footer to end your session

## Architecture

### State Management
Global application state is managed in `state.js`:
```javascript
{
  token: null,           // JWT token
  user: null,            // Decoded user object
  products: [],          // Cached product list
  orders: [],            // Cached order list
  cart: null             // Cached cart data with items, totalAmount, totalItems
}
```

### API Integration
All API requests go through `api.js`, which automatically:
- Attaches the `Authorization: Bearer {token}` header
- Handles 401 (Unauthorized) responses
- Dispatches `auth:expired` events for token refresh

### Authentication
The `auth.js` module handles:
- Token storage/retrieval from localStorage
- JWT decoding for user information
- Logout and session cleanup
- Role checks (admin/user)

### Routing
The `router.js` module provides simple client-side routing between sections without page reloads.

## Backend API Requirements

The backend should provide the following endpoints:

### Authentication
- `POST /api/auth/login` — Login endpoint
  - Request: `{ username, password }`
  - Response: `{ token, user }`

- `POST /api/auth/register` — Register endpoint
  - Request: `{ username, password, email? }`
  - Response: `{ token, user }`
  - Password confirmation is validated on the client side

### Products
- `GET /api/products` — List products
- `POST /api/products` — Create product (admin only)
- `PUT /api/products/:id` — Update product (admin only)
- `DELETE /api/products/:id` — Delete product (admin only)

### Shopping Cart
- `GET /api/cart` — Get user's cart
- `POST /api/cart/items` — Add item to cart
- `PUT /api/cart/items/:id` — Update cart item quantity
- `DELETE /api/cart/items/:id` — Remove item from cart
- `DELETE /api/cart` — Clear entire cart

### Orders
- `GET /api/orders` — List orders
- `POST /api/orders` — Create order
- `GET /api/orders/:id` — Get order details
- `PUT /api/orders/:id` — Update order (admin only)

### Profile
- `GET /api/profile` — Get current user profile
- `PUT /api/profile` — Update profile

All endpoints should respect the `Authorization: Bearer {token}` header and return 401 for invalid/expired tokens.

## Development Notes

### Modular JavaScript
The project uses ES6 modules for code organization. Each module has a clear responsibility:
- `main.js` — Bootstrap and event wiring
- `state.js` — Data storage
- `auth.js` — Auth logic
- `api.js` — HTTP client
- `router.js` — Navigation
- Section modules — UI rendering

### Adding New Sections
To add a new dashboard section:
1. Create a new file in `js/`, e.g., `js/analytics.js`
2. Export a render function: `export function renderAnalytics() { ... }`
3. Add to routes in `js/router.js`
4. Add navigation link in `index.html`

### Styling
CSS uses CSS custom properties (variables) for theming. Edit `css/styles.css` to customize colors, spacing, and layout.

### Error Handling
- API errors are logged to console and may display error messages in modals
- 401 responses trigger automatic redirect to login page
- Login errors display in the login form

## Browser Support

- Chrome/Edge 60+
- Firefox 55+
- Safari 10.1+
- Requires ES6 module support

## License

This project is provided as-is for development purposes.