# Royal Garden Spa API Documentation

## Authentication API

API ini menggunakan Laravel Sanctum untuk autentikasi berbasis token.

### Base URL
```
http://localhost:8000/api
```

### Headers
Untuk endpoint yang memerlukan autentikasi, sertakan header:
```
Authorization: Bearer {your-token}
Content-Type: application/json
Accept: application/json
```

### User Roles
Sistem menggunakan 2 role:
- **customer** (default) - Role untuk pelanggan biasa
- **admin** - Role untuk administrator dengan akses penuh

Setiap user yang mendaftar secara otomatis mendapat role `customer`. Admin user dapat dibuat melalui seeder atau programmatically.

**Default Admin Credentials:**
- Email: `admin@royalgardenspa.com`
- Password: `admin123`

---

## Endpoints

### 1. Register User
**POST** `/register`

Mendaftarkan user baru.

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "08123456789",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "08123456789",
            "role": "customer",
            "created_at": "2025-10-11T02:41:18.000000Z"
        },
        "access_token": "1|randomtokenstring",
        "token_type": "Bearer"
    }
}
```

**Error Response (422):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["Email sudah terdaftar."],
        "phone": ["Nomor telepon sudah terdaftar."],
        "password": ["Konfirmasi password tidak cocok."]
    }
}
```

---

### 2. Login User
**POST** `/login`

Login user yang sudah terdaftar.

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "08123456789",
            "role": "customer"
        },
        "access_token": "2|anothertokenstring",
        "token_type": "Bearer"
    }
}
```

**Error Response (422):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

---

### 3. Get User Profile
**GET** `/profile`

Mendapatkan profil user yang sedang login.

**Headers Required:**
```
Authorization: Bearer {your-token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "08123456789",
            "role": "customer",
            "email_verified_at": null,
            "created_at": "2025-10-11T02:41:18.000000Z",
            "updated_at": "2025-10-11T02:41:18.000000Z"
        }
    }
}
```

---

### 4. Logout User
**POST** `/logout`

Logout user dari device saat ini (menghapus token saat ini).

**Headers Required:**
```
Authorization: Bearer {your-token}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

### 5. Logout from All Devices
**POST** `/logout-all`

Logout user dari semua device (menghapus semua token).

**Headers Required:**
```
Authorization: Bearer {your-token}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Logged out from all devices successfully"
}
```

---

### 6. Get User (Legacy)
**GET** `/user`

Mendapatkan data user yang sedang login (endpoint Laravel default).

**Headers Required:**
```
Authorization: Bearer {your-token}
```

**Success Response (200):**
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "08123456789",
    "email_verified_at": null,
    "created_at": "2025-10-11T02:41:18.000000Z",
    "updated_at": "2025-10-11T02:41:18.000000Z"
}
```

---

## Testing dengan Postman/Insomnia

### 1. Register
- Method: POST
- URL: `http://localhost:8000/api/register`
- Body (JSON):
```json
{
    "name": "Test User",
    "email": "test@example.com",
    "phone": "08123456789",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### 2. Login
- Method: POST
- URL: `http://localhost:8000/api/login`
- Body (JSON):
```json
{
    "email": "test@example.com",
    "password": "password123"
}
```

### 3. Access Protected Endpoints
Setelah login, copy `access_token` dari response dan gunakan di header:
- Header: `Authorization: Bearer {paste-token-here}`

---

## Error Codes

- **200**: Success
- **201**: Created (successful registration)
- **401**: Unauthorized (invalid or missing token)
- **422**: Validation Error (invalid input data)
- **500**: Server Error

---

## Security Notes

1. Token tidak memiliki expiration time (dapat dikustomisasi di config)
2. Password di-hash menggunakan bcrypt
3. Setiap login menghasilkan token baru
4. Token dapat di-revoke kapan saja
5. CORS sudah dikonfigurasi untuk development

## Setup Requirements

1. Pastikan migration sudah dijalankan:
```bash
php artisan migrate
```

2. Pastikan Laravel Sanctum sudah publish (opsional):
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

3. Start development server:
```bash
php artisan serve
```
