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


