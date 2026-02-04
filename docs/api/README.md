# Sasampa POS Mobile API Documentation

## Base URL
```
Production: https://your-domain.com/api/v1
```

## Authentication

The API uses Laravel Sanctum for token-based authentication.

### Headers Required
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
X-Device-ID: {device_identifier}  (required for POS endpoints)
X-App-Version: {app_version}      (optional, for tracking)
```

---

## Authentication Endpoints

### Login with Email/Password
```
POST /api/v1/auth/login
```

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "device_name": "iPhone 15 Pro"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "company_owner",
    "has_pin": true,
    "company": {
      "id": 1,
      "name": "My Business",
      "logo": "https://...",
      "status": "approved",
      "branches_enabled": true
    },
    "current_branch": {
      "id": 1,
      "name": "Main Branch",
      "code": "HQ"
    },
    "permissions": ["*"]
  },
  "token": "1|abc123...",
  "token_type": "Bearer",
  "mobile_access": {
    "status": "approved",
    "can_use_mobile": true,
    "requested_at": "2024-01-15T10:00:00Z",
    "approved_at": "2024-01-16T14:30:00Z"
  }
}
```

### Login with PIN (Quick Access)
```
POST /api/v1/auth/login/pin
```

**Request:**
```json
{
  "email": "user@example.com",
  "pin": "1234",
  "device_name": "iPhone 15 Pro"
}
```

### Get Current User
```
GET /api/v1/auth/user
```

### Logout
```
POST /api/v1/auth/logout
```

### Logout All Devices
```
POST /api/v1/auth/logout-all
```

### Set/Update PIN
```
POST /api/v1/auth/pin
```

**Request:**
```json
{
  "pin": "1234",
  "current_password": "password123"
}
```

### Change Password
```
POST /api/v1/auth/password
```

**Request:**
```json
{
  "current_password": "oldpassword",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

---

## Mobile Access Endpoints

### Request Mobile Access (Company Owner Only)
```
POST /api/v1/mobile-access/request
```

**Request:**
```json
{
  "request_reason": "We need mobile access for our 3 cashiers to process sales on the floor",
  "expected_devices": 3
}
```

### Check Mobile Access Status
```
GET /api/v1/mobile-access/status
```

**Response:**
```json
{
  "request": {
    "id": 1,
    "status": "pending|approved|rejected|revoked",
    "request_reason": "...",
    "expected_devices": 3,
    "created_at": "2024-01-15T10:00:00Z",
    "approved_at": null,
    "rejection_reason": null
  },
  "can_use_mobile": false,
  "registered_devices": 0
}
```

### Register Device (After Approval)
```
POST /api/v1/mobile-access/register-device
```

**Request:**
```json
{
  "device_identifier": "unique-device-uuid",
  "device_name": "John's iPhone",
  "device_model": "iPhone 15 Pro",
  "os_version": "iOS 17.2",
  "app_version": "1.0.0",
  "push_token": "fcm-token-here"
}
```

### Update Push Token
```
PATCH /api/v1/mobile-access/device/push-token
```

**Request:**
```json
{
  "device_identifier": "unique-device-uuid",
  "push_token": "new-fcm-token"
}
```

### List My Devices
```
GET /api/v1/mobile-access/devices
```

### Deactivate Device
```
DELETE /api/v1/mobile-access/devices/{device_identifier}
```

---

## POS Endpoints (Require Mobile Access Approved + Device Registered)

### Get Products
```
GET /api/v1/pos/products
```

**Query Parameters:**
- `search` - Search by name, SKU, or barcode
- `category_id` - Filter by category
- `barcode` - Exact barcode match
- `per_page` - Pagination (default: 50, max: 100)

### Get Single Product
```
GET /api/v1/pos/products/{id_or_barcode}
```

### Scan Barcode
```
GET /api/v1/pos/products/scan/{barcode}
```

### Get Low Stock Products
```
GET /api/v1/pos/products/low-stock
```

### Get Categories
```
GET /api/v1/pos/categories
```

### Process Checkout
```
POST /api/v1/pos/checkout
```

**Request:**
```json
{
  "items": [
    {"product_id": 1, "quantity": 2},
    {"product_id": 5, "quantity": 1}
  ],
  "payment_method": "cash|card|mobile|bank_transfer",
  "amount_paid": 50000,
  "customer_name": "Jane Doe",
  "customer_phone": "+255123456789",
  "customer_tin": "123-456-789",
  "discount_amount": 1000,
  "notes": "Regular customer",
  "offline_id": "local-uuid-123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Sale completed successfully.",
  "data": {
    "id": 42,
    "transaction_number": "TXN-20240115-0042",
    "status": "completed",
    "subtotal": 48000,
    "tax_amount": 8640,
    "discount_amount": 1000,
    "total": 55640,
    "payment_method": "cash",
    "amount_paid": 60000,
    "change_given": 4360,
    "items": [...],
    "created_at": "2024-01-15T14:30:00Z"
  },
  "offline_id": "local-uuid-123"
}
```

### Void Transaction
```
POST /api/v1/pos/transactions/{id}/void
```

**Request:**
```json
{
  "reason": "Customer returned items"
}
```

### Get Receipt Data
```
GET /api/v1/pos/transactions/{id}/receipt
```

---

## Transaction Endpoints

### List Transactions
```
GET /api/v1/pos/transactions
```

**Query Parameters:**
- `status` - Filter by status (completed, voided)
- `payment_method` - Filter by payment method
- `date_from`, `date_to` - Date range
- `branch_id` - Filter by branch
- `user_id` - Filter by cashier
- `search` - Search transaction number or customer
- `per_page` - Pagination

### Get Single Transaction
```
GET /api/v1/pos/transactions/{id}
```

### Today's Transactions
```
GET /api/v1/pos/transactions/today
```

### My Transactions
```
GET /api/v1/pos/transactions/mine
```

---

## Inventory Endpoints

### Get Inventory List
```
GET /api/v1/inventory
```

**Query Parameters:**
- `low_stock` - Filter to show only low stock items (true/false)
- `out_of_stock` - Filter to show only out of stock items (true/false)
- `category_id` - Filter by category
- `search` - Search by name, SKU, barcode

### Get Inventory Summary
```
GET /api/v1/inventory/summary
```

**Response:**
```json
{
  "data": {
    "total_products": 150,
    "low_stock_count": 12,
    "out_of_stock_count": 3,
    "total_stock_value": 5000000,
    "total_retail_value": 7500000,
    "potential_profit": 2500000
  }
}
```

### Adjust Stock
```
POST /api/v1/inventory/{product_id}/adjust
```

**Request:**
```json
{
  "type": "received|damaged|returned|adjustment",
  "quantity": 10,
  "reason": "New stock delivery"
}
```

### Get Stock History
```
GET /api/v1/inventory/{product_id}/history
```

---

## Report Endpoints

### Dashboard Summary
```
GET /api/v1/reports/dashboard
```

**Response:**
```json
{
  "data": {
    "today": {
      "sales_total": 250000,
      "transactions_count": 15,
      "average_sale": 16666.67,
      "items_sold": 45
    },
    "this_month": {
      "sales_total": 5000000,
      "transactions_count": 320,
      "average_sale": 15625
    },
    "alerts": {
      "low_stock_count": 8
    },
    "payment_breakdown_today": {
      "cash": 150000,
      "card": 50000,
      "mobile": 40000,
      "bank_transfer": 10000
    },
    "top_products_today": [...],
    "recent_transactions": [...]
  }
}
```

### Sales Report
```
GET /api/v1/reports/sales
```

**Query Parameters:**
- `period` - today|week|month|custom
- `date_from`, `date_to` - Required when period=custom

---

## Sync Endpoints (Offline Support)

### Pull Data Changes
```
GET /api/v1/sync/pull
```

**Query Parameters:**
- `since` - ISO 8601 timestamp of last sync (optional, omit for full sync)
- `include[]` - What to include: products, categories, inventory

**Response:**
```json
{
  "data": {
    "products": [...],
    "categories": [...],
    "deleted_products": [5, 12, 23]
  },
  "meta": {
    "synced_at": "2024-01-15T14:30:00Z",
    "since": "2024-01-15T10:00:00Z",
    "is_full_sync": false
  }
}
```

### Push Offline Transactions
```
POST /api/v1/sync/push
```

**Request:**
```json
{
  "transactions": [
    {
      "offline_id": "local-uuid-1",
      "items": [{"product_id": 1, "quantity": 2}],
      "payment_method": "cash",
      "amount_paid": 50000,
      "created_at": "2024-01-15T10:30:00Z"
    },
    {
      "offline_id": "local-uuid-2",
      "items": [{"product_id": 3, "quantity": 1}],
      "payment_method": "mobile",
      "amount_paid": 15000,
      "created_at": "2024-01-15T10:35:00Z"
    }
  ]
}
```

**Response:**
```json
{
  "data": {
    "results": [
      {
        "offline_id": "local-uuid-1",
        "success": true,
        "server_id": 42,
        "transaction_number": "TXN-20240115-0042"
      },
      {
        "offline_id": "local-uuid-2",
        "success": true,
        "server_id": 43,
        "transaction_number": "TXN-20240115-0043"
      }
    ],
    "synced_count": 2,
    "failed_count": 0
  },
  "meta": {
    "synced_at": "2024-01-15T14:30:00Z"
  }
}
```

### Check Sync Status
```
GET /api/v1/sync/status
```

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Authentication Error (401)
```json
{
  "message": "Unauthenticated."
}
```

### Mobile Access Errors (403)
```json
{
  "message": "Mobile access has not been requested for your company.",
  "error_code": "mobile_access_not_requested"
}
```

Possible error codes:
- `no_company` - User has no company associated
- `company_not_approved` - Company is pending approval
- `mobile_access_not_requested` - Need to request mobile access
- `mobile_access_pending` - Request is pending admin approval
- `mobile_access_rejected` - Request was rejected
- `mobile_access_revoked` - Access was revoked
- `device_id_missing` - X-Device-ID header not provided
- `device_not_registered` - Device needs to be registered
- `device_deactivated` - Device has been deactivated

### Not Found (404)
```json
{
  "message": "Product not found."
}
```

### Business Logic Error (422)
```json
{
  "success": false,
  "message": "Insufficient stock for Product Name. Available: 5"
}
```

---

## Health Check
```
GET /api/health
```

**Response:**
```json
{
  "status": "ok",
  "timestamp": "2024-01-15T14:30:00Z",
  "version": "v1"
}
```

---

## Rate Limiting

API endpoints are rate limited to 60 requests per minute per user.

When rate limited, you'll receive a 429 response:
```json
{
  "message": "Too Many Attempts."
}
```

Headers included:
- `X-RateLimit-Limit: 60`
- `X-RateLimit-Remaining: 0`
- `Retry-After: 30`

---

## Mobile Access Flow

1. **User logs in** with email/password
2. **Check mobile access status** - `GET /mobile-access/status`
3. If `can_use_mobile: false`:
   - Company owner can request access: `POST /mobile-access/request`
   - Wait for admin approval (poll status or use push notifications)
4. After approval:
   - **Register device**: `POST /mobile-access/register-device`
5. Now user can access all POS endpoints

---

## Offline Mode Implementation

### Recommended Strategy

1. **On app launch / login:**
   - Call `GET /sync/pull` without `since` parameter for full data
   - Store products, categories locally (SQLite/Drift)
   - Save `synced_at` timestamp

2. **Periodic sync (when online):**
   - Call `GET /sync/pull?since={last_synced_at}`
   - Update local database with changes
   - Handle deleted items

3. **Processing offline sales:**
   - Save transaction locally with `offline_id` (UUID)
   - Update local inventory counts
   - Mark as "pending sync"

4. **When back online:**
   - Call `POST /sync/push` with queued transactions
   - Match `offline_id` in response to mark as synced
   - Update local records with server IDs

5. **Conflict handling:**
   - Server wins for products/categories
   - Offline transactions get server IDs after sync
   - Stock levels may need reconciliation
