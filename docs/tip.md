## Tip API Documentation

#### Base URL
```
http://your-domain.com/api
```
> The following endpoints assumes you are using the above base url.
---

### 1. Initialize tip transaction

```
GET /tip/<tip_code>/?amount=<tip_amount>
```
**Headers:**

| Key            | Value              |
|----------------|--------------------|
| Accept         | application/json   |

**Query Parameters:**

| Field    | Type   | Required | Description                       |
|----------|--------|----------|-----------------------------------|
| amount   | string | Yes      | tip amount                        |


**Success Response (200):**

```json
{
  "link": <chapa_checkout_link>,
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 409    | `{ "error": "employee not found" }` |
| 500    | `{ "error": "internal server error" }` |

---
