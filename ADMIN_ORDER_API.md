# Admin Order Management API Documentation

## Overview

API endpoints for administrators to manage all user orders in the spa system.

## Base URL

`/api/admin`

## Authentication

All endpoints require authentication with Sanctum token and admin role.

## Endpoints

### 1. Get All Orders (with filtering and pagination)

**GET** `/orders`

Get list of all orders with optional filtering and pagination.

**Query Parameters:**

-   `status` (optional): Filter by order status (pending, confirmed, in_progress, completed, cancelled, rejected)
-   `date_from` (optional): Filter orders from this date (YYYY-MM-DD)
-   `date_to` (optional): Filter orders until this date (YYYY-MM-DD)
-   `user_id` (optional): Filter orders by specific user
-   `per_page` (optional): Number of items per page (default: 15)

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
            "notes": "Customer notes here",
            "created_at": "2025-10-11T10:30:00.000000Z",
            "updated_at": "2025-10-11T10:30:00.000000Z",
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "spa_service": {
                "id": 1,
                "name": "Deep Tissue Massage",
                "description": "Relaxing massage",
                "price": "100.00"
            }
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 5,
        "per_page": 15,
        "total": 67,
        "from": 1,
        "to": 15
    }
}
```

### 2. Create Order (Admin can create for any user)

**POST** `/orders`

Create a new order for any user.

**Request Body:**

```json
{
    "user_id": 1,
    "spa_services_id": 1,
    "time_service": "14:30",
    "date_service": "2025-10-12",
    "notes": "Admin created this order",
    "status": "confirmed"
}
```

**Validation Rules:**

-   `user_id`: Required, must exist in users table
-   `spa_services_id`: Required, must exist in spa_services table
-   `time_service`: Required, must be in HH:MM format
-   `date_service`: Required, must be today or future date
-   `notes`: Optional, max 1000 characters
-   `status`: Optional, must be one of: pending, confirmed, in_progress, completed, cancelled, rejected

**Response (201):**

```json
{
    "success": true,
    "message": "Order created successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "spa_services_id": 1,
        "status": "confirmed",
        "price": "100.00",
        "time_service": "14:30",
        "date_service": "2025-10-12",
        "notes": "Admin created this order",
        "spa_service": {...},
        "user": {...}
    }
}
```

### 3. Get Specific Order

**GET** `/orders/{id}`

Get details of a specific order.

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
        "notes": "Customer notes",
        "user": {...},
        "spa_service": {...}
    }
}
```

### 4. Update Order

**PUT** `/orders/{id}`

Update any field of an existing order.

**Request Body:**

```json
{
    "user_id": 2,
    "spa_services_id": 2,
    "time_service": "15:30",
    "date_service": "2025-10-13",
    "notes": "Updated notes",
    "status": "confirmed"
}
```

**Validation Rules:**

-   All fields are optional (use `sometimes` validation)
-   Same rules as create endpoint

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

### 5. Delete Order

**DELETE** `/orders/{id}`

Permanently delete an order.

**Response (200):**

```json
{
    "success": true,
    "message": "Order deleted successfully"
}
```

### 6. Accept/Confirm Order

**POST** `/orders/{id}/accept`

Accept a pending order and change status to confirmed.

**Requirements:**

-   Order must have status "pending"

**Response (200):**

```json
{
    "success": true,
    "message": "Order accepted successfully",
    "data": {
        "id": 1,
        "status": "confirmed"
        // Other order data
    }
}
```

### 7. Reject Order

**POST** `/orders/{id}/reject`

Reject a pending order with optional reason.

**Request Body:**

```json
{
    "rejection_reason": "Service unavailable on requested date"
}
```

**Requirements:**

-   Order must have status "pending"
-   `rejection_reason` is optional but recommended

**Response (200):**

```json
{
    "success": true,
    "message": "Order rejected successfully",
    "data": {
        "id": 1,
        "status": "rejected",
        "notes": "Original notes\n\nRejection reason: Service unavailable on requested date"
    }
}
```

### 8. Start Service

**POST** `/orders/{id}/start`

Mark a confirmed order as in progress.

**Requirements:**

-   Order must have status "confirmed"

**Response (200):**

```json
{
    "success": true,
    "message": "Service started successfully",
    "data": {
        "id": 1,
        "status": "in_progress"
        // Other order data
    }
}
```

### 9. Complete Order

**POST** `/orders/{id}/complete`

Mark an order as completed.

**Requirements:**

-   Order must have status "confirmed" or "in_progress"

**Response (200):**

```json
{
    "success": true,
    "message": "Order completed successfully",
    "data": {
        "id": 1,
        "status": "completed"
        // Other order data
    }
}
```

### 10. Get Statistics

**GET** `/orders-statistics`

Get comprehensive statistics about orders for dashboard.

**Response:**

```json
{
    "success": true,
    "message": "Statistics retrieved successfully",
    "data": {
        "total_orders": 150,
        "pending_orders": 12,
        "confirmed_orders": 8,
        "in_progress_orders": 3,
        "completed_orders": 120,
        "cancelled_orders": 5,
        "rejected_orders": 2,
        "today_orders": 5,
        "this_week_orders": 25,
        "this_month_orders": 87,
        "total_revenue": 15000.0,
        "this_month_revenue": 8700.0
    }
}
```

## Status Flow

```
pending → confirmed → in_progress → completed
   ↓           ↓
rejected   cancelled
```

**Status Descriptions:**

-   `pending`: Order created, waiting for admin action
-   `confirmed`: Order accepted by admin
-   `in_progress`: Service is currently being provided
-   `completed`: Service finished successfully
-   `cancelled`: Order cancelled (by customer or admin)
-   `rejected`: Order rejected by admin

## Error Responses

### Validation Error (422)

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "user_id": ["Selected user does not exist"],
        "time_service": ["Service time must be in HH:MM format"]
    }
}
```

### Unauthorized (403)

```json
{
    "message": "This action is unauthorized"
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
    "message": "Only pending orders can be accepted"
}
```

## Key Features

✅ **Full CRUD Operations** - Create, read, update, delete orders  
✅ **Status Management** - Accept, reject, start, complete orders  
✅ **Advanced Filtering** - Filter by status, date range, user  
✅ **Pagination** - Handle large datasets efficiently  
✅ **Statistics Dashboard** - Comprehensive business metrics  
✅ **Automatic Pricing** - Price updated when service changes  
✅ **Status Validation** - Proper status flow enforcement  
✅ **Audit Trail** - Rejection reasons tracked in notes  
✅ **Relationship Loading** - Full user and service details  
✅ **Error Handling** - Comprehensive error responses

## Business Rules

1. **Admin Privileges**: Admins can manage all orders regardless of ownership
2. **Status Restrictions**: Certain actions only work on specific statuses
3. **Automatic Pricing**: Price is set from service when creating/updating
4. **Service Validation**: Only active services can be assigned to orders
5. **Date Validation**: Service dates cannot be in the past
6. **Rejection Tracking**: Rejection reasons are appended to order notes
