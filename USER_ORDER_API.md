# Customer Order API Documentation

## Overview

API endpoints for customers to create and manage their spa service orders.

## Base URL

`/api/customer`

## Authentication

All endpoints require authentication with Sanctum token and customer role.

## Endpoints

### 1. Get Available Services

**GET** `/services`

Get list of all active spa services available for ordering.

**Response:**

```json
{
    "success": true,
    "message": "Available services retrieved successfully",
    "data": [
        {
            "id": 1,
            "uuid": "123e4567-e89b-12d3-a456-426614174000",
            "name": "Deep Tissue Massage",
            "description": "Relaxing deep tissue massage",
            "price": "100.00",
            "duration": 60,
            "category": "massage"
        }
    ]
}
```

### 2. Get User Orders

**GET** `/orders`

Get all orders for the authenticated user.

**Response:**

```json
{
    "success": true,
    "message": "Orders retrieved successfully",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "spa_services_id": 1,
            "status": "pending",
            "price": "100.00",
            "time_service": "14:30",
            "date_service": "2025-10-12",
            "notes": "Please use lavender oil",
            "created_at": "2025-10-11T10:30:00.000000Z",
            "updated_at": "2025-10-11T10:30:00.000000Z",
            "spa_service": {
                "id": 1,
                "name": "Deep Tissue Massage",
                "description": "Relaxing deep tissue massage",
                "price": "100.00"
            }
        }
    ]
}
```

### 3. Create Order

**POST** `/orders`

Create a new spa service order.

**Request Body:**

```json
{
    "spa_services_id": 1,
    "time_service": "14:30",
    "date_service": "2025-10-12",
    "notes": "Please use lavender oil"
}
```

**Validation Rules:**

-   `spa_services_id`: Required, must exist in spa_services table
-   `time_service`: Required, must be in HH:MM format (24-hour)
-   `date_service`: Required, must be a valid date, cannot be in the past
-   `notes`: Optional, max 1000 characters

**Response (201):**

```json
{
    "success": true,
    "message": "Order created successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "spa_services_id": 1,
        "status": "pending",
        "price": "100.00",
        "time_service": "14:30",
        "date_service": "2025-10-12",
        "notes": "Please use lavender oil",
        "created_at": "2025-10-11T10:30:00.000000Z",
        "updated_at": "2025-10-11T10:30:00.000000Z",
        "spa_service": {
            "id": 1,
            "name": "Deep Tissue Massage"
        },
        "user": {
            "id": 1,
            "name": "John Doe"
        }
    }
}
```

### 4. Get Specific Order

**GET** `/orders/{id}`

Get details of a specific order belonging to the authenticated user.

**Response:**

```json
{
    "success": true,
    "message": "Order retrieved successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "spa_services_id": 1,
        "status": "pending",
        "price": "100.00",
        "time_service": "14:30",
        "date_service": "2025-10-12",
        "notes": "Please use lavender oil",
        "spa_service": {
            "id": 1,
            "name": "Deep Tissue Massage"
        },
        "user": {
            "id": 1,
            "name": "John Doe"
        }
    }
}
```

### 5. Update Order

**PUT** `/orders/{id}`

Update an existing order. Only allowed for orders with "pending" status.

**Request Body:**

```json
{
    "time_service": "15:30",
    "date_service": "2025-10-13",
    "notes": "Updated notes"
}
```

**Validation Rules:**

-   `time_service`: Optional, must be in HH:MM format (24-hour)
-   `date_service`: Optional, must be a valid date, cannot be in the past
-   `notes`: Optional, max 1000 characters

**Response (200):**

```json
{
    "success": true,
    "message": "Order updated successfully",
    "data": {
        // Updated order data
    }
}
```

### 6. Cancel Order

**POST** `/orders/{id}/cancel`

Cancel an existing order. Only allowed for orders with "pending" status.

**Response (200):**

```json
{
    "success": true,
    "message": "Order cancelled successfully",
    "data": {
        "id": 1,
        "status": "cancelled"
        // Other order data
    }
}
```

## Error Responses

### Validation Error (422)

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "spa_services_id": ["Selected spa service does not exist"],
        "date_service": ["Service date cannot be in the past"]
    }
}
```

### Unauthorized (401)

```json
{
    "message": "Unauthenticated"
}
```

### Not Found (404)

```json
{
    "success": false,
    "message": "Order not found"
}
```

### Bad Request (400)

```json
{
    "success": false,
    "message": "Cannot update order that is no longer pending"
}
```

### Server Error (500)

```json
{
    "success": false,
    "message": "Failed to create order",
    "error": "Error details"
}
```

## Status Values

-   `pending`: Order is created and waiting for confirmation
-   `confirmed`: Order has been confirmed by admin
-   `in_progress`: Service is currently being provided
-   `completed`: Service has been completed
-   `cancelled`: Order has been cancelled

## Notes

-   Users can only view, update, and cancel their own orders
-   Orders can only be updated or cancelled when status is "pending"
-   The price is automatically set from the spa service price when creating an order
-   All dates should be in YYYY-MM-DD format
-   All times should be in HH:MM format (24-hour)
