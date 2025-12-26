<script setup>
import { ref } from "vue";
import api from "./services/api";

// State
const step = ref("login"); // 'login', 'setup', 'verify', 'authenticated'

// Data
const email = ref("");
const password = ref("");
const userId = ref(null);
const qrCode = ref("");
const mfaSecret = ref("");
const verificationCode = ref("");
const message = ref("");
const error = ref("");

const startSetup = async () => {
  try {
    const response = await api.post("/auth/setup", {
      user_id: userId.value,
    });

    // SVG is base64 encoded
    qrCode.value = `data:image/svg+xml;base64,${response.data.qr_code_svg}`;
    mfaSecret.value = response.data.secret;
    step.value = "setup";
    message.value = "2FA Setup is required. Scan the QR code below.";
  } catch (err) {
    console.error(err);
    error.value = "Setup initialization failed. Please try again.";
  }
};

const handleLogin = async () => {
  message.value = "";
  error.value = "";

  try {
    const response = await api.post("/auth/login", {
      email: email.value,
      password: password.value,
    });

    userId.value = response.data.user_id;

    // Check if 2FA is enabled AND initialized
    if (
      response.data.is_2fa_enabled &&
      response.data.is_2fa_initialized !== false
    ) {
      // 2FA is enabled and seems valid
      step.value = "verify";
      message.value = "Please enter your 2FA code.";
    } else {
      // 2FA is NOT enabled OR not initialized properly. Enforce setup immediately.
      await startSetup();
    }
  } catch (err) {
    console.error(err);
    error.value = err.response?.data?.messages?.error || "Login failed";
  }
};

const handleVerify = async () => {
  try {
    const response = await api.post("/auth/verify", {
      user_id: userId.value,
      code: verificationCode.value,
    });

    step.value = "authenticated";
    message.value = response.data.message;
    error.value = "";
  } catch (err) {
    console.error(err);
    const errorMessage = err.response?.data?.messages?.error;

    // If the backend says 2FA isn't setup yet, force setup now
    if (errorMessage && errorMessage.includes("Call /setup first")) {
      // Ensure we have a user ID if possible (fallback to what's in state)
      userId.value = userId.value || err.response?.data?.data?.user_id;
      await startSetup();
      error.value =
        "Your 2FA access was reset or not found. Please setup again.";
      return;
    }

    error.value = errorMessage || "Verification failed";
  }
};
</script>

<template>
  <div class="container">
    <div class="card">
      <div v-if="step === 'login'">
        <h1>Login</h1>
        <div class="form-group">
          <label>Email</label>
          <input v-model="email" type="email" placeholder="test@example.com" />
        </div>
        <div class="form-group">
          <label>Password</label>
          <input v-model="password" type="password" placeholder="password" />
        </div>
        <button @click="handleLogin">Login</button>
      </div>

      <div v-else-if="step === 'setup'">
        <h1>Setup 2FA</h1>
        <p class="subtitle">Complete setup to access your account.</p>

        <div class="qr-container">
          <img :src="qrCode" alt="2FA QR Code" />
        </div>
        <p class="secret">Secret: {{ mfaSecret }}</p>

        <div class="form-group">
          <label>Enter Code from App</label>
          <input v-model="verificationCode" type="text" placeholder="123456" />
        </div>
        <button @click="handleVerify">Verify & Enable</button>
      </div>

      <div v-else-if="step === 'verify'">
        <h1>Two-Factor Auth</h1>
        <p class="subtitle">Enter the code from your authenticator app.</p>
        <div class="form-group">
          <input v-model="verificationCode" type="text" placeholder="000000" />
        </div>
        <button @click="handleVerify">Verify</button>
      </div>

      <div v-else-if="step === 'authenticated'">
        <h1>Welcome!</h1>
        <p>You are securely logged in.</p>
        <button
          class="secondary"
          @click="
            step = 'login';
            email = '';
            password = '';
            userId = null;
            verificationCode = '';
          "
        >
          Logout
        </button>
      </div>

      <div v-if="message" class="alert info">{{ message }}</div>
      <div v-if="error" class="alert error">{{ error }}</div>
    </div>
  </div>
</template>

<style scoped>
.container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
}
.card {
  width: 100%;
  max-width: 400px;
  padding: 2rem;
  border: 1px solid #eee;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  background: white;
}
h1 {
  margin-top: 0;
  color: #333;
  margin-bottom: 0.5rem;
}
.subtitle {
  margin-top: 0;
  color: #666;
  font-size: 0.95rem;
  margin-bottom: 1.5rem;
}
.form-group {
  margin-bottom: 1rem;
}
label {
  display: block;
  margin-bottom: 0.5rem;
  color: #555;
}
input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}
button {
  width: 100%;
  padding: 0.75rem;
  background-color: #dd4814;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  margin-top: 0.5rem;
}
button.secondary {
  background-color: #6c757d;
}
button:hover {
  opacity: 0.9;
}
.qr-container {
  text-align: center;
  margin: 1rem 0;
  background: #f9f9f9;
  padding: 1rem;
  border-radius: 8px;
}
.qr-container img {
  max-width: 200px;
  display: block;
  margin: 0 auto;
}
.secret {
  font-family: monospace;
  text-align: center;
  color: #555;
  word-break: break-all;
  margin-bottom: 1.5rem;
}
.actions {
  display: flex;
  gap: 1rem;
}
.alert {
  margin-top: 1rem;
  padding: 0.75rem;
  border-radius: 4px;
  text-align: center;
}
.info {
  background-color: #e3f2fd;
  color: #0d47a1;
}
.error {
  background-color: #ffebee;
  color: #c62828;
}
</style>
