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
| Content-Type   | application/json   |
| Accept         | application/json   |

**Body Parameters:**

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

**Example Request:**

```json
{
  "name": "My Service",
  "category_id": "<ulid>",
  "email": "service@example.com",
  "password": "securepassword",
  "contact_phone": "+251912345678",
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

### 3. Verify Service Provider Email

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

// TODO
### 4. Get Employees (Authenticated)

**Endpoint:**

```
GET /service-providers/employees
```

**Headers:**

| Key            | Value                        |
|----------------|------------------------------|
| Authorization  | Bearer {token}               |
| Accept         | application/json             |

**Success Response (200):**

```json
[
  {
    "id": "01JXYZ1234567890",
    "unique_id": "01JXYZ0987654321",
    "is_active": true,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "image_url": "https://example.com/images/john.jpg"
  }
]
```

**Error Response:**

| Status | Example Response |
|--------|------------------|
| 401    | `{ "message": "Unauthenticated" }` |

---

## 4. Activate an Employee

**Endpoint:**

```
PATCH /service-providers/employees/{id}/activate
```

**Headers:**

| Key            | Value                        |
|----------------|------------------------------|
| Authorization  | Bearer {token}               |
| Accept         | application/json             |

**Success Response (200):**

```json
{ "message": "Employee activated successfully" }
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 404    | `{ "message": "Employee not found" }` |
| 401    | `{ "message": "Unauthenticated" }` |

---

## 5. Deactivate an Employee

**Endpoint:**

```
PATCH /service-providers/employees/{id}/deactivate
```

**Headers:**

| Key            | Value                        |
|----------------|------------------------------|
| Authorization  | Bearer {token}               |
| Accept         | application/json             |

**Success Response (200):**

```json
{ "message": "Employee deactivated successfully" }
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 404    | `{ "message": "Employee not found" }` |
| 401    | `{ "message": "Unauthenticated" }` |

---

## 6. Employee Summary

**Endpoint:**

```
GET /service-providers/employees/summary
```

**Headers:**

| Key            | Value                        |
|----------------|------------------------------|
| Authorization  | Bearer {token}               |
| Accept         | application/json             |

**Success Response (200):**

```json
{
  "total": 50,
  "active": 45,
  "inactive": 5
}
```

**Error Response:**

| Status | Example Response |
|--------|------------------|
| 401    | `{ "message": "Unauthenticated" }` |


