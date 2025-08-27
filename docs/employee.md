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
