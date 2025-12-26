# CodeIgniter 4 2FA Backend API

This is the backend API for the Two-Factor Authentication (2FA) demonstration. It is built with **CodeIgniter 4** and provides a secure implementation of TOTP (Time-based One-Time Password) using `spomky-labs/otphp` and `bacon/bacon-qr-code`.

## Features

-   **User Login**: Authenticates users and checks 2FA status.
-   **Intelligent Flow**: Detects if 2FA is **enabled** or **uninitialized** (broken state) to guide the frontend.
-   **2FA Setup**: Generates TOTP secrets and QR codes (SVG format).
-   **2FA Verification**: Validates 6-digit codes to enable 2FA or complete a login.
-   **Unit & Feature Testing**: Includes test suite with PCOV code coverage.

## API Endpoints

### 1. `POST /auth/login`
Authenticates a user via email and password.

*   **Payload**: `{ "email": "...", "password": "..." }`
*   **Response**:
    *   **Success (No 2FA)**: `is_2fa_enabled: false`. Frontend should prompt for setup.
    *   **2FA Required**: `is_2fa_enabled: true`. Frontend should ask for code.
    *   **Broken State**: `is_2fa_enabled: true` BUT `is_2fa_initialized: false`. The user enabled 2FA but lost their secret (or it wasn't saved). Frontend must force re-setup.

### 2. `POST /auth/setup`
Initializes a new 2FA secret for the user.

*   **Payload**: `{ "user_id": 1 }`
*   **Response**: Returns `{ "secret": "...", "qr_code_svg": "..." }`.

### 3. `POST /auth/verify`
Finalizes setup or completes login by checking the code.

*   **Payload**: `{ "user_id": 1, "code": "123456" }`
*   **Response**: Success message if code is valid.

## Setup & Running

This project is best run via the root `docker-compose.yml`, but can be run locally.

### 1. Configuration
Ensure your `.env` file matches your database credentials.

### 2. Migrations & Seeding
```bash
php spark migrate
php spark db:seed UserSeeder
```
*Creates the `users` table and a `test@example.com / password` user.*

### 3. Testing
To run the test suite (Feature & Unit tests) with coverage:

```bash
composer test
```
*Requires `pcov` extension.*

## Project Structure

*   `app/Controllers/Auth2FA.php`: Main controller handling all 2FA logic.
*   `app/Models/UserModel.php`: User model with `secret_2fa` and `is_2fa_enabled` fields.
*   `app/Database/Migrations/`: Database schema definitions.
*   `tests/Feature/`: Integration tests for API endpoints.

## Dependencies

-   `codeigniter4/framework`: ^4.0
-   `spomky-labs/otphp`: ^11.0 (TOTP Logic)
-   `bacon/bacon-qr-code`: ^2.0 (QR Generation)
