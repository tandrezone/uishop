# UIShop API Endpoints

Complete API specification for the UIShop Dashboard backend.

**Base URL:** `/api` (or configure in `js/api.js`)

**Authentication:** All endpoints (except `/auth/login` and `/auth/register`) require the `Authorization: Bearer {token}` header with a valid JWT token.

---

## Authentication Endpoints

### 1. Login

**Endpoint:** `POST /auth/login`

**Description:** Authenticate a user with username and password.

**Request Body:**
```json
{
  "username": "string (required)",
  "password": "string (required)"
}
```

**Response (200 OK):**
```json
{
  "token": "string (JWT token)",
  "user": {
    "id": "string",
    "username": "string",
    "email": "string (optional)",
    "role": "string (e.g., 'user' or 'admin')",
    "name": "string (optional)",
    "sub": "string (optional, subject/user ID from token)"
  }
}
```

**Errors:**
- `401 Unauthorized` - Invalid username or password
- `400 Bad Request` - Missing required fields
- `500 Internal Server Error` - Server error

---

### 2. Register

**Endpoint:** `POST /auth/register`

**Description:** Create a new user account.

**Request Body:**
```json
{
  "username": "string (required, 3-20 characters)",
  "password": "string (required, min 6 characters)",
  "email": "string (optional, valid email format)"
}
```

**Response (201 Created):**
```json
{
  "token": "string (JWT token)",
  "user": {
    "id": "string",
    "username": "string",
    "email": "string (optional)",
    "role": "string (default: 'user')",
    "name": "string (optional)"
  }
}
```

**Errors:**
- `400 Bad Request` - Validation failed (e.g., username taken, weak password)
- `409 Conflict` - Username or email already exists
- `500 Internal Server Error` - Server error

---

## Products Endpoints

### 3. Get All Products

**Endpoint:** `GET /products`

**Description:** Retrieve list of all products.

**Query Parameters:** (optional)
```
?search=keyword     // Search by name or description
?limit=10           // Results per page (default: 20)
?offset=0           // Pagination offset (default: 0)
```

**Response (200 OK):**
```json
{
  "products": [
    {
      "id": "string",
      "name": "string",
      "description": "string",
      "price": "number (in base currency units)",
      "stock": "number",
      "image": "string (optional, URL or base64)",
      "category": "string (optional)",
      "createdBy": "string (user ID)",
      "createdAt": "string (ISO 8601 timestamp)",
      "updatedAt": "string (ISO 8601 timestamp)"
    }
  ],
  "total": "number (total count)",
  "limit": "number",
  "offset": "number"
}
```

**Errors:**
- `401 Unauthorized` - Missing or invalid token
- `500 Internal Server Error` - Server error

---

### 4. Create Product

**Endpoint:** `POST /products`

**Description:** Create a new product. Admin only.

**Request Body:**
```json
{
  "name": "string (required, 1-255 characters)",
  "description": "string (optional, max 2000 characters)",
  "price": "number (required, > 0)",
  "stock": "number (required, >= 0)",
  "image": "string (optional, URL or base64 encoded)",
  "category": "string (optional)"
}
```

**Response (201 Created):**
```json
{
  "id": "string",
  "name": "string",
  "description": "string",
  "price": "number",
  "stock": "number",
  "image": "string",
  "category": "string",
  "createdBy": "string",
  "createdAt": "string (ISO 8601 timestamp)",
  "updatedAt": "string (ISO 8601 timestamp)"
}
```

**Errors:**
- `400 Bad Request` - Validation failed
- `401 Unauthorized` - Missing or invalid token
- `403 Forbidden` - User is not admin
- `500 Internal Server Error` - Server error

---

### 5. Update Product

**Endpoint:** `PUT /products/:id`

**Description:** Update an existing product. Admin only.

**Path Parameters:**
```
:id  - Product ID (string, required)
```

**Request Body:** (all optional, send only fields to update)
```json
{
  "name": "string (optional)",
  "description": "string (optional)",
  "price": "number (optional, > 0)",
  "stock": "number (optional, >= 0)",
  "image": "string (optional)",
  "category": "string (optional)"
}
```

**Response (200 OK):**
```json
{
  "id": "string",
  "name": "string",
  "description": "string",
  "price": "number",
  "stock": "number",
  "image": "string",
  "category": "string",
  "createdBy": "string",
  "createdAt": "string (ISO 8601 timestamp)",
  "updatedAt": "string (ISO 8601 timestamp)"
}
```

**Errors:**
- `400 Bad Request` - Validation failed
- `401 Unauthorized` - Missing or invalid token
- `403 Forbidden` - User is not admin
- `404 Not Found` - Product not found
- `500 Internal Server Error` - Server error

---

### 6. Delete Product

**Endpoint:** `DELETE /products/:id`

**Description:** Delete a product. Admin only.

**Path Parameters:**
```
:id  - Product ID (string, required)
```

**Response (204 No Content):**
```
(empty body)
```

**Errors:**
- `401 Unauthorized` - Missing or invalid token
- `403 Forbidden` - User is not admin
- `404 Not Found` - Product not found
- `500 Internal Server Error` - Server error

---

## Orders Endpoints

### 7. Get All Orders

**Endpoint:** `GET /orders`

**Description:** Retrieve all orders. Admin can see all, regular users see only their own.

**Query Parameters:** (optional)
```
?status=pending         // Filter by status (pending, processing, completed, cancelled)
?limit=10               // Results per page (default: 20)
?offset=0               // Pagination offset (default: 0)
```

**Response (200 OK):**
```json
{
  "orders": [
    {
      "id": "string",
      "userId": "string",
      "products": [
        {
          "productId": "string",
          "quantity": "number",
          "price": "number (price at time of order)"
        }
      ],
      "totalAmount": "number",
      "status": "string (pending|processing|completed|cancelled)",
      "shippingAddress": "string (optional)",
      "notes": "string (optional)",
      "createdAt": "string (ISO 8601 timestamp)",
      "updatedAt": "string (ISO 8601 timestamp)"
    }
  ],
  "total": "number",
  "limit": "number",
  "offset": "number"
}
```

**Errors:**
- `401 Unauthorized` - Missing or invalid token
- `500 Internal Server Error` - Server error

---

### 8. Get My Orders

**Endpoint:** `GET /orders/my`

**Description:** Retrieve all orders for the authenticated user.

**Query Parameters:** (optional)
```
?status=pending         // Filter by status
?limit=10               // Results per page
?offset=0               // Pagination offset
```

**Response (200 OK):**
```json
{
  "orders": [
    {
      "id": "string",
      "userId": "string",
      "products": [
        {
          "productId": "string",
          "quantity": "number",
          "price": "number"
        }
      ],
      "totalAmount": "number",
      "status": "string",
      "shippingAddress": "string",
      "notes": "string",
      "createdAt": "string (ISO 8601 timestamp)",
      "updatedAt": "string (ISO 8601 timestamp)"
    }
  ],
  "total": "number",
  "limit": "number",
  "offset": "number"
}
```

**Errors:**
- `401 Unauthorized` - Missing or invalid token
- `500 Internal Server Error` - Server error

---

### 9. Get Order Details

**Endpoint:** `GET /orders/:id`

**Description:** Retrieve details for a specific order.

**Path Parameters:**
```
:id  - Order ID (string, required)
```

**Response (200 OK):**
```json
{
  "id": "string",
  "userId": "string",
  "products": [
    {
      "productId": "string",
      "quantity": "number",
      "price": "number"
    }
  ],
  "totalAmount": "number",
  "status": "string",
  "shippingAddress": "string",
  "notes": "string",
  "createdAt": "string (ISO 8601 timestamp)",
  "updatedAt": "string (ISO 8601 timestamp)"
}
```

**Errors:**
- `401 Unauthorized` - Missing or invalid token
- `403 Forbidden` - Not authorized to view this order
- `404 Not Found` - Order not found
- `500 Internal Server Error` - Server error

---

### 10. Create Order

**Endpoint:** `POST /orders`

**Description:** Create a new order.

**Request Body:**
```json
{
  "products": [
    {
      "productId": "string (required)",
      "quantity": "number (required, > 0)"
    }
  ],
  "shippingAddress": "string (optional)",
  "notes": "string (optional)"
}
```

**Response (201 Created):**
```json
{
  "id": "string",
  "userId": "string",
  "products": [
    {
      "productId": "string",
      "quantity": "number",
      "price": "number"
    }
  ],
  "totalAmount": "number",
  "status": "string (default: 'pending')",
  "shippingAddress": "string",
  "notes": "string",
  "createdAt": "string (ISO 8601 timestamp)",
  "updatedAt": "string (ISO 8601 timestamp)"
}
```

**Errors:**
- `400 Bad Request` - Validation failed (e.g., invalid product IDs, empty products array)
- `401 Unauthorized` - Missing or invalid token
- `404 Not Found` - Product not found
- `409 Conflict` - Insufficient stock
- `500 Internal Server Error` - Server error

---

### 11. Update Order

**Endpoint:** `PUT /orders/:id`

**Description:** Update an order. Admin can update any order; users can only update their own orders (limited fields).

**Path Parameters:**
```
:id  - Order ID (string, required)
```

**Request Body:** (Admin can update all fields, users can update limited fields)
```json
{
  "status": "string (admin only: pending|processing|completed|cancelled)",
  "shippingAddress": "string (optional)",
  "notes": "string (optional)"
}
```

**Response (200 OK):**
```json
{
  "id": "string",
  "userId": "string",
  "products": [
    {
      "productId": "string",
      "quantity": "number",
      "price": "number"
    }
  ],
  "totalAmount": "number",
  "status": "string",
  "shippingAddress": "string",
  "notes": "string",
  "createdAt": "string (ISO 8601 timestamp)",
  "updatedAt": "string (ISO 8601 timestamp)"
}
```

**Errors:**
- `400 Bad Request` - Validation failed
- `401 Unauthorized` - Missing or invalid token
- `403 Forbidden` - Not authorized to update this order
- `404 Not Found` - Order not found
- `500 Internal Server Error` - Server error

---

## User Profile Endpoints

### 12. Get User Profile

**Endpoint:** `GET /users/profile`

**Description:** Retrieve the authenticated user's profile information.

**Response (200 OK):**
```json
{
  "id": "string",
  "username": "string",
  "email": "string",
  "role": "string (user|admin)",
  "name": "string (optional)",
  "avatar": "string (optional, URL or base64)",
  "createdAt": "string (ISO 8601 timestamp)",
  "updatedAt": "string (ISO 8601 timestamp)"
}
```

**Errors:**
- `401 Unauthorized` - Missing or invalid token
- `500 Internal Server Error` - Server error

---

### 13. Update User Profile

**Endpoint:** `PUT /users/profile`

**Description:** Update the authenticated user's profile information.

**Request Body:** (all optional)
```json
{
  "name": "string (optional)",
  "email": "string (optional, valid email)",
  "avatar": "string (optional, URL or base64)",
  "currentPassword": "string (required if changing password)",
  "newPassword": "string (optional, min 6 characters)"
}
```

**Response (200 OK):**
```json
{
  "id": "string",
  "username": "string",
  "email": "string",
  "role": "string",
  "name": "string",
  "avatar": "string",
  "createdAt": "string (ISO 8601 timestamp)",
  "updatedAt": "string (ISO 8601 timestamp)"
}
```

**Errors:**
- `400 Bad Request` - Validation failed
- `401 Unauthorized` - Missing or invalid token / incorrect current password
- `409 Conflict` - Email already in use
- `500 Internal Server Error` - Server error

---

## Shopping Cart Endpoints

### 14. Get Cart

**Endpoint:** `GET /cart`

**Description:** Retrieve all items in the authenticated user's shopping cart.

**Response (200 OK):**
```json
{
  "items": [
    {
      "id": "number",
      "userId": "number",
      "productId": "number",
      "quantity": "number",
      "product": {
        "name": "string",
        "description": "string (optional)",
        "price": "number",
        "stock": "number",
        "image": "string (optional)",
        "category": "string (optional)"
      },
      "createdAt": "string (ISO 8601 timestamp)",
      "updatedAt": "string (ISO 8601 timestamp)"
    }
  ],
  "totalAmount": "number (sum of all items: price * quantity)",
  "totalItems": "number (count of items in cart)"
}
```

**Errors:**
- `401 Unauthorized` - Missing or invalid token
- `500 Internal Server Error` - Server error

---

### 15. Add Item to Cart

**Endpoint:** `POST /cart/items`

**Description:** Add a product to the shopping cart or increase quantity if it already exists.

**Request Body:**
```json
{
  "productId": "number (required, must be valid product ID)",
  "quantity": "number (required, > 0, default: 1)"
}
```

**Response (201 Created):**
```json
{
  "id": "number",
  "userId": "number",
  "productId": "number",
  "quantity": "number",
  "product": {
    "name": "string",
    "description": "string (optional)",
    "price": "number",
    "stock": "number",
    "image": "string (optional)",
    "category": "string (optional)"
  },
  "createdAt": "string (ISO 8601 timestamp)",
  "updatedAt": "string (ISO 8601 timestamp)"
}
```

**Errors:**
- `400 Bad Request` - Validation failed (invalid product ID, quantity <= 0, insufficient stock)
- `401 Unauthorized` - Missing or invalid token
- `404 Not Found` - Product not found
- `500 Internal Server Error` - Server error

**Notes:**
- If the product already exists in the cart, the quantity will be incremented
- Stock availability is checked before adding to cart

---

### 16. Update Cart Item Quantity

**Endpoint:** `PUT /cart/items/:id`

**Description:** Update the quantity of an item in the shopping cart.

**Path Parameters:**
```
:id  - Cart item ID (number, required)
```

**Request Body:**
```json
{
  "quantity": "number (required, > 0)"
}
```

**Response (200 OK):**
```json
{
  "id": "number",
  "userId": "number",
  "productId": "number",
  "quantity": "number",
  "product": {
    "name": "string",
    "description": "string (optional)",
    "price": "number",
    "stock": "number",
    "image": "string (optional)",
    "category": "string (optional)"
  },
  "createdAt": "string (ISO 8601 timestamp)",
  "updatedAt": "string (ISO 8601 timestamp)"
}
```

**Errors:**
- `400 Bad Request` - Validation failed (invalid cart item ID, quantity <= 0, insufficient stock)
- `401 Unauthorized` - Missing or invalid token
- `404 Not Found` - Cart item not found or doesn't belong to user
- `500 Internal Server Error` - Server error

**Notes:**
- Only the owner of the cart item can update it
- Stock availability is checked before updating

---

### 17. Remove Item from Cart

**Endpoint:** `DELETE /cart/items/:id`

**Description:** Remove a specific item from the shopping cart.

**Path Parameters:**
```
:id  - Cart item ID (number, required)
```

**Response (204 No Content):**
```
(empty body)
```

**Errors:**
- `400 Bad Request` - Invalid cart item ID
- `401 Unauthorized` - Missing or invalid token
- `404 Not Found` - Cart item not found or doesn't belong to user
- `500 Internal Server Error` - Server error

**Notes:**
- Only the owner of the cart item can remove it

---

### 18. Clear Cart

**Endpoint:** `DELETE /cart`

**Description:** Remove all items from the authenticated user's shopping cart.

**Response (204 No Content):**
```
(empty body)
```

**Errors:**
- `401 Unauthorized` - Missing or invalid token
- `500 Internal Server Error` - Server error

**Notes:**
- This operation removes all cart items for the authenticated user
- Useful after order placement or when user wants to start fresh

---

## Common HTTP Status Codes

| Code  | Meaning                                                                                |
| ----- | -------------------------------------------------------------------------------------- |
| `200` | OK - Request successful                                                                |
| `201` | Created - Resource created successfully                                                |
| `204` | No Content - Successful request with no response body                                  |
| `400` | Bad Request - Invalid input or validation error                                        |
| `401` | Unauthorized - Missing or invalid authentication token                                 |
| `403` | Forbidden - Authenticated but not permitted (e.g., non-admin trying to create product) |
| `404` | Not Found - Resource does not exist                                                    |
| `409` | Conflict - Resource conflict (e.g., duplicate username, insufficient stock)            |
| `500` | Internal Server Error - Unexpected server error                                        |

---

## Standard Error Response Format

All error responses follow this format:

```json
{
  "error": "string (error message)",
  "message": "string (optional, more detailed message)",
  "status": "number (HTTP status code)",
  "data": "object (optional, additional error context)"
}
```

Example:
```json
{
  "error": "Validation failed",
  "message": "Username must be between 3 and 20 characters",
  "status": 400
}
```

---

## Authentication

### JWT Token Structure

The JWT token should contain a payload with at least:
```json
{
  "id": "string",
  "username": "string",
  "role": "string (user|admin)",
  "email": "string (optional)",
  "name": "string (optional)",
  "sub": "string (subject/user ID)",
  "exp": "number (expiration time in seconds since epoch)",
  "iat": "number (issued at time in seconds since epoch)"
}
```

The frontend automatically decodes the JWT to extract user information and stores it in the application state.

### Token Refresh

If a token expires (401 response), the app redirects to the login page. The frontend does **not** automatically refresh tokens; users must log in again.

---

## Rate Limiting (Recommended)

Consider implementing rate limiting on your backend:
- Login/Register: 5 requests per minute per IP
- General endpoints: 100 requests per minute per user

---

## CORS (Recommended)

Ensure your backend is properly configured for CORS if hosted on a different domain:
```
Access-Control-Allow-Origin: * (or specific domain)
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

---

## Notes for Backend Developers

1. **Timestamps**: Use ISO 8601 format (e.g., `2024-01-01T12:00:00Z`)
2. **Token Field Names**: The frontend accepts `token`, `access_token`, or `jwt` from response
3. **Pagination**: Default limit is 20, default offset is 0
4. **Stock Management**: When an order is created, decrement product stock; handle concurrent orders carefully
5. **User Isolation**: Ensure users can only see their own orders unless they are admins
6. **Price Snapshots**: Store the price at the time of purchase in the order, not a reference to current product price
