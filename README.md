# Backend and Symfony

Symfony API developed as part of a vocational web development module at Stucom. The project exposes endpoints for nurse data management and user login verification, returning JSON responses.

## Overview

This repository contains a lightweight Symfony backend that:

- Reads nurse data from a JSON file stored in `public/nurses.json`
- Validates nurse login credentials through a dedicated endpoint
- Exposes basic nurse listing and search endpoints
- Returns JSON responses suitable for frontend integration or API testing tools

## Tech Stack

- PHP 8.2+
- Symfony 7.4
- Postman for tests
- Composer
- JSON-based data source

## Project Structure

```text
Backend-and-Symfony/
├── config/
│   ├── packages/
│   ├── routes/
│   ├── bundles.php
│   ├── preload.php
│   ├── routes.yaml
│   └── services.yaml
├── public/
│   ├── index.php
│   └── nurses.json
├── src/
│   ├── Controller/
│   │   ├── LoginController.php
│   │   └── NurseController.php
│   └── Kernel.php
├── .env
├── .env.dev
├── .editorconfig
├── .gitignore
├── composer.json
├── composer.lock
├── README.md
├── symfony.lock
└── vendor/
```

## Main Functionality

### 1. Nurse login
The application validates an email and password against the nurse records in `public/nurses.json`.

Endpoint:

- `POST /nurse/login`

Expected JSON body:

```json
{
  "email": "example@email.com",
  "password": "secret123"
}
```

Example success response:

```json
{
  "success": true,
  "login": true
}
```

Example invalid credentials response:

```json
{
  "error": "Invalid credentials",
  "success": false,
  "login": false
}
```

### 2. List all nurses
Endpoint:

- `GET /nurse/index`

Returns a JSON array of nurses from the JSON file.

### 3. Search nurse by name
Endpoint:

- `GET /nurse/name/{nombre}`

Example:

```text
GET /nurse/name/ana
```

If a match is found, the API returns the corresponding nurse object. Otherwise, it responds with:

```json
{
  "error": "Nurse not found"
}
```

## Getting Started

### Prerequisites

Make sure the following are installed on your machine:

- PHP 8.2 or newer
- Composer
- Postman
- A local web server or PHP built-in server

### Install dependencies

```bash
composer install
```

### Run the project

You can start the application with the built-in PHP server:

```bash
php -S 127.0.0.1:8000 -t public
```

Then access the API in your browser or through Postman/cURL at:

```text
http://127.0.0.1:8000
```

## Environment Configuration

The project includes default Symfony environment files:

- `.env`
- `.env.dev`

These files contain application configuration and environment variables. Adjust them if your local setup requires different values.

## Notes

- The project uses a local JSON file as its data source instead of a database.
- `public/nurses.json` is the main source of nurse records.
- This backend is suitable for learning Symfony routing, controllers, JSON response handling, and basic API authentication flows.

## Useful Commands

```bash
composer install
php bin/console debug:router
php bin/console cache:clear
```

## License

This project is intended for educational purposes as part of a web development module.

## Author

Stucom / Backend and Symfony coursework project
