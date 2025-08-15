# Tip Top
# Setting Up The Project

This guide covers installing PHP, Composer, Laravel, MySQL, and configuring your `.env` file after cloning a project.

---

## 1. Install PHP

1. **Check if PHP is installed:**

```bash
php -v
```

2. **Ubuntu / Debian:**

```bash
sudo apt update
sudo apt install php php-cli php-mbstring php-xml php-bcmath php-curl php-mysql unzip curl -y
```

3. **Check version:**

```bash
php -v
```

> Laravel 10 requires PHP 8.1 or higher.

---

## 2. Install Composer

Composer is a PHP dependency manager.

1. **Download Composer installer:**

```bash
curl -sS https://getcomposer.org/installer -o composer-setup.php
```

2. **Install globally:**

```bash
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

3. **Check installation:**

```bash
composer -V
```

---

## 3. Clone the Laravel Project

```bash
git clone <repository-url> project-name
cd project-name
```

---

## 4. Install Laravel Dependencies

```bash
composer install
```

---

## 5. Set Up Environment File

1. Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

2. Open `.env` in your editor and set the database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

> Make sure the database exists before running migrations. You can create it with:

```bash
mysql -u your_db_username -p
CREATE DATABASE your_database_name;
```

---

## 6. Run Migrations

```bash
php artisan migrate
```

---

## 7. Start the Development Server

```bash
php artisan serve
```

> Your Laravel project should now be running at `http://127.0.0.1:8000`.

# Service Provider API Documentation

This document describes the available API endpoints for managing Service Providers, including request headers, body parameters, and possible responses.

---

## Base URL

```
http://your-domain.com/api
```

---

## 1. Register Service Provider

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

| Field         | Type     | Required | Description                       |
|---------------|----------|----------|-----------------------------------|
| name          | string   | Yes      | Service provider's name           |
| email         | string   | Yes      | Unique email address              |
| password      | string   | Yes      | Plain password (will be hashed)   |
| contact_phone | string   | Yes      | Must start with +2519 or +2517    |

**Example Request:**

```json
{
  "name": "My Service",
  "email": "service@example.com",
  "password": "securepassword",
  "contact_phone": "+251912345678"
}
```

**Success Response (201):**

```json
{
  "message": "Service provider registered successfully",
  "data": {
    "id": "01JXYZ1234567890",
    "name": "My Service",
    "email": "service@example.com"
  }
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 422    | `{ "message": "Validation failed", "errors": { "email": ["The email field must be a valid email address."] } }` |
| 409    | `{ "message": "Email already exists" }` |

---

## 2. Login Service Provider

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

| Field    | Type   | Required | Description                   |
|----------|--------|----------|-------------------------------|
| email    | string | Yes      | Registered service provider email |
| password | string | Yes      | Plain password                 |

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
  "message": "Login successful",
  "token": "1|abcdefghijklmopqrstuvwxyz"
}
```

**Error Responses:**

| Status | Example Response |
|--------|------------------|
| 401    | `{ "message": "Invalid credentials" }` |
| 404    | `{ "message": "Service provider not found" }` |

---

## 3. Get Employees (Authenticated)

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

