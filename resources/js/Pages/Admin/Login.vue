<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => form.post('/dashboard/login');
</script>

<template>
    <Head title="Login Pengurus - PCM Simo" />

    <v-app class="font-poppins">
        <v-main class="login-page d-flex align-center justify-center">
            <div class="login-container">
                <!-- Left Panel: Decorative -->
                <div class="left-panel">
                    <!-- Glassmorphic blobs -->
                    <div class="glass-blob blob-1"></div>
                    <div class="glass-blob blob-2"></div>
                    <div class="glass-blob blob-3"></div>

                    <!-- Center branding on left panel -->
                    <div class="left-brand">
                        <img src="/images/logo-pcmsimo-white.png" alt="PCM Simo" style="height: 100px; opacity: 0.95; filter: drop-shadow(0 2px 8px rgba(0,0,0,0.15));" />
                    </div>
                </div>

                <!-- Right Panel: Form -->
                <div class="right-panel">
                    <div class="form-content">
                        <!-- Heading -->
                        <h1 class="login-title">Masuk ke Portal</h1>
                        <p class="login-subtitle">Selamat datang! Silakan masukkan detail akun Anda.</p>

                        <!-- Form -->
                        <form @submit.prevent="submit" class="mt-7">
                            <!-- Email -->
                            <div class="field-group">
                                <label class="field-label">Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="field-input"
                                    placeholder="Masukkan email Anda"
                                    autocomplete="email"
                                />
                                <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                            </div>

                            <!-- Password -->
                            <div class="field-group">
                                <label class="field-label">Kata Sandi</label>
                                <div class="field-input-wrapper">
                                    <input
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        class="field-input"
                                        placeholder="Masukkan kata sandi"
                                        autocomplete="current-password"
                                    />
                                    <button type="button" class="toggle-pw" @click="showPassword = !showPassword">
                                        <v-icon size="18" color="grey-darken-1">
                                            {{ showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline' }}
                                        </v-icon>
                                    </button>
                                </div>
                                <p v-if="form.errors.password" class="field-error">{{ form.errors.password }}</p>
                            </div>

                            <!-- Remember -->
                            <div class="d-flex align-center justify-space-between mb-6">
                                <label class="remember-label">
                                    <input type="checkbox" v-model="form.remember" class="remember-check" />
                                    <span>Ingat saya</span>
                                </label>
                            </div>

                            <!-- Submit -->
                            <button
                                type="submit"
                                class="submit-btn"
                                :disabled="form.processing"
                            >
                                <span v-if="!form.processing">Masuk</span>
                                <v-progress-circular v-else indeterminate size="20" width="2" color="white" />
                            </button>
                        </form>

                        <!-- Back Link -->
                        <div class="text-center mt-6">
                            <Link href="/" class="back-link">
                                &larr; Kembali ke halaman utama
                            </Link>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="right-footer">
                        &copy; {{ new Date().getFullYear() }} PCM Simo, Boyolali
                    </div>
                </div>
            </div>
        </v-main>
    </v-app>
</template>

<style scoped>
/* ── Page ── */
.login-page {
    background: #f0f1f3;
    min-height: 100dvh;
    padding: 24px;
}

/* ── Container ── */
.login-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    width: 100%;
    max-width: 920px;
    min-height: 580px;
    border-radius: 28px;
    overflow: hidden;
    background: #fff;
    box-shadow:
        0 1px 2px rgba(0, 0, 0, 0.04),
        0 4px 16px rgba(0, 0, 0, 0.06);
    animation: cardIn 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}

@keyframes cardIn {
    from { opacity: 0; transform: scale(0.97) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

/* ── Left Panel ── */
.left-panel {
    position: relative;
    background: linear-gradient(145deg, #006837 0%, #00874a 40%, #10b981 100%);
    overflow: hidden;
    display: flex;
    align-items: flex-end;
    padding: 32px;
}

/* ── Glassmorphic Blobs ── */
.glass-blob {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(0px);
    border: 1px solid rgba(255, 255, 255, 0.12);
}

.blob-1 {
    width: 320px;
    height: 320px;
    top: -40px;
    left: -60px;
    background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.03));
    animation: float1 20s ease-in-out infinite;
}

.blob-2 {
    width: 240px;
    height: 240px;
    bottom: 60px;
    right: -40px;
    background: radial-gradient(circle at 60% 40%, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.03));
    border-color: rgba(245, 158, 11, 0.12);
    animation: float2 16s ease-in-out infinite;
}

.blob-3 {
    width: 180px;
    height: 180px;
    top: 45%;
    left: 30%;
    background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.02));
    animation: float3 22s ease-in-out infinite;
}

@keyframes float1 {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    33% { transform: translate(15px, 20px) rotate(5deg); }
    66% { transform: translate(-10px, -15px) rotate(-3deg); }
}

@keyframes float2 {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    33% { transform: translate(-20px, -10px) rotate(-5deg); }
    66% { transform: translate(10px, 15px) rotate(3deg); }
}

@keyframes float3 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(20px, -20px) scale(1.08); }
}

.left-brand {
    position: absolute;
    z-index: 1;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

/* ── Right Panel ── */
.right-panel {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 0;
    background: #fff;
}

.form-content {
    padding: 48px 44px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.right-footer {
    padding: 16px 44px;
    font-size: 12px;
    color: #a0a0a0;
    text-align: center;
}

/* ── Typography ── */
.login-title {
    font-size: 26px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 6px 0;
    letter-spacing: -0.5px;
    line-height: 1.2;
}

.login-subtitle {
    font-size: 14px;
    color: #888;
    margin: 0;
    font-weight: 400;
}

/* ── Form Fields ── */
.field-group {
    margin-bottom: 20px;
}

.field-label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #444;
    margin-bottom: 6px;
}

.field-input-wrapper {
    position: relative;
}

.field-input {
    width: 100%;
    height: 46px;
    padding: 0 16px;
    border: 1.5px solid #e0e0e0;
    border-radius: 12px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    color: #1a1a1a;
    background: #fff;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.field-input::placeholder {
    color: #bbb;
    font-weight: 400;
}

.field-input:focus {
    border-color: #006837;
    box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.08);
}

.field-input-wrapper .field-input {
    padding-right: 46px;
}

.toggle-pw {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    opacity: 0.5;
    transition: opacity 0.2s ease;
}

.toggle-pw:hover {
    opacity: 0.8;
}

.field-error {
    font-size: 12px;
    color: #dc2626;
    margin: 4px 0 0 4px;
}

/* ── Remember ── */
.remember-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #666;
    cursor: pointer;
    user-select: none;
}

.remember-check {
    width: 16px;
    height: 16px;
    accent-color: #006837;
    border-radius: 4px;
    cursor: pointer;
}

/* ── Submit Button ── */
.submit-btn {
    width: 100%;
    height: 48px;
    border: none;
    border-radius: 12px;
    background: #006837;
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s cubic-bezier(0.22, 1, 0.36, 1);
}

.submit-btn:hover {
    background: #005a2f;
    box-shadow: 0 4px 14px rgba(0, 104, 55, 0.3);
    transform: translateY(-1px);
}

.submit-btn:active {
    transform: translateY(0);
    box-shadow: none;
}

.submit-btn:disabled {
    opacity: 0.7;
    cursor: wait;
}

/* ── Back Link ── */
.back-link {
    font-size: 13px;
    color: #999;
    text-decoration: none;
    transition: color 0.2s ease;
}

.back-link:hover {
    color: #006837;
}

/* ── Responsive ── */
@media (max-width: 768px) {
    .login-page {
        padding: 0;
    }

    .login-container {
        grid-template-columns: 1fr;
        max-width: 100%;
        min-height: 100dvh;
        border-radius: 0;
        box-shadow: none;
    }

    .left-panel {
        display: none;
    }

    .form-content {
        padding: 40px 28px;
    }

    .right-footer {
        padding: 16px 28px;
    }
}
</style>
