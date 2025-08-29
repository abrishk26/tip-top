## Service Provider API Documentation
---
This section describes the available API endpoints related with Service Providers, including request headers, body parameters, and possible responses.
---

#### Base URL
```
http://your-domain.com/api
```
> The following endpoints assumes you are using the above base url.
---

### 1. Register Service Provider

**Endpoint:**

```
POST /service-providers/register
```

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Content-Type   | form-data          |

**Body Parameters:**

Fields:
  #### 1. provider_data - text form of the below json data 

| Field         | Type     | Required | Description                                                                                                   |
|---------------|----------|----------|---------------------------------------------------------------------------------------------------------------|
| name          | string   | Yes      | Service provider's name                                                                                       |
| category_id   | ulid     | Yes      | Service provider's category                                                                                   |
| email         | string   | Yes      | Unique email address                                                                                          |
| description   | string   | No       | Service Provider Description, must not exceed 500 characters                                                  |
| tax_id        | string   | No       | Service Provider Tax ID, must not exceed 150.                                                                 |
| password      | string   | Yes      | Plain password (will be hashed)                                                                               |
| contact_phone | string   | Yes      | Must start with +2519 or +2517                                                                                |
| address       | object   | Yes      | Must contains fields - street_address, city, region - which are all string, required and max 150 characters   |
| image_url     | url      | No       | URL to service provider's logo                                                                                |

  #### 2. license: license file

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
| 400    | `{ "error": "license file missing" }` |

---

### 2. Login Service Provider

**Endpoint:**

```
POST /service-providers/login
```

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Content-Type   | application/json   |
| Accept         | application/json   |

**Body Parameters:**

| Field    | Type   | Required | Description                       |
|----------|--------|----------|-----------------------------------|
| email    | string | Yes      | Registered service provider email |
| password | string | Yes      | Plain password                    |

**Example Request:**

```json
{
  "email": "service@example.com",
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

### 2. Logout Service Provider

**Endpoint:**

```
POST /service-providers/logout
```

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer <token>     |

**Success Response (200):**

```json
{
  "message" => "Logged out successfully"
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 500    | `{ "error": "Failed to logout" }` |

---

### 4. Verify Service Provider Email

**Endpoint:**

```
POST /service-providers/verify-email/?token=<token>
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

---

### 5. Get Service Provider Profile

**Endpoint:**

```
GET /service-providers/profile
```

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer <token>     |

**Success Response (200):**

```json
{
    'id' : <provider_id>,
    'name' : <provider_name>,
    'email' : <provider_email>,
    'category' : <category_name>,
    'description' : <description> optional,
    'tax_id' : <provider_tax_id> optional,
    'address' : <provider_address>: json with fields street, city, region,
    'license' : <path_to_provider_license_file>,
    'contact_phone' : <contact_phone>,
    'image_url' : <image_url> optional,
    'created_at' : <created_at>,
    'updated_at' : <updated_at>

}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 400    | `{ "error": "Unauthorized" }` |

---

### 6. Register Employees

**Endpoint:**

```
POST /service-providers/employees/register
```

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer <token>     |

**Example Request:**

```json
{
  "count": <number> min:1, max:100
}
```

**Success Response (200):**

```json
{
  "message": "Employees registered",
  "employees": [ {"employee_code": <code>}, ...]
}
```
**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 400    | `{ "error": "Unauthorized" }` |
| 400    | `{ "error": "Duplicate employee IDs detected" }` |
| 500    | `{ "error": "Internal server error" }` |

---

### 7. Get Employees Data

**Endpoint:**

```
GET /service-providers/employees
```

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer <token>     |


**Success Response (200):**

```json
{
  "employees": [ {"id": <employee_id>, "is_active": <boolean>, "first_name": <>, "last_name": <>, "email": <email>, "image_url": <>}, ...]
}
```
**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 400    | `{ "error": "Unauthorized" }` |
| 500    | `{ "error": "Internal server error" }` |

---

### 8. Get Employees Summary

**Endpoint:**

```
GET /service-providers/employees/summary
```

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer <token>     |


**Success Response (200):**

```json
{
  "total": <total_employees_count>,
  "active": <active_employees_count>,
  "inactive": <inactive_employees_count>,
}
```
**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 400    | `{ "error": "Unauthorized" }` |
| 500    | `{ "error": "Internal server error" }` |

---

### 9. Activate Employee

**Endpoint:**

```
GET /service-providers/employees/activate/:employee_id
```

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer <token>     |


**Success Response (200):**

```json
{
  "message": "Employee activated"
}
```
**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 400    | `{ "error": "Unauthorized" }` |
| 409    | `{ "error": "employee not found" }` |
| 500    | `{ "error": "Internal server error" }` |

---
### 10. Deactivate Employee

**Endpoint:**

```
GET /service-providers/employees/deactivate/:employee_id
```

**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |
| Authorization  | Bearer <token>     |


**Success Response (200):**

```json
{
  "message": "Employee deactivated"
}
```
**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 400    | `{ "error": "Unauthorized" }` |
| 409    | `{ "error": "employee not found" }` |
| 500    | `{ "error": "Internal server error" }` |

---
