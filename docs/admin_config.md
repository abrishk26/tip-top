# Admin Categories Config API

Base: /api/admin/categories
Content-Type: application/json
Protected: Yes (Authorization: Bearer {token})

---

## List Categories

GET /admin/categories

Description: List all categories (ULID id, name)

200 Response:
```json
[
  { "id": "01K3...ULID", "name": "Spa & Wellness" },
  { "id": "01K3...ULID", "name": "Beauty" }
]
```

---

## Create Category

POST /admin/categories

Headers:
- Content-Type: application/json

Body:
```json
{ "name": "Spa & Wellness" }
```

201 Response:
```json
{ "id": "01K3...ULID", "name": "Spa & Wellness", "created_at": "2025-08-27T20:00:00.000000Z", "updated_at": "2025-08-27T20:00:00.000000Z" }
```

Validation Errors (422):
```json
{ "errors": { "name": ["The name has already been taken."] } }
```

---

## Update Category

PUT /admin/categories/{id}

Headers:
- Content-Type: application/json

Body:
```json
{ "name": "Spa & Beauty" }
```

200 Response:
```json
{ "id": "01K3...ULID", "name": "Spa & Beauty", "created_at": "2025-08-27T19:00:00.000000Z", "updated_at": "2025-08-27T21:10:00.000000Z" }
```

Not Found (404):
```json
{ "message": "No query results for model [App\\Models\\Category] 01K..." }
```

---

## Delete Category

DELETE /admin/categories/{id}

200 Response:
```json
{ "message": "Category deleted" }
```

Not Found (404):
```json
{ "message": "No query results for model [App\\Models\\Category] 01K..." }
```

---

## Auth Notes
- Obtain admin token via POST /api/admin/login
- Include header: `Authorization: Bearer {token}` on all requests
- 401 if missing/invalid token
