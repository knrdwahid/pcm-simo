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
const search = ref('');
const roleFilter = ref('semua');

const form = useForm({
    name: '',
    email: '',
    role: 'tim',
    password: '',
});

// Role options
const roleOptions = [
    {
        value: 'tim',
        title: 'Tim Redaksi',
        desc: 'Akses menulis dan mengelola berita serta kategori.',
        icon: 'mdi-feather',
        color: '#0284c7',
        container: '#f0f9ff',
    },
    {
        value: 'admin',
        title: 'Administrator',
        desc: 'Akses penuh konten berita, kategori, dan kelola pengguna.',
        icon: 'mdi-shield-account',
        color: '#059669',
        container: '#ecfdf5',
    },
];

const isSuperAdmin = computed(() => currentUser.value?.role === 'superadmin');

const availableRoleOptions = computed(() => {
    return isSuperAdmin.value ? roleOptions : roleOptions.filter(r => r.value === 'tim');
});

const canManage = (targetUser) => {
    if (!targetUser) return false;
    if (isSuperAdmin.value) return true;
    return targetUser.role === 'tim';
};

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
    if (!canManage(user)) {
        showAlert.warning('Akses Dibatasi', 'Hanya Super Admin yang berwenang mengubah data akun Admin.');
        return;
    }
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

    if (!canManage(user)) {
        showAlert.warning('Akses Dibatasi', 'Hanya Super Admin yang berwenang menghapus akun Admin.');
        return;
    }

    const confirmed = await showAlert.confirm(
        'Hapus Akun Pengguna?',
        `Apakah Anda yakin ingin menghapus akun "${user.name}" (${user.email})? Tindakan ini tidak dapat dibatalkan.`,
        'Ya, Hapus Akun',
    );

    if (confirmed) {
        form.delete(`/dashboard/users/${user.id}`);
    }
};

const getRoleMeta = (role) => {
    switch (role) {
        case 'superadmin':
            return {
                label: 'Super Admin',
                icon: 'mdi-shield-crown',
                color: '#d97706',
                container: '#fffbeb',
                border: '#fde68a',
            };
        case 'admin':
            return {
                label: 'Admin',
                icon: 'mdi-shield-account',
                color: '#059669',
                container: '#ecfdf5',
                border: '#a7f3d0',
            };
        default:
            return {
                label: 'Tim Redaksi',
                icon: 'mdi-feather',
                color: '#0284c7',
                container: '#f0f9ff',
                border: '#bae6fd',
            };
    }
};

const countSuperAdmin = computed(() => props.users.filter(u => u.role === 'superadmin').length);
const countAdmin = computed(() => props.users.filter(u => u.role === 'admin').length);
const countTim = computed(() => props.users.filter(u => u.role === 'tim').length);

const filteredUsers = computed(() => {
    let result = props.users;
    if (roleFilter.value !== 'semua') {
        result = result.filter(u => u.role === roleFilter.value);
    }
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        result = result.filter(u =>
            u.name.toLowerCase().includes(q) ||
            u.email.toLowerCase().includes(q),
        );
    }
    return result;
});

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

    <div class="m3-page-container">
        <!-- ── Top Page Header (Material 3 Header) ── -->
        <header class="m3-header">
            <div class="m3-header-main">
                <div class="m3-header-titles">
                    <div class="d-flex align-center ga-2 mb-1 flex-wrap">
                        <span class="m3-header-badge">
                            <v-icon size="13" class="mr-2 text-emerald-700">mdi-account-group-outline</v-icon>
                            Otoritas &amp; Akun
                        </span>
                        <span class="m3-header-badge m3-header-badge--count">
                            {{ users.length }} Pengguna
                        </span>
                    </div>
                    <h1 class="m3-headline">Manajemen Pengguna</h1>
                    <p class="m3-subhead">
                        Kelola akun pengurus, hak akses sistem, dan peran pengelola konten PCM Simo.
                    </p>
                </div>

                <div class="m3-header-actions">
                    <button type="button" class="m3-fab-extended" @click="openCreate">
                        <v-icon size="20" class="mr-2">mdi-account-plus-outline</v-icon>
                        <span>Tambah Pengguna</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- ── Summary Metric Cards ── -->
        <section class="m3-summary-grid">
            <div
                class="m3-summary-tile"
                :class="{ 'm3-summary-tile--active': roleFilter === 'superadmin' }"
                style="--tile-color: #d97706; --tile-bg: #fffbeb;"
                @click="roleFilter = roleFilter === 'superadmin' ? 'semua' : 'superadmin'"
            >
                <div class="m3-summary-icon">
                    <v-icon size="24" color="#d97706">mdi-shield-crown</v-icon>
                </div>
                <div>
                    <div class="m3-summary-num">{{ countSuperAdmin }}</div>
                    <div class="m3-summary-txt">Super Admin</div>
                </div>
            </div>

            <div
                class="m3-summary-tile"
                :class="{ 'm3-summary-tile--active': roleFilter === 'admin' }"
                style="--tile-color: #059669; --tile-bg: #ecfdf5;"
                @click="roleFilter = roleFilter === 'admin' ? 'semua' : 'admin'"
            >
                <div class="m3-summary-icon">
                    <v-icon size="24" color="#059669">mdi-shield-account</v-icon>
                </div>
                <div>
                    <div class="m3-summary-num">{{ countAdmin }}</div>
                    <div class="m3-summary-txt">Admin Cabang</div>
                </div>
            </div>

            <div
                class="m3-summary-tile"
                :class="{ 'm3-summary-tile--active': roleFilter === 'tim' }"
                style="--tile-color: #0284c7; --tile-bg: #f0f9ff;"
                @click="roleFilter = roleFilter === 'tim' ? 'semua' : 'tim'"
            >
                <div class="m3-summary-icon">
                    <v-icon size="24" color="#0284c7">mdi-feather</v-icon>
                </div>
                <div>
                    <div class="m3-summary-num">{{ countTim }}</div>
                    <div class="m3-summary-txt">Tim Redaksi</div>
                </div>
            </div>
        </section>

        <!-- ── Toolbar (Search & Filter) ── -->
        <div class="m3-toolbar">
            <div class="m3-segmented-control">
                <button
                    type="button"
                    class="m3-segmented-btn"
                    :class="{ 'm3-segmented-btn--selected': roleFilter === 'semua' }"
                    @click="roleFilter = 'semua'"
                >
                    Semua
                </button>
                <button
                    type="button"
                    class="m3-segmented-btn"
                    :class="{ 'm3-segmented-btn--selected': roleFilter === 'admin' }"
                    @click="roleFilter = 'admin'"
                >
                    Admin
                </button>
                <button
                    type="button"
                    class="m3-segmented-btn"
                    :class="{ 'm3-segmented-btn--selected': roleFilter === 'tim' }"
                    @click="roleFilter = 'tim'"
                >
                    Tim Redaksi
                </button>
            </div>

            <div class="m3-search-bar">
                <v-icon size="18" class="m3-search-icon">mdi-magnify</v-icon>
                <input
                    v-model="search"
                    type="search"
                    placeholder="Cari nama atau email pengguna..."
                    class="m3-search-input"
                />
                <button
                    v-if="search"
                    type="button"
                    class="m3-search-clear"
                    title="Hapus pencarian"
                    @click="search = ''"
                >
                    <v-icon size="14">mdi-close</v-icon>
                </button>
            </div>
        </div>

        <!-- ── Material 3 Table Card ── -->
        <div class="m3-table-card">
            <div class="m3-table-scroll">
                <table class="m3-table">
                    <thead>
                        <tr>
                            <th class="col-user">Pengguna</th>
                            <th class="col-role">Peran &amp; Hak Akses</th>
                            <th class="col-status">Status Akun</th>
                            <th class="col-date">Terdaftar Sejak</th>
                            <th class="col-action text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in filteredUsers" :key="user.id" class="m3-table-row">
                            <!-- User Name & Email -->
                            <td class="col-user">
                                <div class="m3-user-cell">
                                    <div
                                        class="m3-user-avatar"
                                        :style="{
                                            background: getRoleMeta(user.role).container,
                                            color: getRoleMeta(user.role).color,
                                            borderColor: getRoleMeta(user.role).border,
                                        }"
                                    >
                                        {{ user.name ? user.name.charAt(0).toUpperCase() : 'A' }}
                                    </div>
                                    <div class="m3-user-details">
                                        <div class="d-flex align-center ga-1.5">
                                            <span class="m3-user-name">{{ user.name }}</span>
                                            <span v-if="user.id === currentUser.id" class="m3-self-pill">
                                                Anda
                                            </span>
                                        </div>
                                        <span class="m3-user-email">{{ user.email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Role Badge -->
                            <td class="col-role">
                                <span
                                    class="m3-role-tag"
                                    :style="{
                                        backgroundColor: getRoleMeta(user.role).container,
                                        color: getRoleMeta(user.role).color,
                                        borderColor: getRoleMeta(user.role).border,
                                    }"
                                >
                                    <v-icon size="14" class="mr-1.5" :color="getRoleMeta(user.role).color">
                                        {{ getRoleMeta(user.role).icon }}
                                    </v-icon>
                                    {{ getRoleMeta(user.role).label }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="col-status">
                                <span class="m3-status-active">
                                    <span class="m3-pulse-dot-green"></span>
                                    Aktif
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="col-date">
                                <span class="text-xs text-slate-500 font-medium">
                                    {{ formatDate(user.created_at) }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="col-action text-right">
                                <div class="m3-actions-cluster justify-end">
                                    <button
                                        v-if="canManage(user) || user.id === currentUser.id"
                                        type="button"
                                        class="m3-icon-btn m3-icon-btn--edit"
                                        title="Sunting Pengguna"
                                        @click="openEdit(user)"
                                    >
                                        <v-icon size="16">mdi-pencil-outline</v-icon>
                                    </button>

                                    <button
                                        v-if="user.id !== currentUser.id && canManage(user)"
                                        type="button"
                                        class="m3-icon-btn m3-icon-btn--delete"
                                        title="Hapus Akun"
                                        @click="deleteUser(user)"
                                    >
                                        <v-icon size="16">mdi-trash-can-outline</v-icon>
                                    </button>

                                    <span
                                        v-if="!canManage(user) && user.id !== currentUser.id"
                                        class="text-xs text-slate-400 italic px-2"
                                    >
                                        Terkunci
                                    </span>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="filteredUsers.length === 0">
                            <td colspan="5">
                                <div class="m3-empty-state-table">
                                    <div class="m3-empty-art">
                                        <v-icon size="36" color="#94a3b8">mdi-account-off-outline</v-icon>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-800 mb-1">
                                        {{ search ? `Tidak ada pengguna sesuai "${search}"` : 'Tidak ada pengguna' }}
                                    </h3>
                                    <p class="text-xs text-slate-500 mb-4">Tambahkan akun pengurus untuk mendelegasikan wewenang.</p>
                                    <button type="button" class="m3-btn-filled" @click="openCreate">
                                        <v-icon size="16" class="mr-2">mdi-account-plus-outline</v-icon>
                                        Tambah Pengguna Baru
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div v-if="filteredUsers.length > 0" class="m3-table-footer">
                <span>Menampilkan <b>{{ filteredUsers.length }}</b> dari total {{ users.length }} pengguna</span>
            </div>
        </div>

        <!-- ── Material Design 3 Modal Dialog ── -->
        <v-dialog v-model="dialog" max-width="540" transition="dialog-bottom-transition">
            <div class="m3-dialog-surface">
                <!-- Dialog Header -->
                <div class="m3-dialog-header">
                    <div class="m3-dialog-lead">
                        <div class="m3-dialog-icon-badge" style="background: #ecfdf5; color: #006837;">
                            <v-icon size="24" color="#006837">
                                {{ isEdit ? 'mdi-account-edit-outline' : 'mdi-account-plus-outline' }}
                            </v-icon>
                        </div>
                        <div>
                            <div class="m3-dialog-overline">{{ isEdit ? 'Perbarui Profil Akun' : 'Akun Pengurus Baru' }}</div>
                            <h2 class="m3-dialog-headline">{{ isEdit ? 'Sunting Pengguna' : 'Tambah Pengguna' }}</h2>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="m3-dialog-close"
                        aria-label="Tutup"
                        @click="dialog = false"
                    >
                        <v-icon size="18">mdi-close</v-icon>
                    </button>
                </div>

                <!-- Dialog Body Form -->
                <div class="m3-dialog-body">
                    <form id="user-form" @submit.prevent="submit">
                        <!-- Role Selection Cards -->
                        <div class="mb-4">
                            <label class="m3-field-label">Peran &amp; Hak Akses <span class="text-rose-500">*</span></label>
                            <div class="m3-role-cards-grid">
                                <button
                                    v-for="opt in availableRoleOptions"
                                    :key="opt.value"
                                    type="button"
                                    class="m3-role-card-opt"
                                    :class="{ 'm3-role-card-opt--selected': form.role === opt.value }"
                                    :style="{ '--r-color': opt.color, '--r-bg': opt.container }"
                                    @click="form.role = opt.value"
                                >
                                    <div class="m3-role-card-icon">
                                        <v-icon size="20" :color="opt.color">{{ opt.icon }}</v-icon>
                                    </div>
                                    <div class="m3-role-card-info">
                                        <div class="m3-role-card-title">{{ opt.title }}</div>
                                        <div class="m3-role-card-desc">{{ opt.desc }}</div>
                                    </div>
                                    <div v-if="form.role === opt.value" class="m3-role-check">
                                        <v-icon size="12" color="#ffffff">mdi-check</v-icon>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Nama Lengkap -->
                        <div class="mb-4">
                            <label class="m3-field-label">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <div class="m3-input-box" :class="{ 'm3-input-box--error': form.errors.name }">
                                <v-icon size="18" class="m3-input-icon">mdi-account-outline</v-icon>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="m3-native-input"
                                    placeholder="Contoh: Ahmad Fauzi, S.Pd."
                                    required
                                />
                            </div>
                            <p v-if="form.errors.name" class="m3-error-text">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label class="m3-field-label">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <div class="m3-input-box" :class="{ 'm3-input-box--error': form.errors.email }">
                                <v-icon size="18" class="m3-input-icon">mdi-email-outline</v-icon>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="m3-native-input"
                                    placeholder="nama@pcmsimo.or.id"
                                    required
                                />
                            </div>
                            <p v-if="form.errors.email" class="m3-error-text">{{ form.errors.email }}</p>
                        </div>

                        <!-- Password -->
                        <div class="mb-2">
                            <label class="m3-field-label">
                                {{ isEdit ? 'Ganti Password (Kosongkan bila tetap)' : 'Kata Sandi (Password)' }}
                                <span v-if="!isEdit" class="text-rose-500">*</span>
                            </label>
                            <div class="m3-input-box" :class="{ 'm3-input-box--error': form.errors.password }">
                                <v-icon size="18" class="m3-input-icon">mdi-lock-outline</v-icon>
                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="m3-native-input"
                                    :placeholder="isEdit ? 'Ketik jika ingin mengubah...' : 'Minimal 8 karakter...'"
                                    :required="!isEdit"
                                />
                                <button
                                    type="button"
                                    class="m3-pass-toggle"
                                    tabindex="-1"
                                    @click="showPassword = !showPassword"
                                >
                                    <v-icon size="18">{{ showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline' }}</v-icon>
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="m3-error-text">{{ form.errors.password }}</p>
                        </div>
                    </form>
                </div>

                <!-- Dialog Footer -->
                <div class="m3-dialog-footer">
                    <div></div>
                    <div class="m3-dialog-footer-right">
                        <button type="button" class="m3-btn-tonal" @click="dialog = false">
                            Batal
                        </button>
                        <button
                            type="submit"
                            form="user-form"
                            class="m3-btn-filled"
                            :disabled="form.processing"
                        >
                            <v-progress-circular
                                v-if="form.processing"
                                indeterminate
                                size="16"
                                width="2"
                                class="mr-2"
                            />
                            <v-icon v-else size="18" class="mr-2">mdi-check</v-icon>
                            <span>{{ form.processing ? 'Menyimpan...' : (isEdit ? 'Perbarui Pengguna' : 'Simpan Pengguna') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </v-dialog>
    </div>
</template>

<style scoped>
.m3-page-container {
    padding: 32px 36px 60px;
    max-width: 1320px;
    margin: 0 auto;
    font-family: 'Poppins', system-ui, -apple-system, sans-serif;
}

/* ── Header ── */
.m3-header {
    margin-bottom: 28px;
}

.m3-header-main {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

@media (min-width: 768px) {
    .m3-header-main {
        flex-direction: row;
        align-items: flex-start;
        justify-content: space-between;
    }
}

.m3-header-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ecfdf5;
    color: #065f46;
    font-size: 11.5px;
    font-weight: 600;
    padding: 3px 12px;
    border-radius: 9999px;
    border: 1px solid #a7f3d0;
}

.m3-header-badge--count {
    background: #f1f5f9;
    color: #475569;
    border-color: #e2e8f0;
}

.m3-headline {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.4px;
    margin: 0;
    line-height: 1.25;
}

.m3-subhead {
    font-size: 13.5px;
    color: #64748b;
    margin: 4px 0 0;
    max-width: 640px;
}

.m3-fab-extended {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #006837 0%, #008744 100%);
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 600;
    padding: 12px 22px;
    border-radius: 16px;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(0, 104, 55, 0.32);
    transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
    white-space: nowrap;
}

.m3-fab-extended:hover {
    background: linear-gradient(135deg, #00502a 0%, #006837 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(0, 104, 55, 0.4);
}

/* ── Summary Cards ── */
.m3-summary-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}

@media (min-width: 640px) {
    .m3-summary-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

.m3-summary-tile {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 20px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.m3-summary-tile:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    border-color: var(--tile-color);
}

.m3-summary-tile--active {
    border-color: var(--tile-color);
    background: var(--tile-bg);
}

.m3-summary-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: var(--tile-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.m3-summary-tile--active .m3-summary-icon {
    background: #ffffff;
}

.m3-summary-num {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
}

.m3-summary-txt {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    margin-top: 2px;
}

/* ── Toolbar ── */
.m3-toolbar {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

@media (min-width: 768px) {
    .m3-toolbar {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.m3-segmented-control {
    display: flex;
    gap: 6px;
    background: #ffffff;
    padding: 5px;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
}

.m3-segmented-btn {
    padding: 8px 14px;
    border-radius: 12px;
    border: none;
    background: transparent;
    color: #64748b;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.m3-segmented-btn:hover { background: #f8fafc; color: #0f172a; }

.m3-segmented-btn--selected {
    background: #006837 !important;
    color: #ffffff !important;
}

.m3-search-bar {
    position: relative;
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 9999px;
    padding: 0 14px 0 38px;
    width: 100%;
    max-width: 380px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    transition: all 0.2s ease;
}

.m3-search-bar:focus-within {
    border-color: #006837;
    box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.12);
}

.m3-search-icon { position: absolute; left: 13px; color: #94a3b8; }

.m3-search-input {
    flex: 1;
    border: none;
    outline: none;
    padding: 10px 0;
    font-size: 13px;
    color: #0f172a;
    background: transparent;
}

.m3-search-clear {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── Table Card ── */
.m3-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
}

.m3-table-scroll { overflow-x: auto; }

.m3-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.m3-table thead th {
    background: #f8fafc;
    padding: 14px 20px;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}

.m3-table-row {
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s ease;
}

.m3-table-row:last-child { border-bottom: none; }
.m3-table-row:hover { background: #f8fafc; }

.m3-table td {
    padding: 16px 20px;
    vertical-align: middle;
}

.col-user { min-width: 240px; }
.col-role { width: 180px; }
.col-status { width: 140px; }
.col-date { width: 160px; }
.col-action { width: 120px; }

.m3-user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.m3-user-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 1.5px solid;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
}

.m3-user-name {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
}

.m3-user-email {
    font-size: 12px;
    color: #64748b;
    display: block;
}

.m3-self-pill {
    font-size: 10px;
    font-weight: 700;
    background: #ecfdf5;
    color: #065f46;
    padding: 1px 6px;
    border-radius: 9999px;
    border: 1px solid #a7f3d0;
}

.m3-role-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 11px;
    border-radius: 9999px;
    font-size: 11.5px;
    font-weight: 700;
    border-width: 1px;
    border-style: solid;
}

.m3-status-active {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #059669;
}

.m3-pulse-dot-green {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #10b981;
}

.m3-actions-cluster {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.m3-icon-btn {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    border: 1px solid transparent;
    background: transparent;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.m3-icon-btn--edit { color: #0284c7; }
.m3-icon-btn--edit:hover { background: #f0f9ff; border-color: #bae6fd; }
.m3-icon-btn--delete { color: #94a3b8; }
.m3-icon-btn--delete:hover { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }

.m3-table-footer {
    padding: 12px 20px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    font-size: 12px;
    color: #64748b;
}

.m3-empty-state-table {
    padding: 56px 20px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.m3-empty-art {
    width: 64px;
    height: 64px;
    border-radius: 20px;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}

/* ── Material Design 3 Dialog ── */
.m3-dialog-surface {
    background: #ffffff;
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.2);
    display: flex;
    flex-direction: column;
}

.m3-dialog-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 22px 26px 16px;
    border-bottom: 1px solid #f1f5f9;
    background: linear-gradient(180deg, #fafbfc 0%, #ffffff 100%);
}

.m3-dialog-lead {
    display: flex;
    align-items: center;
    gap: 14px;
}

.m3-dialog-icon-badge {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.m3-dialog-overline {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #64748b;
}

.m3-dialog-headline {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}

.m3-dialog-close {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.m3-dialog-close:hover { background: #e2e8f0; color: #0f172a; }

.m3-dialog-body {
    padding: 22px 26px;
}

.m3-field-label {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 6px;
}

/* Role Selector Cards */
.m3-role-cards-grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.m3-role-card-opt {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 16px;
    border: 2px solid #e2e8f0;
    background: #ffffff;
    cursor: pointer;
    text-align: left;
    position: relative;
    transition: all 0.2s ease;
}

.m3-role-card-opt:hover {
    border-color: var(--r-color);
    background: #fafbfc;
}

.m3-role-card-opt--selected {
    border-color: var(--r-color) !important;
    background: var(--r-bg) !important;
}

.m3-role-card-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.m3-role-card-title {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
}

.m3-role-card-desc {
    font-size: 11px;
    color: #64748b;
}

.m3-role-check {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: var(--r-color);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: auto;
    flex-shrink: 0;
}

.m3-input-box {
    position: relative;
    display: flex;
    align-items: center;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 0 14px;
    transition: all 0.2s ease;
}

.m3-input-box:focus-within {
    background: #ffffff;
    border-color: #006837;
    box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.12);
}

.m3-input-box--error {
    border-color: #e11d48 !important;
    background: #fff1f2;
}

.m3-input-icon {
    color: #94a3b8;
    margin-right: 10px;
    flex-shrink: 0;
}

.m3-native-input {
    flex: 1;
    border: none;
    outline: none;
    padding: 10px 0;
    font-size: 13.5px;
    color: #0f172a;
    background: transparent;
    font-family: inherit;
    width: 100%;
}

.m3-pass-toggle {
    border: none;
    background: transparent;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
}

.m3-pass-toggle:hover { color: #0f172a; }

.m3-error-text {
    font-size: 11.5px;
    color: #e11d48;
    margin: 4px 0 0;
}

/* Dialog Footer */
.m3-dialog-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 26px;
    border-top: 1px solid #f1f5f9;
    background: #fafbfc;
}

.m3-dialog-footer-right {
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.m3-btn-tonal {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 14px;
    border: none;
    background: #f1f5f9;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.m3-btn-tonal:hover { background: #e2e8f0; color: #0f172a; }

.m3-btn-filled {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    border-radius: 14px;
    border: none;
    background: linear-gradient(135deg, #006837 0%, #008744 100%);
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(0, 104, 55, 0.28);
}

.m3-btn-filled:hover {
    background: linear-gradient(135deg, #00502a 0%, #006837 100%);
}
</style>
