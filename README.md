# Backend and Symfony

Symfony API developed as part of a vocational web development module at Stucom. The project exposes endpoints for nurse data management, user CRUD operations, and login verification, returning JSON responses and connecting to a cloud-hosted database.

## Overview

This repository contains a Symfony backend API that:

- Connects to a cloud-hosted MySQL database (hosted on Filess.io) via Doctrine ORM.
- Validates nurse login credentials through a dedicated endpoint against remote database records.
- Exposes nurse listing and search endpoints querying the cloud database in real time.
- Provides User CRUD management controllers and templates.
- Returns JSON responses suitable for frontend integration and API testing tools.

## Tech Stack

- PHP 8.2+
- Symfony 7.4
- Doctrine ORM & Migrations
- Cloud MySQL Database (hosted on Filess.io)
- Composer
- Postman / cURL for API testing

## Project Structure

```text
Backend-and-Symfony/
├── config/
│   ├── packages/
│   │   └── doctrine.yaml
│   ├── routes/
│   ├── bundles.php
│   ├── preload.php
│   ├── routes.yaml
│   └── services.yaml
├── migrations/
├── public/
│   ├── index.php
│   └── nurses.json
├── src/
│   ├── Controller/
│   │   ├── LoginController.php
│   │   ├── NurseController.php
│   │   └── UserController.php
│   ├── Entity/
│   │   └── User.php
│   ├── Form/
│   │   └── UserType.php
│   ├── Repository/
│   │   └── UserRepository.php
│   ├── Service/
│   │   └── NurseProvider.php
│   └── Kernel.php
├── templates/
│   └── user/
├── .env
├── .env.dev
├── .editorconfig
├── .gitignore
├── composer.json
├── composer.lock
├── README.md
└── symfony.lock
```

## Main Functionality & Endpoints

### 1. Nurse Login
The application validates nurse credentials (email and password) directly against the `user` table stored in the cloud database.

Endpoint:

- `POST /nurse/login`

Expected JSON body:

```json
{
  "email": "example@email.com",
  "password": "secret123"
}
```

Example success response (HTTP 200):

```json
{
  "success": true,
  "login": true,
  "user": {
    "id": 1,
    "email": "example@email.com",
    "name": "Ana",
    "surname": "García"
  }
}
```

Example invalid credentials response (HTTP 401):

```json
{
  "error": "Invalid credentials",
  "success": false,
  "login": false
}
```

### 2. Create Nurse
Creates and persists a new nurse in the centralized cloud database with encrypted password using Symfony's password hasher.

Endpoint:

- `POST /nurse`

Expected JSON body:

```json
{
  "name": "Lucía",
  "last_name": "Fernández Ruiz",
  "national_id": "45678901B",
  "license_number": "ENF-4567",
  "specialty": "Pediatrics",
  "shift": "Morning",
  "email": "lucia.fernandez@hospital.com",
  "password": "Password123!"
}
```

Example success response (HTTP 201 Created):

```json
{
  "message": "Nurse created successfully",
  "success": true,
  "nurse": {
    "id": 13,
    "name": "Lucía",
    "last_name": "Fernández Ruiz",
    "national_id": "45678901B",
    "license_number": "ENF-4567",
    "specialty": "Pediatrics",
    "shift": "Morning",
    "email": "lucia.fernandez@hospital.com",
    "password": "$2y$13$..."
  }
}
```

Example missing data or validation error (HTTP 400 Bad Request):

```json
{
  "error": "Missing required fields: password",
  "missing_fields": [
    "password"
  ],
  "success": false
}
```

### 3. List All Nurses
Endpoint:

- `GET /nurse/index`

Queries the cloud database and returns a JSON array of all registered nurses/users.

### 4. Search Nurse by Name
Endpoint:

- `GET /nurse/name/{name}`

Example:

```text
GET /nurse/name/ana
```

If a match is found, the API returns the matching nurse record(s). Otherwise, it responds with:

```json
{
  "error": "Nurse not found"
}
```

### 5. User CRUD (Web)
Endpoints under `/user` provide full management capabilities (List, Create, Show, Edit, Delete) for user entities via Twig templates:

- `GET /user` - User listing
- `GET/POST /user/new` - Create a new user
- `GET /user/{id}` - View user details
- `GET/POST /user/{id}/edit` - Edit existing user
- `POST /user/{id}` - Delete user

## Getting Started

### Prerequisites

Make sure the following are installed on your machine:

- **PHP 8.2** or newer (with `pdo_mysql` enabled)
- **Composer**
- **Internet connection** (required to reach the cloud MySQL database)
- **Postman** or **cURL** for testing endpoints

> **Note:** A local MySQL/MariaDB server (such as XAMPP MySQL) is no longer required to be running locally, as all database operations connect directly to the shared cloud database instance.

### Installation

1. Clone the repository and navigate to the project directory:
   ```bash
   cd Backend-and-Symfony
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

### Run the Project

You can start the application using PHP's built-in server:

```bash
php -S 127.0.0.1:8000 -t public
```

Or using the Symfony CLI (if installed):

```bash
symfony server:start
```

Then access the API at:

```text
http://127.0.0.1:8000
```

## Database & Environment Configuration

The application is configured to connect to a remote cloud MySQL database hosted on **Filess.io**.

The connection string is configured in `.env`:

```env
DATABASE_URL="mysql://hospital_app_signwhenam:[PASSWORD]@e7crf7.h.filess.io:61002/hospital_app_signwhenam"
```

### Cloud Database Benefits for the Team:
- **Centralized Data**: All team members interact with the same remote dataset in real time.
- **No Local DB Setup**: No need to import/export `.sql` dumps or maintain local database configurations.
- **Local Overrides**: If you need to test against a different database locally, create an uncommitted `.env.local` file to override `DATABASE_URL`.

## Useful Commands

```bash
# Install PHP dependencies
composer install

# Verify database connection and schema mapping
php bin/console doctrine:schema:validate

# Run migrations (if new migrations are added)
php bin/console doctrine:migrations:migrate

# List all registered routes
php bin/console debug:router

# Clear Symfony cache
php bin/console cache:clear

# Start development server
php -S 127.0.0.1:8000 -t public
```

## License

This project is intended for educational purposes as part of a web development module.

## Author

Stucom / Backend and Symfony coursework project
