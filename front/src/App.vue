<script setup>
import { ref } from "vue";
import api from "./services/api";

const email = ref("");
const password = ref("");
const message = ref("");
const error = ref("");

const handleLogin = async () => {
  message.value = "";
  error.value = "";

  try {
    const response = await api.post("/auth/login", {
      email: email.value,
      password: password.value,
    });

    // Check if 2FA is required or login successful
    if (response.data.message) {
      message.value = response.data.message;
    }

    console.log("Login response:", response.data);
  } catch (err) {
    console.error(err);
    if (err.response && err.response.data && err.response.data.messages) {
      error.value = JSON.stringify(err.response.data.messages);
    } else {
      error.value = "Login failed. Check console.";
    }
  }
};
</script>

<template>
  <div class="login-container">
    <h1>Login to CodeIgniter API</h1>

    <div class="form-group">
      <label>Email:</label>
      <input v-model="email" type="email" placeholder="test@example.com" />
    </div>

    <div class="form-group">
      <label>Password:</label>
      <input v-model="password" type="password" placeholder="password123" />
    </div>

    <button @click="handleLogin">Login</button>

    <div v-if="message" class="success">{{ message }}</div>
    <div v-if="error" class="error">{{ error }}</div>
  </div>
</template>

<style scoped>
.login-container {
  max-width: 400px;
  margin: 2rem auto;
  padding: 2rem;
  border: 1px solid #ccc;
  border-radius: 8px;
}
.form-group {
  margin-bottom: 1rem;
}
.form-group label {
  display: block;
  margin-bottom: 0.5rem;
}
.form-group input {
  width: 100%;
  padding: 0.5rem;
}
button {
  padding: 0.5rem 1rem;
  background-color: #dd4814;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
button:hover {
  background-color: #b0360f;
}
.success {
  margin-top: 1rem;
  color: green;
  background: #e6fffa;
  padding: 0.5rem;
}
.error {
  margin-top: 1rem;
  color: red;
  background: #ffe6e6;
  padding: 0.5rem;
}
</style>
