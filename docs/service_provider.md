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
