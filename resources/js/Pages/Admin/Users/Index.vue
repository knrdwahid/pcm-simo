<script setup>
import showAlert from '@/Utils/sweetalert';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    users: { type: Array, default: () => [] },
    superAdminInfo: { type: Object, default: null },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user ?? {});

const dialog = ref(false);
const isEdit = ref(false);
const selectedUser = ref(null);
const showPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    role: 'tim',
    password: '',
});

// Hanya peran Admin dan Tim Redaksi yang dapat dipilih atau dibuat
const roleOptions = [
    {
        value: 'tim',
        title: 'Tim Redaksi',
        desc: 'Akses menulis dan mengelola berita serta kategori.',
        icon: 'mdi-feather',
    },
    {
        value: 'admin',
        title: 'Admin',
        desc: 'Akses penuh konten berita, kategori, dan kelola pengguna.',
        icon: 'mdi-shield-account',
    },
];

const openCreate = () => {
    isEdit.value = false;
    selectedUser.value = null;
    form.reset();
    form.clearErrors();
    form.role = 'tim';
    showPassword.value = false;
    dialog.value = true;
};

const openEdit = (user) => {
    isEdit.value = true;
    selectedUser.value = user;
    form.reset();
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
    form.password = '';
    showPassword.value = false;
    dialog.value = true;
};

const submit = () => {
    if (isEdit.value) {
        form.put(`/dashboard/users/${selectedUser.value.id}`, {
            onSuccess: () => {
                dialog.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/dashboard/users', {
            onSuccess: () => {
                dialog.value = false;
                form.reset();
            },
        });
    }
};

const deleteUser = async (user) => {
    if (user.id === currentUser.value.id) {
        showAlert.warning('Tidak Diizinkan', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
        return;
    }

    const confirmed = await showAlert.confirm(
        'Hapus Akun Pengguna?',
        `Apakah Anda yakin ingin menghapus akun "${user.name}" (${user.email})? Tindakan ini tidak dapat dibatalkan.`,
        'Ya, Hapus Akun'
    );

    if (confirmed) {
        form.delete(`/dashboard/users/${user.id}`);
    }
};

const getRoleBadge = (role) => {
    if (role === 'admin') {
        return {
            label: 'Admin',
            class: 'badge-admin',
            icon: 'mdi-shield-account',
        };
    }
    return {
        label: 'Tim Redaksi',
        class: 'badge-tim',
        icon: 'mdi-feather',
    };
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Manajemen Pengguna - Admin PCM Simo" />

    <div class="user-page-container">
        <!-- Top Page Header -->
        <div class="page-header">
            <div>
                <div class="d-flex align-center ga-2 mb-1">
                    <h1 class="page-title">Manajemen Pengguna</h1>
                    <span class="total-badge">{{ users.length }} Pengguna</span>
                </div>
                <p class="page-subtitle">Kelola akun operasional pengurus, peran (role), dan kredensial login portal</p>
            </div>

            <button type="button" class="btn-create" @click="openCreate">
                <v-icon size="18" class="mr-1">mdi-account-plus-outline</v-icon>
                <span>Tambah Pengguna</span>
            </button>
        </div>

        <!-- Super Admin Status Banner (Hanya terlihat saat yang login adalah Super Admin) -->
        <div v-if="superAdminInfo" class="superadmin-banner mb-6">
            <div class="d-flex align-center ga-3">
                <div class="superadmin-banner-icon">
                    <v-icon size="22" color="#f59e0b">mdi-shield-crown</v-icon>
                </div>
                <div class="flex-1">
                    <div class="d-flex align-center ga-2 mb-1">
                        <span class="font-bold text-sm text-slate-800">Otoritas Tertinggi: {{ superAdminInfo.name }}</span>
                        <span class="superadmin-badge-lock">Terkunci & Tidak Terlihat</span>
                    </div>
                    <p class="text-xs text-slate-600 m-0 leading-relaxed">
                        Akun Super Admin (<strong>{{ superAdminInfo.email }}</strong>) adalah otoritas tertinggi sistem yang berstatus permanen, tidak dapat diubah oleh siapapun, dan otomatis disembunyikan dari daftar pengguna yang dapat dilihat oleh Admin maupun Tim.
                    </p>
                </div>
            </div>
        </div>

        <!-- Users Table Card -->
        <div class="table-container">
            <div class="table-scroll">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th class="col-num">#</th>
                            <th class="col-user">Pengguna</th>
                            <th class="col-email">Email</th>
                            <th class="col-role">Peran / Role</th>
                            <th class="col-date">Terdaftar</th>
                            <th class="col-action text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(u, index) in users" :key="u.id" class="table-row">
                            <!-- Number -->
                            <td class="col-num">{{ index + 1 }}</td>

                            <!-- User Info -->
                            <td class="col-user">
                                <div class="user-cell">
                                    <div class="user-avatar" :class="`avatar-${u.role}`">
                                        {{ u.name ? u.name.charAt(0).toUpperCase() : 'U' }}
                                    </div>
                                    <div>
                                        <div class="user-name">
                                            {{ u.name }}
                                            <span v-if="u.id === currentUser.id" class="self-badge">Anda</span>
                                        </div>
                                        <div class="user-subtext">ID Akun: #{{ u.id }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Email -->
                            <td class="col-email">
                                <div class="email-cell">
                                    <v-icon size="15" color="#64748b" class="mr-1">mdi-email-outline</v-icon>
                                    <span>{{ u.email }}</span>
                                </div>
                            </td>

                            <!-- Role Badge -->
                            <td class="col-role">
                                <span :class="['role-badge', getRoleBadge(u.role).class]">
                                    <v-icon size="14" class="mr-1">{{ getRoleBadge(u.role).icon }}</v-icon>
                                    {{ getRoleBadge(u.role).label }}
                                </span>
                            </td>

                            <!-- Date Registered -->
                            <td class="col-date">
                                <span class="date-text">
                                    <v-icon size="13" color="#94a3b8" class="mr-1">mdi-calendar-blank-outline</v-icon>
                                    {{ formatDate(u.created_at) }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="col-action text-right">
                                <div class="actions-group justify-end">
                                    <!-- Edit Button -->
                                    <button
                                        type="button"
                                        class="action-btn action-btn--edit"
                                        title="Sunting Data Pengguna"
                                        @click="openEdit(u)"
                                    >
                                        <v-icon size="16">mdi-pencil-outline</v-icon>
                                    </button>

                                    <!-- Delete Button (Disabled for self) -->
                                    <button
                                        type="button"
                                        class="action-btn action-btn--delete"
                                        :disabled="u.id === currentUser.id"
                                        :title="u.id === currentUser.id ? 'Tidak bisa menghapus akun Anda sendiri' : 'Hapus Akun Pengguna'"
                                        @click="deleteUser(u)"
                                    >
                                        <v-icon size="16">mdi-trash-can-outline</v-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="users.length === 0">
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon-circle">
                                        <v-icon size="36" color="#94a3b8">mdi-account-group-outline</v-icon>
                                    </div>
                                    <h3 class="empty-title">Belum ada akun pengguna operasional</h3>
                                    <p class="empty-desc">Tambahkan akun Admin atau Tim Redaksi untuk memberikan akses ke portal.</p>
                                    <button type="button" class="btn-create" @click="openCreate">
                                        <v-icon size="16" class="mr-1">mdi-plus</v-icon>
                                        Tambah Pengguna
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div v-if="users.length > 0" class="table-footer">
                <div class="footer-summary">
                    Total <span class="footer-highlight">{{ users.length }}</span> pengguna terdaftar
                </div>
            </div>
        </div>

        <!-- Create / Edit User Modal Dialog -->
        <v-dialog v-model="dialog" max-width="500" persistent>
            <v-card rounded="xl" class="pa-6 border border-slate-100 modal-card">
                <!-- Dialog Header -->
                <div class="d-flex align-center justify-space-between mb-4 pb-3 border-b border-slate-100">
                    <div class="d-flex align-center ga-2">
                        <div class="modal-icon-circle">
                            <v-icon size="20" color="#006837">
                                {{ isEdit ? 'mdi-account-edit-outline' : 'mdi-account-plus-outline' }}
                            </v-icon>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-800 leading-tight">
                                {{ isEdit ? 'Sunting Data Pengguna' : 'Tambah Pengguna Baru' }}
                            </h2>
                            <p class="text-xs text-slate-500 m-0">
                                {{ isEdit ? 'Perbarui informasi nama, email, role, atau ganti password' : 'Lengkapi formulir untuk menambahkan akun pengurus baru' }}
                            </p>
                        </div>
                    </div>
                    <button type="button" class="close-btn" @click="dialog = false">
                        <v-icon size="18">mdi-close</v-icon>
                    </button>
                </div>

                <!-- Dialog Form -->
                <v-form @submit.prevent="submit">
                    <!-- Nama Lengkap -->
                    <div class="mb-4">
                        <label class="input-label">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <v-text-field
                            v-model="form.name"
                            density="compact"
                            variant="outlined"
                            placeholder="Contoh: Ahmad Fauzi"
                            :error-messages="form.errors.name"
                            hide-details="auto"
                            prepend-inner-icon="mdi-account-outline"
                        />
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label class="input-label">
                            Alamat Email <span class="text-rose-500">*</span>
                        </label>
                        <v-text-field
                            v-model="form.email"
                            type="email"
                            density="compact"
                            variant="outlined"
                            placeholder="Contoh: user@pcmsimo.or.id"
                            :error-messages="form.errors.email"
                            hide-details="auto"
                            prepend-inner-icon="mdi-email-outline"
                        />
                    </div>

                    <!-- Role Selection -->
                    <div class="mb-4">
                        <label class="input-label">
                            Peran / Hak Akses (Role) <span class="text-rose-500">*</span>
                        </label>
                        <div class="role-selector-grid">
                            <div
                                v-for="opt in roleOptions"
                                :key="opt.value"
                                :class="['role-option-card', { 'role-option-card--active': form.role === opt.value }]"
                                @click="form.role = opt.value"
                            >
                                <div class="d-flex align-center justify-space-between mb-1">
                                    <div class="d-flex align-center ga-1 font-bold text-xs">
                                        <v-icon size="16">{{ opt.icon }}</v-icon>
                                        <span>{{ opt.title }}</span>
                                    </div>
                                    <v-icon v-if="form.role === opt.value" size="16" color="#006837">mdi-check-circle</v-icon>
                                </div>
                                <p class="role-option-desc">{{ opt.desc }}</p>
                            </div>
                        </div>
                        <div v-if="form.errors.role" class="text-xs text-rose-500 mt-1">
                            {{ form.errors.role }}
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-5">
                        <label class="input-label">
                            Password Login
                            <span v-if="!isEdit" class="text-rose-500">*</span>
                            <span v-else class="text-xs text-slate-400 font-normal ml-1">(Kosongkan jika tidak diubah)</span>
                        </label>
                        <v-text-field
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            density="compact"
                            variant="outlined"
                            :placeholder="isEdit ? 'Masukkan password baru jika ingin mengganti' : 'Minimal 6 karakter'"
                            :error-messages="form.errors.password"
                            hide-details="auto"
                            prepend-inner-icon="mdi-lock-outline"
                            :append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                            @click:append-inner="showPassword = !showPassword"
                        />
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-center justify-end ga-3 pt-2 border-t border-slate-100">
                        <button
                            type="button"
                            class="btn-cancel"
                            @click="dialog = false"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="btn-submit"
                            :disabled="form.processing"
                        >
                            <v-progress-circular
                                v-if="form.processing"
                                indeterminate
                                size="16"
                                width="2"
                                color="white"
                                class="mr-2"
                            />
                            <span>{{ isEdit ? 'Simpan Perubahan' : 'Tambah Pengguna' }}</span>
                        </button>
                    </div>
                </v-form>
            </v-card>
        </v-dialog>
    </div>
</template>

<style scoped>
.user-page-container {
    padding: 24px;
    max-width: 1200px;
    margin: 0 auto;
}

/* Page Header */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.page-title {
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.02em;
    margin: 0;
}

.total-badge {
    background: #e2e8f0;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
}

.page-subtitle {
    font-size: 13.5px;
    color: #64748b;
    margin: 0;
}

.btn-create {
    display: inline-flex;
    align-items: center;
    background: #006837;
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 600;
    padding: 10px 18px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 104, 55, 0.25);
    transition: all 0.2s ease;
}

.btn-create:hover {
    background: #00502b;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(0, 104, 55, 0.35);
}

/* Superadmin Banner */
.superadmin-banner {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 14px 18px;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.08);
}

.superadmin-banner-icon {
    width: 40px;
    height: 40px;
    background: #ffffff;
    border: 1px solid #fde68a;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.superadmin-badge-lock {
    background: #f59e0b;
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 10px;
    letter-spacing: 0.2px;
}

/* Table Container */
.table-container {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.table-scroll {
    overflow-x: auto;
}

.user-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 13.5px;
}

.user-table thead {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.user-table th {
    padding: 14px 16px;
    font-weight: 600;
    color: #475569;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-row {
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s ease;
}

.table-row:hover {
    background: #f8fafc;
}

.user-table td {
    padding: 14px 16px;
    vertical-align: middle;
}

/* Columns */
.col-num {
    width: 48px;
    color: #94a3b8;
    font-weight: 500;
    text-align: center;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
}

.avatar-admin {
    background: #dcfce7;
    color: #15803d;
    border: 2px solid #bbf7d0;
}

.avatar-tim {
    background: #e0f2fe;
    color: #0369a1;
    border: 2px solid #bae6fd;
}

.user-name {
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 6px;
}

.self-badge {
    background: #dcfce7;
    color: #166534;
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 12px;
    border: 1px solid #bbf7d0;
}

.user-subtext {
    font-size: 11px;
    color: #94a3b8;
}

.email-cell {
    display: flex;
    align-items: center;
    color: #475569;
    font-family: monospace;
    font-size: 13px;
}

/* Role Badges */
.role-badge {
    display: inline-flex;
    align-items: center;
    font-size: 11.5px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
}

.badge-admin {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #dcfce7;
}

.badge-tim {
    background: #f0f9ff;
    color: #0369a1;
    border: 1px solid #e0f2fe;
}

.date-text {
    display: flex;
    align-items: center;
    color: #64748b;
    font-size: 12.5px;
}

/* Actions Group */
.actions-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid transparent;
    cursor: pointer;
    background: transparent;
    transition: all 0.2s ease;
}

.action-btn--edit {
    color: #0284c7;
    background: #f0f9ff;
    border-color: #e0f2fe;
}

.action-btn--edit:hover:not(:disabled) {
    background: #0284c7;
    color: #ffffff;
}

.action-btn--delete {
    color: #e11d48;
    background: #fff1f2;
    border-color: #ffe4e6;
}

.action-btn--delete:hover:not(:disabled) {
    background: #e11d48;
    color: #ffffff;
}

.action-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

/* Empty State */
.empty-state {
    padding: 48px 24px;
    text-align: center;
}

.empty-icon-circle {
    width: 72px;
    height: 72px;
    background: #f1f5f9;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
}

.empty-title {
    font-size: 16px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
}

.empty-desc {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 16px;
}

/* Table Footer */
.table-footer {
    padding: 14px 20px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    font-size: 12.5px;
    color: #64748b;
}

.footer-highlight {
    font-weight: 700;
    color: #0f172a;
}

/* Modal Form Styles */
.modal-icon-circle {
    width: 36px;
    height: 36px;
    background: #ecfdf5;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f1f5f9;
    border: none;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.close-btn:hover {
    background: #e2e8f0;
    color: #1e293b;
}

.input-label {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 6px;
}

.role-selector-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

.role-option-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    cursor: pointer;
    background: #ffffff;
    transition: all 0.2s ease;
}

.role-option-card:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}

.role-option-card--active {
    border-color: #006837 !important;
    background: #f0fdf4 !important;
}

.role-option-desc {
    font-size: 11px;
    color: #64748b;
    margin: 0;
}

.btn-cancel {
    padding: 9px 18px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    background: #f1f5f9;
    border: none;
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-cancel:hover {
    background: #e2e8f0;
    color: #334155;
}

.btn-submit {
    display: inline-flex;
    align-items: center;
    padding: 9px 20px;
    font-size: 13px;
    font-weight: 600;
    color: #ffffff;
    background: #006837;
    border: none;
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 10px rgba(0, 104, 55, 0.25);
}

.btn-submit:hover:not(:disabled) {
    background: #00502b;
    transform: translateY(-1px);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
