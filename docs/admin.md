Admin Authentication

Base

Prefix: /api/admin
Content-Type: application/json
Protected routes require:
Authorization: Bearer {token}
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