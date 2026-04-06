# UIShop API Endpoints - Quick Reference

## Base URL
`/api` (configurable in `js/api.js`)

## Authentication
- All endpoints require `Authorization: Bearer {token}` header (except `/auth/login` and `/auth/register`)
- Token is a JWT containing: `id`, `username`, `role`, `email`, `exp`, `iat`
- Frontend accepts token field names: `token`, `access_token`, or `jwt`

---

## Auth Endpoints

### POST /auth/login
**Request:** `{ username, password }`
**Response:** `{ token, user: { id, username, email, role, name } }`
**Errors:** 401 (invalid credentials), 400 (missing fields)

### POST /auth/register
**Request:** `{ username, password, email? }`
**Response:** `{ token, user: { id, username, email, role } }`
**Errors:** 400 (validation), 409 (username/email exists)

---

## Products Endpoints

### GET /products
**Query:** `?search=keyword&limit=20&offset=0`
**Response:** `{ products: [], total, limit, offset }`
**Errors:** 401 (auth required)

### POST /products (Admin Only)
**Request:** `{ name, description?, price, stock, image?, category? }`
**Response:** `{ id, name, description, price, stock, image, category, createdBy, createdAt, updatedAt }`
**Errors:** 400 (validation), 401 (auth), 403 (not admin)

### PUT /products/:id (Admin Only)
**Request:** `{ name?, description?, price?, stock?, image?, category? }`
**Response:** Product object
**Errors:** 400 (validation), 401 (auth), 403 (not admin), 404 (not found)

### DELETE /products/:id (Admin Only)
**Response:** 204 No Content
**Errors:** 401 (auth), 403 (not admin), 404 (not found)

---

## Orders Endpoints

### GET /orders
**Query:** `?status=pending&limit=20&offset=0`
**Response:** `{ orders: [], total, limit, offset }`
**Notes:** Admins see all orders, users see only their own
**Errors:** 401 (auth required)

### GET /orders/my
**Query:** `?status=pending&limit=20&offset=0`
**Response:** `{ orders: [], total, limit, offset }`
**Notes:** Always returns only authenticated user's orders
**Errors:** 401 (auth required)

### GET /orders/:id
**Response:** `{ id, userId, products: [{productId, quantity, price}], totalAmount, status, shippingAddress, notes, createdAt, updatedAt }`
**Errors:** 401 (auth), 403 (not authorized), 404 (not found)

### POST /orders
**Request:** `{ products: [{productId, quantity}], shippingAddress?, notes? }`
**Response:** Order object
**Errors:** 400 (validation), 401 (auth), 404 (product not found), 409 (insufficient stock)

### PUT /orders/:id
**Request:** `{ status?, shippingAddress?, notes? }` (admin can change status, users cannot)
**Response:** Order object
**Errors:** 400 (validation), 401 (auth), 403 (not authorized), 404 (not found)

---

## User Profile Endpoints

### GET /users/profile
**Response:** `{ id, username, email, role, name, avatar, createdAt, updatedAt }`
**Errors:** 401 (auth required)

### PUT /users/profile
**Request:** `{ name?, email?, avatar?, currentPassword?, newPassword? }`
**Response:** Updated user object
**Errors:** 400 (validation), 401 (auth, invalid password), 409 (email taken)

---

## Status Codes
- `200` OK / `201` Created / `204` No Content
- `400` Bad Request (validation error)
- `401` Unauthorized (missing/invalid token)
- `403` Forbidden (permission denied)
- `404` Not Found
- `409` Conflict (duplicate, insufficient stock)
- `500` Internal Server Error

---

## Error Response Format
```json
{
  "error": "string",
  "message": "string",
  "status": "number"
}
```

---

## Key Business Rules
- Products: `price > 0`, `stock >= 0`
- Orders: Prices are snapshots at purchase time, stock decrements on order creation
- Users: Cannot see other users' orders unless admin
- Auth: No automatic token refresh on 401 (must re-login)
- Pagination: Default limit 20, default offset 0
