# API App

A lightweight PHP API application using Doctrine DBAL and JWT authentication.

## Requirements

- PHP 7.4+
- Composer
- MySQL or compatible database

## Setup

1. Clone the repository.
2. Update `.env` with your database credentials.
3. Install dependencies:

   ```bash
   composer install
   ```

4. Start the PHP built-in server from the project root:

   ```bash
   php -S localhost:8000
   ```

## Configuration

The database settings are loaded from `.env` and used by `config/database.php`.

## Default `.env` values

```text
DB_HOST=localhost
DB_DATABASE=api
DB_USERNAME=root
DB_PASSWORD=root
DB_CHARSET=utf8mb4
REGISTRATION_ENABLED=true
```

## Notes

- The app bootstraps in `index.php`.
- The default timezone is set to `Asia/Kolkata`.
- Refer to [docs/developer-guide.md](docs/developer-guide.md) for the Generic CRUD APIs usage.
- Use the Postman Collections in [docs/postman_collection.json](docs/postman_collection.json) to test the APIs.
- Refer to [docs/troubleshooting.md](docs/troubleshooting.md) for common issues and solutions.