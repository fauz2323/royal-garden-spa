# Role System Documentation

## Overview
Sistem role telah ditambahkan ke aplikasi Royal Garden Spa untuk mengontrol akses dan hak istimewa pengguna.

## Available Roles
1. **customer** (default) - Role untuk pelanggan biasa
2. **admin** - Role untuk administrator dengan akses penuh

## Implementation Details

### Database Schema
- Kolom `role` ditambahkan ke tabel `users` dengan tipe `ENUM('admin', 'customer')`
- Default value: `customer`

### Model Changes
- `role` ditambahkan ke `$fillable` array di model `User`
- Helper methods ditambahkan:
  - `isAdmin()` - Check if user is admin
  - `isCustomer()` - Check if user is customer

### API Responses
Semua response API yang melibatkan user data sekarang menyertakan field `role`:

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
      "created_at": "2025-10-11T02:53:00.000000Z",
      "updated_at": "2025-10-11T02:53:00.000000Z"
    }
  }
}
```

### Default Admin User
Sebuah admin user default dibuat melalui seeder:
- Email: `admin@royalgardenspa.com`
- Password: `admin123`
- Role: `admin`

### Middleware
`RoleMiddleware` dibuat untuk authorization berdasarkan role:

```php
// Contoh penggunaan di routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Admin only routes
});
```

### Factory Updates
- User factory sekarang membuat users dengan role `customer` secara default
- Method `admin()` ditambahkan untuk membuat admin users:

```php
User::factory()->admin()->create(); // Creates admin user
User::factory()->create(); // Creates customer user
```

## Usage Examples

### Creating Users
```php
// Register via API - automatically gets 'customer' role
POST /api/register

// Create admin programmatically
User::factory()->admin()->create([
    'email' => 'admin@example.com'
]);
```

### Checking Roles
```php
$user = auth()->user();

if ($user->isAdmin()) {
    // Admin specific logic
}

if ($user->isCustomer()) {
    // Customer specific logic
}
```

### Protecting Routes
```php
// Admin only routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
});

// Customer routes
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']);
});
```

## Testing
Semua test telah diperbarui untuk menyertakan role validation:
- Registration tests memverifikasi role default 'customer'
- Response structure tests menyertakan field 'role'
- Role-specific helper method tests
- Factory tests untuk admin dan customer users

## Migration Commands
```bash
# Migrate the role column
php artisan migrate

# Seed admin user
php artisan db:seed --class=AdminUserSeeder
```
