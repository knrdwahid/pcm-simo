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
        href: '/dashboard/news',
    },
    {
        label: 'KATEGORI BERITA',
        key: 'totalCategories',
        subLabel: 'Rubrikasi terdaftar',
        icon: 'mdi-tag-outline',
        iconBg: '#eff6ff',
        iconColor: '#2563eb',
        accentColor: '#2563eb',
        href: '/dashboard/categories',
    },
    {
        label: 'TRAFIK & PENGUNJUNG',
        key: 'totalViews',
        subKey: 'onlineVisitors',
        subLabel: 'online sekarang',
        icon: 'mdi-chart-timeline-variant-shimmer',
        iconBg: '#fff7ed',
        iconColor: '#ea580c',
        accentColor: '#ea580c',
        format: true,
        href: '/dashboard/monitoring',
    },
    {
        label: 'AUM & ORGANISASI',
        key: 'totalAums',
        subLabel: 'Majelis, Ortom, AUM, Masjid',
        icon: 'mdi-domain',
        iconBg: '#f0fdfa',
        iconColor: '#0d9488',
        accentColor: '#0d9488',
        href: '/dashboard/aum',
    },
];
</script>

<template>
    <Head title="Dashboard - PCM Simo" />

    <div class="m3-page-container">
        <!-- ── Top Page Bar (Material 3 Header) ── -->
        <header class="m3-header">
            <div class="m3-header-main">
                <div class="m3-header-titles">
                    <div class="d-flex align-center ga-2 mb-1 flex-wrap">
                        <span class="m3-header-badge">
                            <v-icon size="13" class="mr-1 text-emerald-700">mdi-view-dashboard-outline</v-icon>
                            Portal Informasi &amp; Dakwah
                        </span>
                        <span class="m3-header-badge m3-header-badge--count">
                            PCM Simo
                        </span>
                    </div>
                    <h1 class="m3-headline">Ringkasan Dashboard</h1>
                    <p class="m3-subhead">
                        Selamat datang kembali, <strong class="text-slate-800">{{ user.name ?? 'Admin' }}</strong>. Pantau aktivitas publikasi warta, AUM &amp; Ortom, serta performa portal terkini.
                    </p>
                </div>

                <div class="m3-header-actions">
                    <Link href="/dashboard/monitoring" class="text-decoration-none">
                        <button class="m3-btn-tonal">
                            <v-icon size="18" class="mr-1.5 text-emerald-700">mdi-chart-timeline-variant-shimmer</v-icon>
                            <span>Monitoring Pengunjung</span>
                        </button>
                    </Link>
                    <Link href="/dashboard/aum" class="text-decoration-none">
                        <button class="m3-btn-tonal">
                            <v-icon size="18" class="mr-1.5 text-emerald-700">mdi-domain</v-icon>
                            <span>Kelola AUM &amp; Ortom</span>
                        </button>
                    </Link>
                    <Link href="/dashboard/news/create" class="text-decoration-none">
                        <button class="m3-fab-extended">
                            <v-icon size="20" class="mr-1.5">mdi-plus</v-icon>
                            <span>Tulis Berita Baru</span>
                        </button>
                    </Link>
                </div>
            </div>
        </header>

        <!-- ── Material 3 Metrics Grid ── -->
        <section class="m3-metrics-grid" aria-label="Statistik Utama">
            <component
                :is="card.href ? Link : 'div'"
                v-for="card in statCards"
                :key="card.key"
                :href="card.href"
                class="m3-metric-card text-decoration-none"
                :style="{ '--m3-accent': card.accentColor, '--m3-container': card.iconBg }"
            >
                <div class="m3-metric-top">
                    <div class="m3-metric-icon">
                        <v-icon size="22" :color="card.iconColor">{{ card.icon }}</v-icon>
                    </div>
                    <span v-if="card.subKey && stats[card.subKey] > 0" class="m3-metric-active-pill">
                        <span class="m3-pulse-dot"></span>
                        {{ stats[card.subKey] }} Aktif
                    </span>
                    <v-icon v-else size="16" class="m3-metric-arrow">mdi-arrow-top-right</v-icon>
                </div>

                <div class="m3-metric-body">
                    <div class="m3-metric-value">
                        {{ card.format ? formatNumber(stats[card.key]) : (stats[card.key] ?? 0) }}
                    </div>
                    <div class="m3-metric-label">{{ card.label }}</div>
                </div>

                <div class="m3-metric-footer">
                    <span>{{ card.subLabel }}</span>
                    <v-icon size="14" class="m3-metric-footer-icon">mdi-chevron-right</v-icon>
                </div>
            </component>
        </section>

        <!-- ── Recent Articles Table (Material 3 Card) ── -->
        <div class="m3-table-card">
            <div class="m3-table-header">
                <div>
                    <div class="d-flex align-center ga-2 mb-1">
                        <span class="m3-header-badge">
                            <v-icon size="13" class="mr-1 text-emerald-700">mdi-newspaper-variant-outline</v-icon>
                            Pembaruan Terkini
                        </span>
                    </div>
                    <h2 class="m3-table-title">Warta Berita Terbaru</h2>
                    <p class="m3-table-subtitle">Publikasi artikel dan kabar persyarikatan terkini se-Cabang Simo</p>
                </div>
                <Link href="/dashboard/news" class="text-decoration-none">
                    <button class="m3-btn-tonal">
                        <span>Buka Manajemen Berita</span>
                        <v-icon size="16" class="ml-1.5">mdi-arrow-right</v-icon>
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
.m3-page-container {
    padding: 32px 36px 60px;
    max-width: 1320px;
    margin: 0 auto;
    font-family: 'Poppins', system-ui, -apple-system, sans-serif;
}

/* ── M3 Top Header ── */
.m3-header {
    margin-bottom: 24px;
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
    background: #ecfdf5;
    color: #065f46;
    font-size: 11.5px;
    font-weight: 600;
    padding: 3px 10px;
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
    letter-spacing: -0.02em;
    margin: 0 0 4px 0;
    line-height: 1.25;
}

.m3-subhead {
    font-size: 13.5px;
    color: #64748b;
    margin: 0;
    max-width: 640px;
    line-height: 1.5;
}

.m3-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.m3-fab-extended {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(135deg, #006837 0%, #008744 100%);
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 600;
    padding: 11px 22px;
    border-radius: 9999px;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(0, 104, 55, 0.3);
    transition: all 0.2s cubic-bezier(0.2, 0, 0, 1);
}

.m3-fab-extended:hover {
    background: linear-gradient(135deg, #00502a 0%, #006837 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(0, 104, 55, 0.4);
}

.m3-btn-tonal {
    display: inline-flex;
    align-items: center;
    padding: 10px 18px;
    border-radius: 9999px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #0f172a;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    transition: all 0.2s ease;
}

.m3-btn-tonal:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* ── M3 Metrics Grid ── */
.m3-metrics-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

@media (min-width: 640px) {
    .m3-metrics-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .m3-metrics-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

.m3-metric-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 20px;
    text-align: left;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
    position: relative;
    overflow: hidden;
}

.m3-metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--m3-accent, #006837);
    opacity: 0;
    transition: opacity 0.2s ease;
}

.m3-metric-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    border-color: #cbd5e1;
}

.m3-metric-card:hover::before {
    opacity: 1;
}

.m3-metric-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.m3-metric-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: var(--m3-container, #ecfdf5);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.m3-metric-arrow {
    color: #94a3b8;
    transition: transform 0.2s ease, color 0.2s ease;
}

.m3-metric-card:hover .m3-metric-arrow {
    color: var(--m3-accent, #006837);
    transform: translate(2px, -2px);
}

.m3-metric-active-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 2px 8px;
    border-radius: 9999px;
    background: #ecfdf5;
    color: #047857;
    font-size: 11px;
    font-weight: 700;
    border: 1px solid #a7f3d0;
}

.m3-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: #10b981;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.85); }
}

.m3-metric-body {
    margin-bottom: 12px;
}

.m3-metric-value {
    font-size: 30px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
    letter-spacing: -0.02em;
    font-variant-numeric: tabular-nums;
}

.m3-metric-label {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.m3-metric-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    color: #94a3b8;
    font-weight: 500;
    padding-top: 10px;
    border-top: 1px solid #f8fafc;
}

.m3-metric-footer-icon {
    transition: transform 0.2s ease;
}

.m3-metric-card:hover .m3-metric-footer-icon {
    transform: translateX(3px);
    color: #0f172a;
}

/* ── M3 Table Card ── */
.m3-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.m3-table-header {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 22px 24px 18px;
    border-bottom: 1px solid #f1f5f9;
}

@media (min-width: 640px) {
    .m3-table-header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.m3-table-title {
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.m3-table-subtitle {
    font-size: 12.5px;
    color: #64748b;
    margin: 2px 0 0 0;
}

/* ── Table Styling ── */
.table-scroll {
    overflow-x: auto;
}

.recent-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    text-align: left;
}

.recent-table th {
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 12px 18px;
    border-bottom: 1px solid #e2e8f0;
}

.recent-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
    color: #1e293b;
    vertical-align: middle;
}

.table-row {
    transition: background 0.15s ease;
}

.table-row:hover {
    background: #f8fafc;
}

.table-row:last-child td {
    border-bottom: none;
}

/* Column specific */
.col-thumb { width: 70px; }
.col-title { min-width: 260px; }
.col-cat { width: 140px; }
.col-date { width: 130px; }
.col-status { width: 110px; }
.col-action { width: 90px; }

/* Thumbnail */
.thumb-wrapper {
    width: 52px;
    height: 38px;
    border-radius: 8px;
    overflow: hidden;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
}

.thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
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
    gap: 4px;
}

.article-title-link {
    color: #0f172a;
    font-weight: 600;
    font-size: 13.5px;
    line-height: 1.4;
    text-decoration: none;
    transition: color 0.15s ease;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
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
    font-size: 11.5px;
    color: #64748b;
    display: inline-flex;
    align-items: center;
}

.headline-tag {
    font-size: 10.5px;
    font-weight: 700;
    color: #d97706;
    background: #fef3c7;
    padding: 1px 6px;
    border-radius: 4px;
}

/* Category Badge */
.category-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 11.5px;
    font-weight: 600;
    border-width: 1px;
    border-style: solid;
    white-space: nowrap;
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
    width: 32px;
    height: 32px;
    border-radius: 9px;
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
