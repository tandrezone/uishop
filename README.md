# UIShop Dashboard

A modern, modular web-based dashboard for managing products and orders with role-based access control.

## Features

- 🔐 **Authentication System** — Login with username/password, JWT token-based auth
- 👥 **Role-Based Access Control** — Admin and user roles with role badges
- 📦 **Product Management** — View, create, and manage products
- 🗒️ **Order Management** — Track and manage orders
- 👤 **User Profile** — User account and profile management
- 💾 **State Management** — Centralized application state with localStorage persistence
- 🎨 **Responsive UI** — Clean, modern dashboard interface with sidebar navigation
- 🔄 **Token-Based API** — Automatic bearer token attachment to all API requests

## Project Structure

```
uishop/
├── index.html              # Main HTML template
├── css/
│   └── styles.css          # Global styles & layout
├── js/
│   ├── main.js             # Application entry point & bootstrap
│   ├── state.js            # Global state management
│   ├── auth.js             # Authentication & token handling
│   ├── api.js              # Centralized API client with request interceptor
│   ├── router.js           # Simple section router
│   ├── modal.js            # Modal dialog management
│   ├── products.js         # Products section rendering
│   ├── orders.js           # Orders section rendering
│   └── profile.js          # Profile section rendering
└── README.md               # This file
```

## Getting Started

### Prerequisites

- Modern web browser (ES6 module support required)
- Backend API server running at `/api` (configurable in `js/api.js`)
- Elasticsearch (optional, for full-text search on backend)

### Installation

1. Clone or download the project
2. Update the `BASE_URL` in `js/api.js` if your backend API is hosted elsewhere:
   ```javascript
   const BASE_URL = 'https://your-api.com/api';
   ```

3. Serve the project using a local web server:
   ```bash
   # Using Python 3
   python -m http.server 8000
   
   # Using Node.js with http-server
   npx http-server
   
   # Using PHP
   php -S localhost:8000
   ```

4. Open `http://localhost:8000` in your browser

## Usage

### Login
1. Enter your username and password on the login page
2. Click "Sign In"
3. On successful authentication, you'll be redirected to the dashboard

### Dashboard Navigation
The sidebar contains three main sections:
- **Products** (📦) — Browse and manage the product catalog
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
  orders: []             // Cached order list
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
- `POST /api/login` — Login endpoint
  - Request: `{ username, password }`
  - Response: `{ token, user }`

### Products
- `GET /api/products` — List products
- `POST /api/products` — Create product (admin only)
- `PUT /api/products/:id` — Update product (admin only)
- `DELETE /api/products/:id` — Delete product (admin only)

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