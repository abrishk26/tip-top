## Employee API Documentation
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

### 4. Get Employee Profile

**Endpoint:**

```
GET /employees/profile
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

### 5. Update Employee Profile

**Endpoint:**

```
PUT /employees/profile
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

### 6. Change Employee Password

**Endpoint:**

```
PUT /employees/change-password
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

### 7. Deactivate Employee Account

**Endpoint:**

```
DELETE /employees/account
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
