# Employee API Documentation
---
This section describes the available API endpoints related with Employees, including request headers, body parameters, and possible responses.
---

#### Base URL
```
http://your-domain.com/api
```
> The following endpoints assumes you are using the above base url.
---

### 1. Register Employee

**Endpoint:**

```
POST /employees/register
```

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Content-Type   | application/json   |
| Accept         | application/json   |

**Body Parameters:**

| Field         | Type     | Required | Description                                                |
|---------------|----------|----------|------------------------------------------------------------|
| employee_code | ulid     | Yes      | unique code given by the service provider for the employee |
| first_name    | string   | Yes      | Employee's first name                                      |
| last_name     | string   | Yes      | Employee's last name                                       |
| email         | string   | Yes      | Unique email address                                       |
| password      | string   | Yes      | Plain password (will be hashed)                            |
| image_url     | url      | No       | URL to employee's profile picture                             |

**Example Request:**

```json
{
  "fist_name": "<first_name>",
  "last_name": "<first_name>",
  "employee_code": "<ulid>",
  "email": "service@example.com",
  "password": "securepassword",
  "image_url": "<logo_url>"
}
```

**Success Response (201):**

```json
{
  "message": "registration completed successfully",
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 422    | `{ "message": "Validation failed", "errors": { "email": ["The email field must be a valid email address."] } }` |
| 409    | `{ "error": "the email has already been taken" }` |
| 404    | `{ "error": "employee not found" }` |

---

### 2. Login Employee

**Endpoint:**

```
POST /employees/login
```

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Content-Type   | application/json   |
| Accept         | application/json   |

**Body Parameters:**

| Field    | Type   | Required | Description                       |
|----------|--------|----------|-----------------------------------|
| email    | string | Yes      | Registered employee email |
| password | string | Yes      | Plain password                    |

**Example Request:**

```json
{
  "email": "employee@example.com",
  "password": "securepassword"
}
```

**Success Response (200):**

```json
{
  "message": "login successful",
  "token": "1|abcdefghijklmopqrstuvwxyz"
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 401    | `{ "error": "invalid credentials" }` |
| 500    | `{ "error": "internal server error" }` |

---

### 3. Verify Employee Email

**Endpoint:**

```
POST /employees/verify-email/?token=<token>
```

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |

**Success Response (200):**

```json
{
  "message": "email verified successfully",
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 400    | `{ "error": "invalid token" }` |
| 400    | `{ "error": "token expired" }` |

---

### 4. Logout Employee

**Endpoint:**

```
POST /employees/logout
```

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer {token}    |

**Success Response (200):**

```json
{
  "message": "logout successful"
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 401    | `{ "error": "Unauthenticated" }` |
| 500    | `{ "error": "Internal server error" }` |

---

### 5. Get Employee Profile

**Endpoint:**

```
GET /employee/profile
```

**Controller:** `EmployeeDataController@getProfile`

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer {token}    |

**Success Response (200):**

```json
{
  "message": "Profile retrieved successfully",
  "data": {
    "id": "01HXYZ1234567890ABCDEF",
    "tip_code": "EMP001",
    "is_active": true,
    "is_verified": true,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "image_url": "https://example.com/profile.jpg",
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z"
  }
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 401    | `{ "error": "Unauthenticated" }` |
| 404    | `{ "error": "Profile not found" }` |
| 500    | `{ "error": "Internal server error" }` |

---

### 6. Update Employee Profile

**Endpoint:**

```
PUT /employee/profile
```

**Controller:** `EmployeeDataController@updateProfile`

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Content-Type   | application/json   |
| Accept         | application/json   |
| Authorization  | Bearer {token}    |

**Body Parameters:**

| Field      | Type   | Required | Description                    |
|------------|--------|----------|--------------------------------|
| first_name | string | No       | Employee's first name          |
| last_name  | string | No       | Employee's last name           |
| image_url  | url    | No       | URL to employee's profile picture |

**Example Request:**

```json
{
  "first_name": "Jane",
  "last_name": "Smith",
  "image_url": "https://example.com/new-profile.jpg"
}
```

**Success Response (200):**

```json
{
  "message": "Profile updated successfully",
  "data": {
    "first_name": "Jane",
    "last_name": "Smith",
    "image_url": "https://example.com/new-profile.jpg",
    "updated_at": "2025-01-15T11:00:00.000000Z"
  }
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 400    | `{ "error": "No fields to update" }` |
| 401    | `{ "error": "Unauthenticated" }` |
| 422    | `{ "message": "Validation failed", "errors": { "image_url": ["The image url field must be a valid URL."] } }` |
| 500    | `{ "error": "Internal server error" }` |

---

### 7. Change Employee Password

**Endpoint:**

```
PUT /employee/password
```

**Controller:** `EmployeeDataController@changePassword`

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Content-Type   | application/json   |
| Accept         | application/json   |
| Authorization  | Bearer {token}    |

**Body Parameters:**

| Field            | Type   | Required | Description                    |
|------------------|--------|----------|--------------------------------|
| current_password | string | Yes      | Current password               |
| new_password     | string | Yes      | New password (min 8 chars)    |
| confirm_password | string | Yes      | Confirm new password           |

**Example Request:**

```json
{
  "current_password": "oldpassword123",
  "new_password": "newpassword456",
  "confirm_password": "newpassword456"
}
```

**Success Response (200):**

```json
{
  "message": "Password changed successfully"
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 400    | `{ "message": "Validation failed", "errors": { "confirm_password": ["The confirm password and new password must match."] } }` |
| 401    | `{ "error": "Current password is incorrect" }` |
| 401    | `{ "error": "Unauthenticated" }` |
| 404    | `{ "error": "Profile not found" }` |
| 500    | `{ "error": "Internal server error" }` |

---

### 8. Deactivate Employee Account

**Endpoint:**

```
DELETE /employee/account
```

**Controller:** `EmployeeDataController@deactivateAccount`

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer {token}    |

**Success Response (200):**

```json
{
  "message": "Account deactivated successfully"
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 401    | `{ "error": "Unauthenticated" }` |
| 500    | `{ "error": "Internal server error" }` |

---

### 9. Setup Bank Account

**Endpoint:**

```
POST /employees/set-bank-info
```

**Controller:** `EmployeeController@completeBankInfo`

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Content-Type   | application/json   |
| Accept         | application/json   |
| Authorization  | Bearer {token}    |

**Body Parameters:**

| Field         | Type     | Required | Description                                                |
|---------------|----------|----------|------------------------------------------------------------|
| business_name | string   | Yes      | Name of the business entity                                |
| account_name  | string   | Yes      | Name on the bank account                                   |
| bank_code     | integer  | Yes      | Numeric bank code from Chapa's supported banks            |
| account_number| string   | Yes      | Bank account number                                        |

**Example Request:**

```json
{
  "business_name": "My Business",
  "account_name": "John Doe",
  "bank_code": 946,
  "account_number": "1000315712328"
}
```

**Success Response (200):**

```json
{
  "message": "Bank account registered successfully",
  "subaccount_id": "sub_123456789"
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 400    | `{ "error": "The Bank Code is incorrect please check if it does exist with our getbanks endpoint." }` |
| 400    | `{ "error": "The account number is not valid for bank name" }` |
| 401    | `{ "error": "Unauthenticated" }` |
| 500    | `{ "error": "Payment service is temporarily unavailable. Please try again later." }` |

---

### 10. Get Bank Account Information

**Endpoint:**

```
GET /employee/bank-account
```

**Controller:** `EmployeeDataController@getBankAccount`

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer {token}    |

**Success Response (200):**

```json
{
  "message": "Subaccount retrieved successfully",
  "data": {
    "sub_account_id": "sub_123456789",
    "updated_at": "2025-01-15T12:00:00.000000Z"
  }
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 401    | `{ "error": "Unauthenticated" }` |
| 404    | `{ "error": "Bank account not found" }` |
| 500    | `{ "error": "Internal server error" }` |

---

### 11. Update Bank Account Information

**Endpoint:**

```
PUT /employee/bank-account
```

**Controller:** `EmployeeDataController@updateBankAccount`

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Content-Type   | application/json   |
| Accept         | application/json   |
| Authorization  | Bearer {token}    |

**Body Parameters:**

| Field         | Type     | Required | Description                                                |
|---------------|----------|----------|------------------------------------------------------------|
| business_name | string   | Yes      | Updated business name                                       |
| account_name  | string   | Yes      | Updated account name                                        |
| bank_code     | integer  | Yes      | Updated bank code                                           |
| account_number| string   | Yes      | Updated account number                                      |

**Example Request:**

```json
{
  "business_name": "Updated Business Name",
  "account_name": "John Doe",
  "bank_code": 946,
  "account_number": "1000315712329"
}
```

**Success Response (200):**

```json
{
  "message": "Bank account updated successfully",
  "data": {
    "sub_account_id": "sub_987654321",
    "updated_at": "2025-01-15T12:30:00.000000Z"
  }
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 400    | `{ "error": "The Bank Code is incorrect please check if it does exist with our getbanks endpoint." }` |
| 400    | `{ "error": "The account number is not valid for bank name" }` |
| 401    | `{ "error": "Unauthenticated" }` |
| 404    | `{ "error": "Bank account not found" }` |
| 500    | `{ "error": "Payment service is temporarily unavailable. Please try again later." }` |

---

## Admin-Only Employee Data Routes

> **Note:** These endpoints are only accessible to authenticated admin users and require the `is_admin` middleware.

### 12. Get All Employee Data (Admin)

**Endpoint:**

```
GET /employees-data
```

**Controller:** `EmployeeDataController@index`

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer {admin_token} |

**Success Response (200):**

```json
{
  "data": [
    {
      "id": "01HXYZ1234567890ABCDEF",
      "employee_id": "01HABC1234567890ABCDEF",
      "first_name": "John",
      "last_name": "Doe",
      "email": "john.doe@example.com",
      "image_url": "https://example.com/profile.jpg",
      "created_at": "2025-01-15T10:30:00.000000Z",
      "updated_at": "2025-01-15T10:30:00.000000Z"
    }
  ]
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 401    | `{ "error": "Unauthenticated" }` |
| 403    | `{ "error": "Access denied. Admin privileges required." }` |

---

### 13. Get Specific Employee Data (Admin)

**Endpoint:**

```
GET /employees-data/{id}
```

**Controller:** `EmployeeDataController@show`

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer {admin_token} |

**Success Response (200):**

```json
{
  "id": "01HXYZ1234567890ABCDEF",
  "employee_id": "01HABC1234567890ABCDEF",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "image_url": "https://example.com/profile.jpg",
  "created_at": "2025-01-15T10:30:00.000000Z",
  "updated_at": "2025-01-15T10:30:00.000000Z"
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 401    | `{ "error": "Unauthenticated" }` |
| 403    | `{ "error": "Access denied. Admin privileges required." }` |
| 404    | `{ "error": "Employee data not found" }` |

---

### 14. Create Employee Data (Admin)

**Endpoint:**

```
POST /employees-data
```

**Controller:** `EmployeeDataController@store`

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Content-Type   | application/json   |
| Accept         | application/json   |
| Authorization  | Bearer {admin_token} |

**Body Parameters:**

| Field      | Type   | Required | Description                    |
|------------|--------|----------|--------------------------------|
| employee_id| ulid   | Yes      | ID of the associated employee  |
| first_name | string | Yes      | Employee's first name          |
| last_name  | string | Yes      | Employee's last name           |
| email      | string | Yes      | Employee's email address       |
| image_url  | url    | No       | URL to employee's profile picture |

**Example Request:**

```json
{
  "employee_id": "01HABC1234567890ABCDEF",
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane.smith@example.com",
  "image_url": "https://example.com/profile.jpg"
}
```

**Success Response (201):**

```json
{
  "message": "Employee data created successfully",
  "data": {
    "id": "01HXYZ1234567890ABCDEF",
    "employee_id": "01HABC1234567890ABCDEF",
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane.smith@example.com",
    "image_url": "https://example.com/profile.jpg",
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z"
  }
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 401    | `{ "error": "Unauthenticated" }` |
| 403    | `{ "error": "Access denied. Admin privileges required." }` |
| 422    | `{ "message": "Validation failed", "errors": { "email": ["The email field must be a valid email address."] } }` |

---

### 15. Update Employee Data (Admin)

**Endpoint:**

```
PUT /employees-data/{id}
```

**Controller:** `EmployeeDataController@update`

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Content-Type   | application/json   |
| Accept         | application/json   |
| Authorization  | Bearer {admin_token} |

**Body Parameters:**

| Field      | Type   | Required | Description                    |
|------------|--------|----------|--------------------------------|
| first_name | string | No       | Employee's first name          |
| last_name  | string | No       | Employee's last name           |
| email      | string | No       | Employee's email address       |
| image_url  | url    | No       | URL to employee's profile picture |

**Example Request:**

```json
{
  "first_name": "Jane",
  "last_name": "Johnson",
  "email": "jane.johnson@example.com"
}
```

**Success Response (200):**

```json
{
  "message": "Employee data updated successfully",
  "data": {
    "id": "01HXYZ1234567890ABCDEF",
    "first_name": "Jane",
    "last_name": "Johnson",
    "email": "jane.johnson@example.com",
    "updated_at": "2025-01-15T11:00:00.000000Z"
  }
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 401    | `{ "error": "Unauthenticated" }` |
| 403    | `{ "error": "Access denied. Admin privileges required." }` |
| 404    | `{ "error": "Employee data not found" }` |
| 422    | `{ "message": "Validation failed", "errors": { "email": ["The email field must be a valid email address."] } }` |

---

### 16. Delete Employee Data (Admin)

**Endpoint:**

```
DELETE /employees-data/{id}
```

**Controller:** `EmployeeDataController@destroy`

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer {admin_token} |

**Success Response (200):**

```json
{
  "message": "Employee data deleted successfully"
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 401    | `{ "error": "Unauthenticated" }` |
| 403    | `{ "error": "Access denied. Admin privileges required." }` |
| 404    | `{ "error": "Employee data not found" }` |

---

### 17. Get Employee Data by Employee ID (Admin)

**Endpoint:**

```
GET /employees-data/employee/{employeeId}
```

**Controller:** `EmployeeDataController@getByEmployeeId`

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer {admin_token} |

**Success Response (200):**

```json
{
  "data": {
    "id": "01HXYZ1234567890ABCDEF",
    "employee_id": "01HABC1234567890ABCDEF",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "image_url": "https://example.com/profile.jpg",
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z"
  }
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 401    | `{ "error": "Unauthenticated" }` |
| 403    | `{ "error": "Access denied. Admin privileges required." }` |
| 404    | `{ "error": "Employee data not found" }` |

---

### 18. Get Employee transactions

**Endpoint:**

```
GET  /employees/transactions
```

**Controller:** `EmployeeController@transactions`

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer {token} |

**Success Response (200):**

```json
{
  "transactions": [
    {
      "id": "01k56ymcgm3p95mcgr0x2ke1r0",
      "tx_ref": "EdayefSSKw",
      "status": "completed",
      "created_at": "2025-09-15T15:06:57.000000Z",
      "amount": 150.0
    }
  ]
}
```

Notes:
- `amount` is the net amount received by the employee for that transaction (after fees).