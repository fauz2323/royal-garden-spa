# Admin Spa Service API Documentation

## Admin Spa Service Management

API ini memungkinkan admin untuk mengelola layanan spa (CRUD operations). Semua endpoint memerlukan autentikasi dan role admin.

### Base URL
```
http://localhost:8000/api/admin
```

### Headers Required
```
Authorization: Bearer {admin-token}
Content-Type: application/json
Accept: application/json
```

---

## Endpoints

### 1. Get All Spa Services
**GET** `/spa-services`

Mendapatkan semua layanan spa.

**Success Response (200):**
```json
{
    "success": true,
    "message": "Spa services retrieved successfully",
    "data": {
        "services": [
            {
                "id": 1,
                "uuid": "12345678-1234-1234-1234-123456789012",
                "name": "Traditional Balinese Massage",
                "description": "Relaksasi tubuh dengan pijat tradisional Bali...",
                "price": "150000.00",
                "duration": 90,
                "image": null,
                "points": "0",
                "is_active": true,
                "created_at": "2025-10-11T03:20:00.000000Z",
                "updated_at": "2025-10-11T03:20:00.000000Z"
            }
        ]
    }
}
```

---

### 2. Create Spa Service
**POST** `/spa-services`

Membuat layanan spa baru.

**Request Body:**
```json
{
    "name": "New Spa Service",
    "description": "Detailed description of the spa service",
    "price": 150000,
    "duration": 90,
    "is_active": true
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `description`: required, string
- `price`: required, numeric, minimum 0
- `duration`: required, integer, minimum 1 (in minutes)
- `is_active`: optional, boolean (default: true)

**Success Response (201):**
```json
{
    "success": true,
    "message": "Spa service created successfully",
    "data": {
        "service": {
            "id": 9,
            "uuid": "87654321-4321-4321-4321-210987654321",
            "name": "New Spa Service",
            "description": "Detailed description of the spa service",
            "price": "150000.00",
            "duration": 90,
            "is_active": true,
            "created_at": "2025-10-11T03:25:00.000000Z",
            "updated_at": "2025-10-11T03:25:00.000000Z"
        }
    }
}
```

**Error Response (422):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": ["Nama layanan wajib diisi."],
        "price": ["Harga layanan wajib diisi."],
        "duration": ["Durasi layanan wajib diisi."]
    }
}
```

---

### 3. Get Single Spa Service
**GET** `/spa-services/{id}`

Mendapatkan detail layanan spa berdasarkan ID.

**Success Response (200):**
```json
{
    "success": true,
    "message": "Spa service retrieved successfully",
    "data": {
        "service": {
            "id": 1,
            "uuid": "12345678-1234-1234-1234-123456789012",
            "name": "Traditional Balinese Massage",
            "description": "Relaksasi tubuh dengan pijat tradisional Bali...",
            "price": "150000.00",
            "duration": 90,
            "image": null,
            "points": "0",
            "is_active": true,
            "created_at": "2025-10-11T03:20:00.000000Z",
            "updated_at": "2025-10-11T03:20:00.000000Z"
        }
    }
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Spa service not found"
}
```

---

### 4. Update Spa Service
**PUT** `/spa-services/{id}`

Memperbarui layanan spa yang sudah ada.

**Request Body (Partial Update Supported):**
```json
{
    "name": "Updated Service Name",
    "price": 200000,
    "is_active": false
}
```

**Validation Rules (all optional):**
- `name`: sometimes required, string, max 255 characters
- `description`: sometimes required, string
- `price`: sometimes required, numeric, minimum 0
- `duration`: sometimes required, integer, minimum 1
- `is_active`: sometimes required, boolean

**Success Response (200):**
```json
{
    "success": true,
    "message": "Spa service updated successfully",
    "data": {
        "service": {
            "id": 1,
            "uuid": "12345678-1234-1234-1234-123456789012",
            "name": "Updated Service Name",
            "description": "Relaksasi tubuh dengan pijat tradisional Bali...",
            "price": "200000.00",
            "duration": 90,
            "is_active": false,
            "updated_at": "2025-10-11T03:30:00.000000Z"
        }
    }
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Spa service not found"
}
```

---

### 5. Delete Spa Service
**DELETE** `/spa-services/{id}`

Menghapus layanan spa.

**Success Response (200):**
```json
{
    "success": true,
    "message": "Spa service deleted successfully"
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Spa service not found"
}
```

---

### 6. Toggle Service Status
**POST** `/spa-services/{id}/toggle-status`

Mengubah status aktif/non-aktif layanan spa.

**Success Response (200):**
```json
{
    "success": true,
    "message": "Service status updated successfully",
    "data": {
        "service": {
            "id": 1,
            "name": "Traditional Balinese Massage",
            "is_active": false,
            "updated_at": "2025-10-11T03:35:00.000000Z"
        }
    }
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Spa service not found"
}
```

---

## Authorization

Semua endpoint memerlukan:
1. **Authentication**: User harus login dengan Bearer token
2. **Authorization**: User harus memiliki role 'admin'

**Error Responses:**

**Unauthenticated (401):**
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

**Insufficient Permissions (403):**
```json
{
    "success": false,
    "message": "Insufficient permissions. Required role: admin"
}
```

---

## Model Properties

### SpaService Model
- `id`: Primary key
- `uuid`: Unique identifier (auto-generated)
- `name`: Service name (string, max 255)
- `description`: Service description (text)
- `price`: Service price (decimal, 2 places)
- `duration`: Service duration in minutes (integer)
- `image`: Service image path (nullable)
- `points`: Points earned (string, default "0")
- `is_active`: Service status (boolean, default true)
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

### Helper Methods
- `getFormattedPriceAttribute()`: Returns formatted price (e.g., "Rp 150.000")
- `getFormattedDurationAttribute()`: Returns formatted duration (e.g., "1h 30m")
- `scopeActive()`: Query scope for active services only

---

## Sample Data

The seeder creates 8 sample spa services including:
- Traditional Balinese Massage (Rp 150.000, 90 minutes)
- Hot Stone Therapy (Rp 200.000, 120 minutes)
- Aromatherapy Facial (Rp 125.000, 75 minutes)
- Deep Tissue Massage (Rp 175.000, 90 minutes)
- Reflexology (Rp 100.000, 60 minutes)
- Body Scrub & Wrap (Rp 180.000, 105 minutes)
- Couple Massage Package (Rp 350.000, 120 minutes)
- Prenatal Massage (Rp 160.000, 75 minutes) - inactive
