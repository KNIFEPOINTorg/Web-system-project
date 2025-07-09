# LFLshop API Documentation

## Overview
LFLshop provides a RESTful API for managing an Ethiopian e-commerce platform. All API endpoints return JSON responses and use standard HTTP status codes.

## Base URL
```
http://localhost/LFLshop/api/
```

## Authentication
Most endpoints require user authentication via PHP sessions. Include session cookies in requests.

## Response Format
All API responses follow this structure:

### Success Response
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": { ... },
    "timestamp": "2025-07-09T12:00:00Z"
}
```

### Error Response
```json
{
    "success": false,
    "error": {
        "message": "Error description",
        "code": 400,
        "timestamp": "2025-07-09T12:00:00Z"
    }
}
```

## Authentication API (`/auth.php`)

### Login
**POST** `/auth.php?action=login`

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "user_type": "customer"
        },
        "redirect": "customer-dashboard.html"
    }
}
```

### Register
**POST** `/auth.php?action=register`

**Request Body:**
```json
{
    "firstName": "John",
    "lastName": "Doe",
    "email": "user@example.com",
    "password": "password123",
    "userType": "customer",
    "phone": "+251911234567"
}
```

### Check Authentication
**GET** `/auth.php?action=check`

Returns current user session information.

### Logout
**POST** `/auth.php?action=logout`

Destroys the current session.

## Products API (`/products.php`)

### Get Products
**GET** `/products.php?action=list`

**Query Parameters:**
- `category` (optional): Filter by category ID
- `search` (optional): Search term
- `limit` (optional): Number of results (default: 20)
- `offset` (optional): Pagination offset

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Ethiopian Coffee Beans",
            "description": "Premium Yirgacheffe coffee",
            "price": 450.00,
            "sale_price": 400.00,
            "image": "coffee.jpg",
            "category": "Coffee",
            "seller_name": "Coffee Farm Co.",
            "location": "Yirgacheffe"
        }
    ]
}
```

### Get Single Product
**GET** `/products.php?action=single&id=1`

### Get Featured Products
**GET** `/products.php?action=featured`

### Get Categories
**GET** `/products.php?action=categories`

## Cart API (`/cart.php`)

### Get Cart
**GET** `/cart.php`

Returns current user's cart items.

### Add to Cart
**POST** `/cart.php?action=add`

**Request Body:**
```json
{
    "product_id": 1,
    "quantity": 2,
    "size": "Medium"
}
```

### Update Cart Item
**PUT** `/cart.php`

**Request Body:**
```json
{
    "item_id": 1,
    "quantity": 3
}
```

### Remove from Cart
**DELETE** `/cart.php?item_id=1`

### Clear Cart
**POST** `/cart.php?action=clear`

## Orders API (`/orders.php`)

### Get Orders
**GET** `/orders.php?action=list`

Returns user's order history.

### Get Single Order
**GET** `/orders.php?action=single&id=1`

### Create Order
**POST** `/orders.php`

**Request Body:**
```json
{
    "delivery_option": "standard",
    "shipping_address": "123 Main St, Addis Ababa",
    "payment_method": "cash_on_delivery",
    "notes": "Please call before delivery"
}
```

### Update Order Status (Seller only)
**PUT** `/orders.php`

**Request Body:**
```json
{
    "order_id": 1,
    "status": "shipped"
}
```

## Payment API (`/payment.php`)

### Process Payment
**POST** `/payment.php?action=process`

**Request Body:**
```json
{
    "order_id": 1,
    "payment_method": "card",
    "card_number": "4111111111111111",
    "expiry_month": "12",
    "expiry_year": "2025",
    "cvv": "123",
    "cardholder_name": "John Doe"
}
```

### Get Payment Status
**GET** `/payment.php?action=status&order_id=1`

## Error Codes

| Code | Description |
|------|-------------|
| 200  | Success |
| 400  | Bad Request / Validation Error |
| 401  | Unauthorized |
| 403  | Forbidden |
| 404  | Not Found |
| 429  | Rate Limited |
| 500  | Internal Server Error |

## Rate Limiting
API requests are limited to 100 requests per minute per IP address.

## Ethiopian-Specific Features

### Currency
All prices are in Ethiopian Birr (ETB). Use the CurrencyHelper JavaScript utility for formatting.

### Phone Numbers
Ethiopian phone numbers should be in format: `+251XXXXXXXXX` or `0XXXXXXXXX`

### Locations
Common Ethiopian locations are supported for delivery and seller locations.

## Security
- All API endpoints use secure CORS configuration
- Input validation and sanitization applied
- SQL injection protection via prepared statements
- XSS protection via output encoding
- CSRF protection on state-changing operations
