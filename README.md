# Task Management API

## Docker Setup

This project includes a Docker setup for running the **task-management-api** using PHP-FPM, Nginx, and MySQL.

## Requirements

- Docker
- Docker Compose

## Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/ahmedSamirSoftwareEng/task-management-api.git
cd task-management-api
```

### 2. Setup Environment

```bash
cp .env.example .env
```

### 3. Build Docker Images

```bash
docker compose build
```

### 4. Run the Application

```bash
docker compose up
```

### 5. Install Dependencies

```bash
docker compose exec app composer install
```

### 6. Generate Application Key

```bash
docker compose exec app php artisan key:generate
```

### 7. Run Migrations

```bash
docker compose exec app php artisan migrate
```

### 8. Seed the Database
```bash
docker compose exec app php artisan db:seed
```

The application will be available at `http://localhost:8081`