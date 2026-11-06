# 🏥 Hospital Nurse Application — Backend REST API

## 📖 Description

**Hospital Nurse Application** is a RESTful API developed with **Symfony** and **PHP 8.2**, designed to manage hospital emergency nursing staff in a centralized, secure, and efficient manner.

### What problem does it solve?
In hospital emergency environments, manual or decentralized management of shifts, specialties, and healthcare personnel credentials leads to delays and administrative errors. This application solves this problem by providing a standardized backend that automates the complete lifecycle of nurse records (**CRUD**: Create, Read, Update, and Delete), validates the integrity of their professional data (National ID, license number, specialty, and shift), and authenticates their access credentials in real time.

### Main Features
* **Comprehensive Nurse Management (CRUD):** REST endpoints to register, list, search by ID or name, update, and delete nursing records.
* **Authentication System (Nurse Login):** Secure validation of access credentials for registered personnel.
* **Strict Data Validation:** Control of mandatory fields, email format validation, length limits, and duplicate prevention (Email, National ID, and License Number).
* **Quality and Automation (CI/CD):** Automated test suite using **PHPUnit** integrated into a **GitHub Actions** workflow that validates code on every *push* and *Pull Request*.

---

## 🛠️ Prerequisites

Before starting the installation, make sure you have the following tools installed on your system:

* **PHP** >= 8.2 (with extensions `pdo_mysql`, `mbstring`, `xml`, `ctype`, `iconv`, `intl`)
* **Composer** (PHP dependency manager)
* **Database Server** MySQL / MariaDB (local via XAMPP or remote on `filess.io`)
* **Symfony CLI** (optional, recommended for starting the local server) or Apache (XAMPP)
* **Git**

---

## 🚀 Installation

Follow these steps to clone the repository and set up the development environment on your local machine:

### 1. Clone the repository
Open a terminal and run the following command to download the project:

```bash
git clone https://github.com/carol1234562/Backend-and-Symfony.git
cd Backend-and-Symfony
```

### 2. Install project dependencies
Run Composer to download and install all required packages and libraries into the `vendor/` folder:

```bash
composer install
```

### 3. Configure environment variables
The project uses `.env` files to manage configuration and database connections. You can create a `.env.local` file from the main `.env` file to adjust your local or remote server credentials:

```bash
cp .env .env.local
```

Verify that the `DATABASE_URL` variable points correctly to your MySQL database instance:

```dotenv
APP_ENV=dev
APP_SECRET=your_secret_key_here
DATABASE_URL="mysql://user:password@host:3306/database_name?serverVersion=8.0&charset=utf8mb4"
```

### 4. Prepare Database and Migrations
If you are using a new database, run the following Doctrine commands to create the schema and apply the corresponding migrations:

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
```

---

## 💻 Usage

### 1. Start the Local Server
You can start the development server by running any of the following options from the project root:

**Option A — With PHP's built-in server:**
```bash
php -S 127.0.0.1:8000 -t public
```

**Option B — With Symfony CLI:**
```bash
symfony server:start
```

The API will be available at: `http://127.0.0.1:8000`

---

### 2. API Usage Examples (CRUD Endpoints and Login)

All requests and responses use the `application/json` format. You can easily test them using **Postman**, **Insomnia**, or **cURL**.

#### 🔹 A. Create a new nurse (`POST /nurse`)
* **Status codes:** `201 Created` | `400 Bad Request`
* **Body Example (JSON):**
```json
{
    "name": "Laura",
    "last_name": "Martínez",
    "national_id": "45123456X",
    "license_number": "ENF-9988",
    "specialty": "Pediatric Emergency",
    "shift": "Morning",
    "email": "laura.martinez@hospital.com",
    "password": "SecurePassword2026!"
}
```

#### 🔹 B. Get full nurse list (`GET /nurse/index`)
* **Status code:** `200 OK`
* **URL:** `GET http://127.0.0.1:8000/nurse/index`

#### 🔹 C. Search nurse by ID (`GET /nurse/{id}`)
* **Status codes:** `200 OK` | `404 Not Found`
* **URL:** `GET http://127.0.0.1:8000/nurse/1`

#### 🔹 D. Search nurse by name (`GET /nurse/name/{name}`)
* **Status codes:** `200 OK` | `404 Not Found`
* **URL:** `GET http://127.0.0.1:8000/nurse/name/Laura`

#### 🔹 E. Update nurse data (`PUT /nurse/{id}`)
* **Status codes:** `200 OK` | `400 Bad Request` | `404 Not Found`
* **URL:** `PUT http://127.0.0.1:8000/nurse/1`
* **Body Example (JSON):**
```json
{
    "name": "Laura Sofía",
    "specialty": "Intensive Care Unit (ICU)",
    "shift": "Night"
}
```

#### 🔹 F. Delete a nurse (`DELETE /nurse/{id}`)
* **Status codes:** `200 OK` | `404 Not Found`
* **URL:** `DELETE http://127.0.0.1:8000/nurse/1`

#### 🔹 G. Nurse login (`POST /nurse/login`)
* **Status codes:** `200 OK` | `401 Unauthorized` | `400 Bad Request`
* **Body Example (JSON):**
```json
{
    "email": "laura.martinez@hospital.com",
    "password": "SecurePassword2026!"
}
```

---

## 🧪 Running Automated Tests (PHPUnit)

The project features automated functional tests located in `tests/Controller/NurseControllerTest.php` that verify all controller methods and their HTTP responses.

To run the test suite locally in your terminal, use the following command:

```bash
php vendor/phpunit/phpunit/phpunit
```

### Continuous Integration (GitHub Actions)
Every time a `push` is made or a `Pull Request` is opened toward the `main` branch, the workflow defined in `.github/workflows/ci.yml` runs automatically in an Ubuntu container with PHP 8.2 to ensure all tests pass before integrating new changes.

---

## 📂 Project Structure

```text
Backend-and-Symfony/
├── .github/
│   └── workflows/
│       └── ci.yml                  # Continuous Integration Pipeline (GitHub Actions)
├── config/                         # Symfony routing, services, and package configuration
├── migrations/                     # Database schema migrations (Doctrine)
├── public/
│   └── index.php                   # Application main entry point
├── src/
│   ├── Controller/
│   │   ├── LoginController.php     # Authentication controller
│   │   ├── NurseController.php     # Main CRUD operations controller
│   │   └── UserController.php      # Auxiliary user management
│   ├── Entity/
│   │   └── User.php                # Main ORM entity (Nurse)
│   └── Repository/
│       └── UserRepository.php      # User entity queries and persistence
├── tests/
│   └── Controller/
│       └── NurseControllerTest.php # Unit and functional tests with PHPUnit
├── .env                            # Default environment variables
├── composer.json                   # PHP dependency definition
├── phpunit.dist.xml                # PHPUnit configuration
└── README.md                       # Project technical documentation
```

---

## 👥 Authors and Development Team (PR05)

* **Carolina Solorzano** 
* **Johan Agredas** 
* **Sergi Pereda** 