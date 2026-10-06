<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import showAlert from '@/Utils/sweetalert';

const drawer = ref(true);
const page = usePage();
const user = computed(() => page.props.auth?.user ?? { name: 'Admin' });

// Auto-trigger SweetAlert2 on backend flash messages
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        showAlert.toast(flash.success, 'success');
    } else if (flash?.error) {
        showAlert.error('Terjadi Kesalahan', flash.error);
    } else if (flash?.warning) {
        showAlert.warning('Perhatian', flash.warning);
    } else if (flash?.info) {
        showAlert.toast(flash.info, 'info');
    }
}, { deep: true, immediate: true });

const mainMenu = [
    { title: 'Dashboard', icon: 'mdi-view-dashboard-outline', href: '/dashboard', activeRoute: '/dashboard' },
    { title: 'Monitoring', icon: 'mdi-chart-timeline-variant-shimmer', href: '/dashboard/monitoring', activeRoute: '/dashboard/monitoring' },
    { title: 'Berita', icon: 'mdi-newspaper-variant-outline', href: '/dashboard/news', activeRoute: '/dashboard/news' },
    { title: 'Kategori', icon: 'mdi-tag-outline', href: '/dashboard/categories', activeRoute: '/dashboard/categories' },
    { title: 'AUM & Organisasi', icon: 'mdi-domain', href: '/dashboard/aum', activeRoute: '/dashboard/aum' },
    { title: 'Pengguna', icon: 'mdi-account-group-outline', href: '/dashboard/users', activeRoute: '/dashboard/users', roles: ['superadmin', 'admin'] },
];

const filteredMainMenu = computed(() => {
    return mainMenu.filter(item => {
        if (!item.roles) return true;
        return item.roles.includes(user.value?.role);
    });
});

const userRoleLabel = computed(() => {
    switch (user.value?.role) {
        case 'superadmin':
            return 'Super Admin';
        case 'admin':
            return 'Admin';
        case 'tim':
            return 'Tim Redaksi';
        default:
            return 'Administrator';
    }
});

const userRoleBadgeClass = computed(() => {
    switch (user.value?.role) {
        case 'superadmin':
            return 'role-badge--superadmin';
        case 'admin':
            return 'role-badge--admin';
        case 'tim':
            return 'role-badge--tim';
        default:
            return '';
    }
});

const otherMenu = [
    { title: 'Lihat Website', icon: 'mdi-web', href: '/', external: true },
];

const logout = () => router.post('/dashboard/logout');

// Flash message
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const isActive = (item) => {
    if (!item.activeRoute) return false;
    if (item.activeRoute === '/dashboard') return page.url === '/dashboard';
    return page.url.startsWith(item.activeRoute);
};

// Dynamic Topbar Page Title
const pageTitle = computed(() => {
    const url = page.url;
    if (url === '/dashboard') return 'Dashboard Utama';
    if (url.startsWith('/dashboard/monitoring')) return 'Monitoring & Statistik Pengunjung';
    if (url.startsWith('/dashboard/news/create')) return 'Tulis Berita Baru';
    if (url.includes('/dashboard/news/') && url.endsWith('/edit')) return 'Sunting Berita';
    if (url.startsWith('/dashboard/news')) return 'Manajemen Berita';
    if (url.startsWith('/dashboard/categories')) return 'Kategori Berita';
    if (url.startsWith('/dashboard/aum')) return 'AUM & Organisasi';
    if (url.startsWith('/dashboard/users')) return 'Manajemen Pengguna';
    return 'Panel Pengurus';
});

// Live Real-Time Date & Clock
const getFormattedDate = () => {
    const now = new Date();
    return now.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const getFormattedTime = () => {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    return `${hours}:${minutes}:${seconds} WIB`;
};

const currentDate = ref(getFormattedDate());
const currentTime = ref(getFormattedTime());

let timer = null;
onMounted(() => {
    timer = setInterval(() => {
        currentDate.value = getFormattedDate();
        currentTime.value = getFormattedTime();
    }, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <v-app class="font-poppins">
        <!-- Sidebar -->
        <v-navigation-drawer v-model="drawer" width="260" class="sidebar" elevation="0" permanent>
            <!-- Logo -->
            <div class="sidebar-brand">
                <Link href="/dashboard" class="sidebar-brand-link">
                    <img src="/images/logo-pcmsimo-white.png" alt="PCM Simo" class="sidebar-brand-logo" />
                </Link>
            </div>

            <!-- Menu -->
            <div class="sidebar-menu">
                <div class="menu-section-label">Menu Utama</div>
                <div v-for="item in filteredMainMenu" :key="item.title">
                    <Link :href="item.href" class="text-decoration-none">
                        <div :class="['menu-item', { 'menu-item--active': isActive(item) }]">
                            <v-icon size="20">{{ item.icon }}</v-icon>
                            <span>{{ item.title }}</span>
                        </div>
                    </Link>
                </div>

                <div class="menu-section-label mt-6">Lainnya</div>
                <div v-for="item in otherMenu" :key="item.title">
                    <a :href="item.href" target="_blank" class="text-decoration-none">
                        <div class="menu-item">
                            <v-icon size="20">{{ item.icon }}</v-icon>
                            <span>{{ item.title }}</span>
                        </div>
                    </a>
                </div>
            </div>

            <template #append>
                <!-- User -->
                <div class="sidebar-user">
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-avatar">
                            {{ user.name ? user.name.charAt(0) : 'A' }}
                        </div>
                        <div class="sidebar-user-meta">
                            <div class="sidebar-user-name">{{ user.name }}</div>
                            <div :class="['sidebar-user-role', userRoleBadgeClass]">
                                <v-icon v-if="user.role === 'superadmin'" size="11" class="mr-1">mdi-shield-crown</v-icon>
                                <v-icon v-else-if="user.role === 'admin'" size="11" class="mr-1">mdi-shield-account</v-icon>
                                <v-icon v-else size="11" class="mr-1">mdi-feather</v-icon>
                                <span>{{ userRoleLabel }}</span>
                            </div>
                        </div>
                    </div>
                    <button class="sidebar-logout" @click="logout">
                        <v-icon size="18">mdi-logout</v-icon>
                        <span>Keluar (Logout)</span>
                    </button>
                </div>
            </template>
        </v-navigation-drawer>

        <!-- Top Bar -->
        <v-app-bar elevation="0" class="topbar" height="64">
            <v-app-bar-nav-icon @click="drawer = !drawer" class="d-lg-none" />

            <div class="topbar-title">
                <Transition name="title-fade-slide" mode="out-in">
                    <span :key="pageTitle" class="topbar-title-page">{{ pageTitle }}</span>
                </Transition>
            </div>

            <v-spacer />

            <div class="d-flex align-center ga-3">
                <a href="/" target="_blank" class="topbar-icon-btn" title="Buka Website">
                    <v-icon size="20">mdi-open-in-new</v-icon>
                </a>

                <div class="topbar-divider"></div>

                <div class="topbar-datetime">
                    <div class="topbar-date">{{ currentDate }}</div>
                    <div class="topbar-time">{{ currentTime }}</div>
                </div>
            </div>
        </v-app-bar>

        <!-- Main Content -->
        <v-main class="main-content">
            <div class="page-viewport">
                <Transition name="page-fade-slide" mode="out-in" appear>
                    <div :key="page.url.split('?')[0]" class="page-content-wrapper">
                        <slot />
                    </div>
                </Transition>
            </div>
        </v-main>
    </v-app>
</template>

<style scoped>
/* ── Sidebar ── */
.sidebar {
    background: #0a1f14 !important;
    border: none !important;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 26px 16px 22px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    margin-bottom: 10px;
}

.sidebar-brand-link {
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    width: 100%;
}

.sidebar-brand-logo {
    height: 84px;
    width: 100%;
    max-width: 228px;
    object-fit: contain;
    filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.4));
    transition: transform 0.2s ease, filter 0.2s ease;
}

.sidebar-brand-link:hover .sidebar-brand-logo {
    transform: scale(1.03);
    filter: drop-shadow(0 6px 16px rgba(0, 0, 0, 0.5));
}

/* ── Menu ── */
.sidebar-menu {
    padding: 8px 12px;
}

.menu-section-label {
    font-size: 11px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.3);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    padding: 8px 12px 6px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 16px;
    border-radius: 9999px;
    font-size: 13.5px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.65);
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    margin-bottom: 4px;
    position: relative;
}

.menu-item:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.08);
    transform: translateX(3px);
}

.menu-item--active {
    background: linear-gradient(135deg, #006837 0%, #008744 100%) !important;
    color: #ffffff !important;
    font-weight: 600;
    box-shadow: 0 4px 18px rgba(0, 104, 55, 0.45);
    transform: translateX(2px);
}

/* ── Sidebar User ── */
.sidebar-user {
    padding: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.sidebar-user-info {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.sidebar-user-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #006837;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sidebar-user-meta {
    overflow: hidden;
}

.sidebar-user-name {
    font-size: 13px;
    font-weight: 600;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-user-role {
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    padding: 1px 6px;
    border-radius: 4px;
    font-weight: 500;
    margin-top: 2px;
    color: rgba(255, 255, 255, 0.4);
}

.role-badge--superadmin {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24 !important;
    border: 1px solid rgba(245, 158, 11, 0.35);
}

.role-badge--admin {
    background: rgba(16, 185, 129, 0.2);
    color: #6ee7b7 !important;
    border: 1px solid rgba(16, 185, 129, 0.35);
}

.role-badge--tim {
    background: rgba(56, 189, 248, 0.2);
    color: #7dd3fc !important;
    border: 1px solid rgba(56, 189, 248, 0.35);
}

.sidebar-logout {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    background: transparent;
    color: rgba(255, 255, 255, 0.5);
    font-size: 12px;
    font-weight: 500;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    transition: all 0.2s ease;
}

.sidebar-logout:hover {
    background: rgba(220, 38, 38, 0.15);
    border-color: rgba(220, 38, 38, 0.3);
    color: #fca5a5;
}

/* ── Top Bar ── */
.topbar {
    background: #fff !important;
    border-bottom: 1px solid #f0f0f0 !important;
    padding: 0 24px !important;
}

.topbar-title {
    margin-left: 8px;
}

.topbar-title-page {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
}

.topbar-divider {
    width: 1px;
    height: 32px;
    background: #e2e8f0;
}

.topbar-datetime {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    line-height: 1.3;
}

.topbar-date {
    font-size: 12.5px;
    font-weight: 600;
    color: #5a6a85;
    white-space: nowrap;
    letter-spacing: -0.1px;
}

.topbar-time {
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.topbar-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    color: #888;
    transition: all 0.2s ease;
    text-decoration: none;
}

.topbar-icon-btn:hover {
    background: #f5f5f5;
    color: #333;
}

/* ── Main ── */
.main-content {
    background: #f7f8fa !important;
    min-height: 100vh;
}

/* ── Page Viewport & Transition Animations ── */
.page-viewport {
    position: relative;
    min-height: calc(100vh - 64px);
    width: 100%;
    overflow-x: hidden;
}

.page-content-wrapper {
    width: 100%;
    will-change: opacity, transform;
}

/* Page transition: soft fade & gentle upward glide */
.page-fade-slide-enter-active {
    transition: opacity 0.28s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}

.page-fade-slide-leave-active {
    transition: opacity 0.16s cubic-bezier(0.4, 0, 1, 1),
                transform 0.16s cubic-bezier(0.4, 0, 1, 1);
}

.page-fade-slide-enter-from {
    opacity: 0;
    transform: translateY(12px) scale(0.996);
}

.page-fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

/* Topbar page title transition */
.title-fade-slide-enter-active,
.title-fade-slide-leave-active {
    transition: opacity 0.22s ease, transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    display: inline-block;
}

.title-fade-slide-enter-from {
    opacity: 0;
    transform: translateY(6px);
}

.title-fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>
