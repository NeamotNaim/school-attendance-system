<template>
  <div class="login-container">
    <div class="login-wrapper">
      <div class="login-illustration">
        <div class="illustration-content">
          <svg class="illustration-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
          </svg>
          <h1 class="illustration-title">School Attendance System</h1>
          <p class="illustration-subtitle">Manage student attendance efficiently</p>
        </div>
      </div>
      <div class="login-card">
        <div class="login-header">
          <h2>Welcome Back</h2>
          <p>Sign in to your account</p>
        </div>
        <form @submit.prevent="handleLogin" class="login-form">
          <div class="form-group">
            <label for="email">Email Address</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="Enter your email"
                required
                autocomplete="email"
              />
            </div>
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
              <input
                id="password"
                v-model="form.password"
                type="password"
                placeholder="Enter your password"
                required
                autocomplete="current-password"
              />
            </div>
          </div>
          <button type="submit" :disabled="loading" class="btn-login">
            <span v-if="!loading">Sign In</span>
            <span v-else class="loading-text">
              <span class="spinner-small"></span>
              Signing in...
            </span>
          </button>
          <div v-if="error" class="error-message">
            <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ error }}</span>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

const router = useRouter();
const form = ref({
  email: '',
  password: '',
});
const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    const response = await api.post('/login', form.value);
    localStorage.setItem('auth_token', response.data.access_token);
    localStorage.setItem('user', JSON.stringify(response.data.user));
    router.push('/');
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed. Please check your credentials.';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  padding: 2rem;
}

.login-wrapper {
  display: grid;
  grid-template-columns: 1fr 1fr;
  max-width: 1000px;
  width: 100%;
  background: var(--bg-secondary);
  border-radius: 16px;
  box-shadow: var(--shadow-xl);
  overflow: hidden;
}

.login-illustration {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: white;
}

.illustration-content {
  text-align: center;
}

.illustration-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 2rem;
  stroke: white;
}

.illustration-title {
  font-size: 2rem;
  font-weight: 700;
  color: white;
  margin-bottom: 0.5rem;
}

.illustration-subtitle {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.9);
}

.login-card {
  padding: 3rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.login-header {
  margin-bottom: 2rem;
}

.login-header h2 {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.login-header p {
  color: var(--text-secondary);
  font-size: 0.9375rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 1rem;
  width: 20px;
  height: 20px;
  color: var(--text-muted);
  pointer-events: none;
}

.input-wrapper input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 3rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.9375rem;
  background: var(--bg-secondary);
  color: var(--text-primary);
  transition: all 0.2s ease;
}

.input-wrapper input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.input-wrapper input::placeholder {
  color: var(--text-muted);
}

.btn-login {
  width: 100%;
  padding: 0.875rem;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 0.5rem;
}

.btn-login:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: var(--shadow-lg);
}

.btn-login:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.loading-text {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  color: var(--danger);
  font-size: 0.875rem;
}

.error-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

@media (max-width: 768px) {
  .login-wrapper {
    grid-template-columns: 1fr;
  }
  
  .login-illustration {
    display: none;
  }
  
  .login-card {
    padding: 2rem;
  }
}
</style>
