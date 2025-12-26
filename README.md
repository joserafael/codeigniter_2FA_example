# Docker Development Environment for CodeIgniter 4 + Vue.js

This repository provides a complete Docker configuration for developing modern web applications with a **CodeIgniter 4** backend and a **Vue.js 3** frontend. The environment is fully containerized and includes PHP, Nginx, Node.js, MySQL, Redis, MailHog, and Redis Commander.

## Features

*   **PHP 8.3-FPM**: Latest PHP version for the backend API.
*   **Vue.js 3 + Vite**: Modern frontend development stack with Hot Module Replacement (HMR).
*   **Nginx**: Web server for the backend.
*   **MySQL 8.0**: Database server.
*   **Redis**: Caching and session management.
*   **MailHog**: Email capture and testing tool.
*   **Redis Commander**: Web interface for managing Redis.
*   **Automatic Proxying**: Frontend (`localhost:5173`) automatically proxies API requests to the backend (`localhost:8080`).

## Prerequisites

Before starting, make sure you have the following installed:

*   Docker
*   Docker Compose

## Architecture Overview

The project consists of two main applications running in parallel containers:

1.  **Backend (`app` service)**: CodeIgniter 4 application serving a REST API.
2.  **Frontend (`front` service)**: Vue.js 3 application served via Vite.

## Getting Started

### 1. Start the Environment

Run the following command in the root directory:

```bash
docker-compose up -d --build
```

This will build the images and start all services (Backend, Frontend, Database, etc.).

### 2. Database and Migrations

Once the containers are up, run the database migrations and seeders to set up your initial data:

```bash
# Run migrations
docker-compose exec app php spark migrate

# Run seeder (creates test user)
docker-compose exec app php spark db:seed UserSeeder
```

### 3. Access the Application

*   **Frontend (Vue.js)**: [http://localhost:5173](http://localhost:5173)
    *   *This is the main entry point for users.*
*   **Backend API (CodeIgniter)**: [http://localhost:8080](http://localhost:8080)
*   **MailHog**: [http://localhost:8025](http://localhost:8025)
*   **Redis Commander**: [http://localhost:8081](http://localhost:8081)

## Development Workflow

### Backend Development (CodeIgniter)

Navigate to the `codeigniter_2FA_example` folder. Any changes you make to PHP files will be immediately reflected (except for configuration changes that might require a container restart).

**Running Commands:**
```bash
# Run tests
docker-compose exec app composer test

# Create a new controller
docker-compose exec app php spark make:controller MyController
```

### Frontend Development (Vue.js)

Navigate to the `front` folder. The Vite dev server is running in the `front` container and supports Hot Module Replacement (HMR). Changes to `.vue` or `.js` files will instantly update the browser.

**Running Commands:**
```bash
# Install new package
docker-compose exec front npm install package-name
```

## Two-Factor Authentication (2FA) Demo

This project includes a fully functional 2FA flow:

1.  **Login**: Use the default credentials:
    *   **Email**: `test@example.com`
    *   **Password**: `password`
2.  **Setup**: If 2FA is not enabled, you will be automatically redirected to the setup screen.
3.  **Verify**: Scan the QR code with Google Authenticator or Authy, enter the code, and your account will be secured.

## Project Structure

*   `codeigniter_2FA_example/`: The CodeIgniter 4 backend application.
*   `front/`: The Vue.js 3 frontend application.
*   `docker-compose.yml`: Orchestrates all services.
*   `Dockerfile.php`: Backend Docker image definition.
*   `docker/`: Additional Docker configurations (Nginx, etc.).

## Services Configuration

| Service | Host Port | Internal Port | Description |
| :--- | :--- | :--- | :--- |
| **Frontend** | `5173` | `5173` | Vue.js App |
| **Backend** | `8080` | `80` | CodeIgniter API |
| **MySQL** | `33061` | `3306` | Database |
| **Redis** | `63791` | `6379` | Cache |
| **MailHog** | `8025` | `8025` | Email Testing |
| **Redis GUI**| `8081` | `8081` | Redis Commander |

## Contributing

Feel free to open issues or pull requests for improvements.