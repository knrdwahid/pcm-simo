<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    user: { type: Object, default: () => ({}) },
    stats: {
        type: Object,
        default: () => ({
            totalArticles: 0,
            publishedArticles: 0,
            totalCategories: 0,
            totalAums: 0,
            totalViews: 0,
        }),
    },
    recentArticles: { type: Array, default: () => [] },
});

const formatDate = (dateStr) => {
    if (!dateStr) return { date: '-', time: '' };
    try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return { date: '-', time: '' };
        const dateStrFormatted = date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
        return dateStrFormatted;
    } catch {
        return dateStr;
    }
};

const formatNumber = (num) => {
    if (!num) return '0';
    if (num >= 1000) return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
    return num.toLocaleString('id-ID');
};

const hexToRgba = (hex, alpha = 1) => {
    if (!hex) return `rgba(0, 104, 55, ${alpha})`;
    let clean = hex.replace('#', '');
    if (clean.length === 3) {
        clean = clean.split('').map(c => c + c).join('');
    }
    const num = parseInt(clean, 16);
    if (isNaN(num)) return `rgba(0, 104, 55, ${alpha})`;
    const r = (num >> 16) & 255;
    const g = (num >> 8) & 255;
    const b = num & 255;
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

const toggleStatus = (id) => {
    router.patch(`/dashboard/news/${id}/toggle-publish`, {}, {
        preserveScroll: true,
    });
};

const statCards = [
    {
        label: 'TOTAL BERITA',
        key: 'totalArticles',
        subKey: 'publishedArticles',
        subLabel: 'tayang aktif',
        icon: 'mdi-newspaper-variant-outline',
        iconBg: '#ecfdf5',
        iconColor: '#006837',
        accentColor: '#006837',
    },
    {
        label: 'KATEGORI BERITA',
        key: 'totalCategories',
        subLabel: 'Rubrikasi terdaftar',
        icon: 'mdi-tag-outline',
        iconBg: '#eff6ff',
        iconColor: '#2563eb',
        accentColor: '#2563eb',
    },
    {
        label: 'TOTAL PEMBACA',
        key: 'totalViews',
        subLabel: 'Akumulasi kunjungan warta',
        icon: 'mdi-eye-outline',
        iconBg: '#fff7ed',
        iconColor: '#ea580c',
        accentColor: '#ea580c',
        format: true,
    },
    {
        label: 'AMAL USAHA',
        key: 'totalAums',
        subLabel: 'Sekolah, PKU & Masjid',
        icon: 'mdi-office-building-outline',
        iconBg: '#f0fdfa',
        iconColor: '#0d9488',
        accentColor: '#0d9488',
    },
];
</script>

<template>
    <Head title="Dashboard - PCM Simo" />

    <div class="dashboard-container">
        <!-- Header Section -->
        <div class="dash-header">
            <div>
                <h1 class="dash-title">Ringkasan Dashboard</h1>
                <p class="dash-subtitle">Selamat datang kembali, <strong class="text-slate-700">{{ user.name ?? 'Admin' }}</strong>. Pantau aktivitas portal dakwah dan warta terkini.</p>
            </div>
            <Link href="/dashboard/news/create" class="text-decoration-none">
                <button class="btn-quick-create">
                    <v-icon size="18" class="mr-1">mdi-plus</v-icon>
                    <span>Tulis Berita Baru</span>
                </button>
            </Link>
        </div>

        <!-- Stat Cards Grid -->
        <div class="stat-grid">
            <div v-for="card in statCards" :key="card.key" class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-label">{{ card.label }}</span>
                    <div class="stat-icon-wrapper" :style="{ background: card.iconBg }">
                        <v-icon :color="card.iconColor" size="20">{{ card.icon }}</v-icon>
                    </div>
                </div>

                <div class="stat-value-row">
                    <span class="stat-value">
                        {{ card.format ? formatNumber(stats[card.key]) : stats[card.key] }}
                    </span>
                </div>

                <div class="stat-footer">
                    <template v-if="card.subKey">
                        <span class="stat-badge-active">
                            <span class="stat-pulse-dot"></span>
                            {{ stats[card.subKey] }} {{ card.subLabel }}
                        </span>
                    </template>
                    <template v-else>
                        <span class="stat-sub-text">{{ card.subLabel }}</span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Recent Articles Table Card -->
        <div class="recent-table-card">
            <div class="table-card-header">
                <div>
                    <h2 class="table-card-title">Berita Terbaru</h2>
                    <p class="table-card-subtitle">Warta dan publikasi artikel terkini PCM Simo</p>
                </div>
                <Link href="/dashboard/news" class="text-decoration-none">
                    <button class="btn-view-all">
                        <span>Lihat Semua Berita</span>
                        <v-icon size="16" class="ml-1">mdi-arrow-right</v-icon>
                    </button>
                </Link>
            </div>

            <div class="table-scroll">
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th class="col-thumb">Sampul</th>
                            <th class="col-title">Judul Berita</th>
                            <th class="col-cat">Kategori</th>
                            <th class="col-date">Tanggal</th>
                            <th class="col-status">Status</th>
                            <th class="col-action text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="art in recentArticles" :key="art.id" class="table-row">
                            <!-- Thumbnail -->
                            <td class="col-thumb">
                                <div class="thumb-wrapper">
                                    <img
                                        v-if="art.image_url"
                                        :src="art.image_url"
                                        :alt="art.title"
                                        class="thumb-img"
                                        loading="lazy"
                                    />
                                    <div v-else class="thumb-placeholder">
                                        <v-icon size="18" color="#94a3b8">mdi-image-outline</v-icon>
                                    </div>
                                </div>
                            </td>

                            <!-- Title & Views -->
                            <td class="col-title">
                                <div class="title-cell">
                                    <Link :href="`/dashboard/news/${art.id}/edit`" class="article-title-link">
                                        {{ art.title }}
                                    </Link>
                                    <div class="article-meta">
                                        <span class="meta-views">
                                            <v-icon size="12" class="mr-0.5">mdi-eye-outline</v-icon>
                                            {{ (art.views || 0).toLocaleString('id-ID') }} pembaca
                                        </span>
                                        <span v-if="art.is_featured" class="headline-tag">
                                            ★ Headline
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="col-cat">
                                <span
                                    class="category-badge"
                                    :style="{
                                        backgroundColor: hexToRgba(art.category?.color, 0.08),
                                        color: art.category?.color || '#006837',
                                        borderColor: hexToRgba(art.category?.color, 0.22),
                                    }"
                                >
                                    <span
                                        class="category-dot"
                                        :style="{ backgroundColor: art.category?.color || '#006837' }"
                                    ></span>
                                    {{ art.category?.name || 'Umum' }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="col-date">
                                <span class="date-text">
                                    {{ formatDate(art.published_at) }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="col-status">
                                <button
                                    type="button"
                                    :class="[
                                        'status-pill',
                                        art.status === 'published' ? 'status-pill--published' : 'status-pill--draft'
                                    ]"
                                    @click="toggleStatus(art.id)"
                                    title="Klik untuk mengubah status"
                                >
                                    <span class="status-pulse-dot"></span>
                                    <span class="status-text">{{ art.status === 'published' ? 'Tayang' : 'Draft' }}</span>
                                </button>
                            </td>

                            <!-- Action -->
                            <td class="col-action">
                                <div class="actions-group">
                                    <Link :href="`/dashboard/news/${art.id}/edit`" class="action-btn action-btn--edit" title="Sunting">
                                        <v-icon size="15">mdi-pencil-outline</v-icon>
                                    </Link>
                                    <a :href="`/berita/${art.slug}`" target="_blank" class="action-btn action-btn--view" title="Lihat">
                                        <v-icon size="15">mdi-eye-outline</v-icon>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="recentArticles.length === 0">
                            <td colspan="6" class="text-center py-10 text-slate-400 text-xs">
                                Belum ada artikel warta yang dipublikasikan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
.dashboard-container {
    padding: 28px 32px;
    max-width: 1280px;
    margin: 0 auto;
}

/* ── Top Header ── */
.dash-header {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 26px;
}

@media (min-width: 640px) {
    .dash-header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.dash-title {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.3px;
    margin: 0;
}

.dash-subtitle {
    font-size: 13px;
    color: #64748b;
    margin: 3px 0 0 0;
}

.btn-quick-create {
    display: inline-flex;
    align-items: center;
    background: #006837;
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 18px;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 104, 55, 0.25);
    transition: all 0.2s ease;
}

.btn-quick-create:hover {
    background: #00552d;
    box-shadow: 0 4px 12px rgba(0, 104, 55, 0.35);
    transform: translateY(-1px);
}

/* ── Stat Cards ── */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}

@media (min-width: 640px) {
    .stat-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .stat-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.2s ease;
}

.stat-card:hover {
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    border-color: #cbd5e1;
    transform: translateY(-2px);
}

.stat-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.stat-label {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.6px;
}

.stat-icon-wrapper {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-value-row {
    margin-bottom: 12px;
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.5px;
    line-height: 1;
}

.stat-footer {
    display: flex;
    align-items: center;
    font-size: 12px;
    color: #64748b;
}

.stat-badge-active {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ecfdf5;
    color: #047857;
    font-size: 11.5px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 9999px;
    border: 1px solid #a7f3d0;
}

.stat-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: #10b981;
}

.stat-sub-text {
    font-size: 12px;
    color: #64748b;
}

/* ── Table Card ── */
.recent-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    overflow: hidden;
}

.table-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 22px 16px;
    border-bottom: 1px solid #f1f5f9;
}

.table-card-title {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.table-card-subtitle {
    font-size: 12px;
    color: #64748b;
    margin: 2px 0 0 0;
}

.btn-view-all {
    display: inline-flex;
    align-items: center;
    background: transparent;
    border: none;
    color: #006837;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 8px;
    transition: all 0.15s ease;
}

.btn-view-all:hover {
    background: #f0fdf4;
    color: #00552d;
}

/* ── Table ── */
.table-scroll {
    overflow-x: auto;
}

.recent-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.recent-table thead th {
    background: #f8fafc;
    padding: 12px 18px;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}

.table-row {
    border-bottom: 1px solid #f1f5f9;
    transition: background-color 0.15s ease;
}

.table-row:last-child {
    border-bottom: none;
}

.table-row:hover {
    background-color: #f8fafc;
}

.recent-table td {
    padding: 12px 18px;
    vertical-align: middle;
}

.col-thumb {
    width: 70px;
}

.col-title {
    min-width: 260px;
}

.col-cat {
    width: 160px;
    white-space: nowrap;
}

.col-date {
    width: 130px;
    white-space: nowrap;
}

.col-status {
    width: 110px;
    white-space: nowrap;
}

.col-action {
    width: 90px;
    white-space: nowrap;
    text-align: right;
}

/* Thumb */
.thumb-wrapper {
    width: 58px;
    height: 40px;
    border-radius: 8px;
    overflow: hidden;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
}

.thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.2s ease;
}

.table-row:hover .thumb-img {
    transform: scale(1.05);
}

.thumb-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Title & Meta */
.title-cell {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.article-title-link {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    text-decoration: none;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.15s ease;
}

.article-title-link:hover {
    color: #006837;
}

.article-meta {
    display: flex;
    align-items: center;
    gap: 8px;
}

.meta-views {
    font-size: 11px;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
}

.headline-tag {
    font-size: 10px;
    font-weight: 700;
    color: #b45309;
    background: #fffbeb;
    border: 1px solid #fde68a;
    padding: 0 6px;
    border-radius: 9999px;
}

/* Category Badge */
.category-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 600;
    border-width: 1px;
    border-style: solid;
}

.category-dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    flex-shrink: 0;
}

/* Date */
.date-text {
    font-size: 12px;
    color: #475569;
    font-weight: 500;
}

/* Status Pill */
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
    border-width: 1px;
    border-style: solid;
    cursor: pointer;
    background: transparent;
    transition: all 0.15s ease;
}

.status-pill--published {
    background: #ecfdf5;
    color: #047857;
    border-color: #a7f3d0;
}

.status-pill--published .status-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: #10b981;
}

.status-pill--draft {
    background: #f8fafc;
    color: #64748b;
    border-color: #cbd5e1;
}

.status-pill--draft .status-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: #94a3b8;
}

/* Action Buttons */
.actions-group {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.action-btn {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid transparent;
    background: transparent;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
}

.action-btn--edit {
    color: #0284c7;
}

.action-btn--edit:hover {
    background: #f0f9ff;
    color: #0369a1;
    border-color: #bae6fd;
}

.action-btn--view {
    color: #64748b;
}

.action-btn--view:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #e2e8f0;
}
</style>
