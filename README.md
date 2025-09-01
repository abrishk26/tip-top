# Tip Top
---

## Setting Up The Project

### 1. Prerequisites

    1. Install PHP
    2. Install Composer

---

### 2. Clone the Project

```bash
git clone git@github.com:abrishk26/tip-top.git
cd tip-top
```
---

### 3. Install Dependencies

```bash
composer install
```
---

### 4. Set Up Environment File

1. Copy `.env.example` to `.env`:

    ```bash
    cp .env.example .env
    ```
2. Open `.env` in your editor:

    ```env
    // chapa configuration 
    CHAPA_KEY="<your-chapa-api-key>"

    // cloudinary configuration
    CLOUDINARY_KEY="<api-key>"
    CLOUDINARY_SECRET="<secret_key>"
    CLOUDINARY_CLOUD_NAME="<cloud_name>"
    CLOUDINARY_URL="<cloudinary_url>"

    // email service configuration
    RESEND_KEY="<your_email_service_api_key>" // note this example uses resend, you can change to other providers

    // frontend url includeing port
    FRONTEND_URL=<url:port>

    // database configuration
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

### 5. Run Migrations

```bash
php artisan migrate
```
---

### 6. Start the Development Server

```bash
php artisan serve
```
> Your Laravel project should now be running at `http://127.0.0.1:8000`.

---
