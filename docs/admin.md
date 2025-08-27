Admin Authentication

Base

Prefix: /api/admin
Content-Type: application/json
Protected routes require:
Authorization: Bearer {token}

---

### Reporting & Analytics

Base: /api/admin/reports

Query date range for all endpoints (optional):
- start_date: YYYY-MM-DD
- end_date: YYYY-MM-DD

#### GET /admin/reports/overview
KPIs returned:
- total_gross_tips
- platform_revenue
- chapa_fees
- net_to_employees
- active_providers
- active_employees

Example:
GET /admin/reports/overview?start_date=2025-08-01&end_date=2025-08-27

200 Response:
json
{
  "range": {"start": "2025-08-01 00:00:00", "end": "2025-08-27 23:59:59"},
  "total_gross_tips": 12345.67,
  "platform_revenue": 456.78,
  "chapa_fees": 123.45,
  "net_to_employees": 11765.44,
  "active_providers": 12,
  "active_employees": 98
}

#### GET /admin/reports/tips
Filters:
- provider_id
- employee_id
- status (e.g., pending, paid, failed)
- start_date, end_date
- per_page (default 15)

Example:
GET /admin/reports/tips?provider_id=01K..&status=paid&start_date=2025-08-01&end_date=2025-08-27&per_page=20

200 Response: Laravel pagination JSON of `tips` ordered by created_at desc

#### GET /admin/reports/payments
Filters:
- provider_id (via underlying tip.service_provider_id)
- employee_id
- start_date, end_date
- per_page (default 15)

Example:
GET /admin/reports/payments?employee_id=01K..&start_date=2025-08-01&end_date=2025-08-27

200 Response: Laravel pagination JSON of `payments` ordered by created_at desc

#### GET /admin/reports/top-employees
Query:
- start_date, end_date (optional)
- limit (default 10)

Returns: items = [{ employee_id, total_amount, payments_count }]

#### GET /admin/reports/top-providers
Query:
- start_date, end_date (optional)
- limit (default 10)

Returns: items = [{ provider_id, total_amount, payments_count }]
Accept: application/json

### Endpoints

#### POST /admin/login

Body:
json
{
  "email": "admin@example.com",
  "password": "secret123"
}

200 Response:
json
{
  "message": "Login successful",
  "admin": {
    "id": "01J...ULID",
    "name": "Super Admin",
    "email": "admin@example.com"
  },
  "token": "plain-text-token"
}


401/404 Errors:
json
{ "error": "Invalid credentials" }
or

json
{ "error": "Admin not found" }

#### GET /admin/profile

Headers: Authorization: Bearer {token}

200 Response:
json
{
  "id": "01J...ULID",
  "name": "Super Admin",
  "email": "admin@example.com",
  "is_active": true,
  "last_login_at": "2025-08-27T10:12:34.000000Z",
  "created_at": "2025-08-27T09:00:00.000000Z"
}

#### POST /admin/logout

Headers: Authorization: Bearer {token}

200 Response:
json
{ "message": "Logged out successfully" }


### Admin Seeding

Seeder: AdminSeeder
Purpose:
Create/update an admin user (upsert by email)
Delete an admin by email only (optional)
Run

Create/Update (default flow):

Command:

php artisan db:seed --class=AdminSeeder

Prompts:
Do you want to delete an admin by email only? → No
Admin name: Super Admin
Admin email: admin@example.com
Admin password: secret123


Result:
If an admin with that email exists, it is deleted and re-created
New credentials become active immediately


### Admin Service Providers

Base: /api/admin

Headers (protected routes):
- Authorization: Bearer {token}
- Accept: application/json


#### GET /admin/service-providers

Query params (optional):
- status: pending | accepted | rejected
- is_suspended: true | false
- is_verified: true | false
- q: search by name/email
- per_page: integer (default 15)

Example:
GET /admin/service-providers?status=pending&is_suspended=false&is_verified=false&q=spa&per_page=20

200 Response:
json
{
  "current_page": 1,
  "data": [ { "id": "01K...", "name": "Blue Lagoon Spa", "email": "spa..@example.com", "registration_status": "pending" } ],
  "per_page": 20,
  "total": 1
}

#### GET /admin/service-providers/{id}

Example:
GET /admin/service-providers/01K3NRCEG37NXFY816Z858XF8W

200 Response:
json
{
  "id": "01K3NRCEG37NXFY816Z858XF8W",
  "name": "Blue Lagoon Spa",
  "email": "spad6bskf@example.com",
  "registration_status": "pending",
  "is_verified": false,
  "is_suspended": false
}

#### POST /admin/service-providers/{id}/accept

Body: none

200 Response:
json
{
  "message": "Service provider accepted",
  "provider": { "id": "01K...", "registration_status": "accepted", "is_verified": true }
}

#### POST /admin/service-providers/{id}/reject

Body: none

200 Response:
json
{
  "message": "Service provider rejected",
  "provider": { "id": "01K...", "registration_status": "rejected" }
}

#### POST /admin/service-providers/{id}/suspend

Body:
json
{
  "reason": "Policy violation"
}

200 Response:
json
{
  "message": "Service provider suspended",
  "provider": { "id": "01K...", "is_suspended": true, "suspended_at": "2025-08-27T12:35:00Z", "suspension_reason": "Policy violation" }
}

#### POST /admin/service-providers/{id}/unsuspend

Body: none

200 Response:
json
{
  "message": "Service provider unsuspended",
  "provider": { "id": "01K...", "is_suspended": false, "suspended_at": null, "suspension_reason": null }
}

#### GET /admin/service-providers/{id}/employees

Description: List employees for a provider. Use GET (no body).

Example:
GET /admin/service-providers/01K3NRCEG37NXFY816Z858XF8W/employees

200 Response:
json
{
  "provider_id": "01K3NRCEG37NXFY816Z858XF8W",
  "employees": [
    {
      "id": "01K3NRG8P0SWEHW2MA2F3MG8ZF",
      "unique_id": "01K...",
      "is_active": false,
      "first_name": null,
      "last_name": null,
      "email": null,
      "image_url": null
    }
  ]
}

### Employee Oversight (AdminEmployeeController)

Base: /api/admin

Headers (protected routes):
- Authorization: Bearer {token}
- Accept: application/json

#### GET /admin/employees

Query params (optional):
- q: search by name/email/unique_id
- provider_id: ULID of service provider
- is_active: true | false
- is_verified: true | false
- is_suspended: true | false
- per_page: integer (default 15)

Example:
GET /admin/employees?q=dawit&provider_id=01K3NRCEG37NXFY816Z858XF8W&is_active=false&per_page=20

200 Response:
json
{
  "current_page": 1,
  "data": [
    {
      "id": "01K3NRG8P0SWEHW2MA2F3MG8ZF",
      "unique_id": "01K...",
      "service_provider_id": "01K3NRCEG37NXFY816Z858XF8W",
      "is_active": false,
      "is_verified": false,
      "is_suspended": false
    }
  ],
  "per_page": 20,
  "total": 1
}

#### GET /admin/employees/{id}

Example:
GET /admin/employees/01K3NRG8P0SWEHW2MA2F3MG8ZF

200 Response:
json
{
  "id": "01K3NRG8P0SWEHW2MA2F3MG8ZF",
  "unique_id": "01K...",
  "service_provider_id": "01K3NRCEG37NXFY816Z858XF8W",
  "is_active": false,
  "is_verified": false,
  "is_suspended": false
}

#### POST /admin/employees/{id}/activate

Body: none

200 Response:
json
{
  "message": "Employee activated",
  "employee": { "id": "01K3NRG8P0SWEHW2MA2F3MG8ZF", "is_active": true }
}

#### POST /admin/employees/{id}/deactivate

Body: none

200 Response:
json
{
  "message": "Employee deactivated",
  "employee": { "id": "01K3NRG8P0SWEHW2MA2F3MG8ZF", "is_active": false }
}

#### POST /admin/employees/{id}/suspend

Body:
json
{
  "reason": "Policy violation"
}

200 Response:
json
{
  "message": "Employee suspended",
  "employee": { "id": "01K3NRG8P0SWEHW2MA2F3MG8ZF", "is_suspended": true, "suspended_at": "2025-08-27T14:05:00Z", "suspension_reason": "Policy violation" }
}

#### POST /admin/employees/{id}/unsuspend

Body: none

200 Response:
json
{
  "message": "Employee unsuspended",
  "employee": { "id": "01K3NRG8P0SWEHW2MA2F3MG8ZF", "is_suspended": false, "suspended_at": null, "suspension_reason": null }
}