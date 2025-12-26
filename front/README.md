# Vue.js 2FA Frontend

This is the frontend client for the CodeIgniter 4 Two-Factor Authentication (2FA) example. It is built with **Vue 3** and **Vite**.

## Overview

This application demonstrates a secure authentication flow:
1.  **Login**: User authenticates with email and password.
2.  **Check 2FA Status**: The app automatically checks if 2FA is required.
3.  **Setup Flow**: If 2FA is not enabled (or not initialized), the user is forced into a setup wizard to scan a QR code.
4.  **Verification**: Users must enter a valid Time-based One-Time Password (TOTP) from their authenticator app (Google Authenticator, Authy, etc.) to complete login.

## Running with Docker (Recommended)

This project is integrated into the main `docker-compose.yml`. It runs automatically alongside the backend.

### Start the Application
To start the entire stack (Frontend + Backend + Database):

```bash
docker-compose up -d
```

### Access

*   **Frontend**: [http://localhost:5173](http://localhost:5173)
*   **Backend API**: [http://localhost:8080](http://localhost:8080)

The frontend is configured to proxy API requests (`/auth/...`) to the backend via the internal Docker network, resolving CORS issues automatically.

## Local Development (Without Docker)

If you prefer to run the frontend outside of Docker:

1.  **Install Dependencies**
    ```bash
    npm install
    ```

2.  **Start Dev Server**
    ```bash
    npm run dev
    ```

*Note: You may need to adjust `vite.config.js` proxy target if your backend is running on a different port than the Docker setup.*

## Project Structure

*   **`src/App.vue`**: Main application logic handling the state machine for Login -> Setup -> Verify.
*   **`src/services/api.js`**: Axios instance configured for API communication.
*   **`vite.config.js`**: Vite configuration including the API proxy setup.

## Technologies

*   Vue.js 3 (Composition API)
*   Vite
*   Axios
*   CSS3 (Scoped Styles)
