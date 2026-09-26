<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    articles: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ cari: '', kategori: 'semua', status: 'semua' }) },
});

const search = ref(props.filters.cari || '');
const selectedCategory = ref(props.filters.kategori || 'semua');
const selectedStatus = ref(props.filters.status || 'semua');

const deleteDialog = ref(false);
const articleToDelete = ref(null);

const totalCount = computed(() => props.articles.length);
const publishedCount = computed(() => props.articles.filter(a => a.status === 'published').length);
const draftCount = computed(() => props.articles.filter(a => a.status === 'draft').length);

const hasActiveFilter = computed(() => {
    return (search.value && search.value.trim() !== '') ||
        (selectedCategory.value && selectedCategory.value !== 'semua') ||
        (selectedStatus.value && selectedStatus.value !== 'semua');
});

const formatFullDate = (dateStr) => {
    if (!dateStr) return { date: '-', time: '' };
    try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return { date: '-', time: '' };
        const dateFormatted = date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
        const timeFormatted = date.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
        }) + ' WIB';
        return { date: dateFormatted, time: timeFormatted };
    } catch {
        return { date: dateStr, time: '' };
    }
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

const applyFilter = () => {
    router.get(
        '/dashboard/news',
        {
            cari: search.value,
            kategori: selectedCategory.value,
            status: selectedStatus.value,
        },
        { preserveState: true, replace: true }
    );
};

let searchDebounce = null;
const handleSearchInput = () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        applyFilter();
    }, 350);
};

const clearSearch = () => {
    search.value = '';
    applyFilter();
};

const setStatusTab = (status) => {
    if (selectedStatus.value === status) return;
    selectedStatus.value = status;
    applyFilter();
};

const resetFilters = () => {
    search.value = '';
    selectedCategory.value = 'semua';
    selectedStatus.value = 'semua';
    applyFilter();
};

const toggleStatus = (id) => {
    router.patch(`/dashboard/news/${id}/toggle-publish`, {}, { preserveScroll: true });
};

const confirmDelete = (article) => {
    articleToDelete.value = article;
    deleteDialog.value = true;
};

const doDelete = () => {
    if (!articleToDelete.value) return;
    router.delete(`/dashboard/news/${articleToDelete.value.id}`, {
        onSuccess: () => {
            deleteDialog.value = false;
            articleToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="Kelola Berita - Admin PCM Simo" />

    <div class="news-page-container">
        <!-- Top Page Header -->
        <div class="page-header">
            <div>
                <div class="d-flex align-center ga-2 mb-1">
                    <h1 class="page-title">Manajemen Berita</h1>
                    <span class="total-badge">{{ totalCount }} Data</span>
                </div>
                <p class="page-subtitle">Kelola artikel warta, opini dakwah, dan publikasi PCM Simo</p>
            </div>

            <Link href="/dashboard/news/create" class="text-decoration-none">
                <button class="btn-create">
                    <v-icon size="18" class="mr-1">mdi-plus</v-icon>
                    <span>Tambah Berita Baru</span>
                </button>
            </Link>
        </div>

        <!-- Filter & Control Toolbar -->
        <div class="toolbar-card">
            <!-- Left: Search & Category -->
            <div class="toolbar-controls">
                <!-- Modern Search Bar -->
                <div class="search-box">
                    <v-icon size="18" class="search-icon">mdi-magnify</v-icon>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari judul berita atau cuplikan..."
                        class="search-input"
                        @input="handleSearchInput"
                    />
                    <button
                        v-if="search"
                        class="search-clear"
                        type="button"
                        @click="clearSearch"
                        title="Hapus pencarian"
                    >
                        <v-icon size="16">mdi-close-circle</v-icon>
                    </button>
                </div>

                <!-- Category Selector -->
                <div class="select-wrapper">
                    <v-icon size="16" class="select-icon">mdi-folder-outline</v-icon>
                    <select
                        v-model="selectedCategory"
                        class="custom-select"
                        @change="applyFilter"
                    >
                        <option value="semua">Semua Kategori</option>
                        <option
                            v-for="cat in categories"
                            :key="cat.id"
                            :value="cat.id"
                        >
                            {{ cat.name }}
                        </option>
                    </select>
                </div>

                <!-- Reset Button -->
                <button
                    v-if="hasActiveFilter"
                    type="button"
                    class="btn-reset-filter"
                    @click="resetFilters"
                    title="Reset semua filter"
                >
                    <v-icon size="15">mdi-filter-off-outline</v-icon>
                    <span>Reset</span>
                </button>
            </div>

            <!-- Right: Status Segmented Tabs -->
            <div class="status-tabs">
                <button
                    type="button"
                    :class="['status-tab', { 'status-tab--active': selectedStatus === 'semua' }]"
                    @click="setStatusTab('semua')"
                >
                    <span>Semua</span>
                </button>
                <button
                    type="button"
                    :class="['status-tab', { 'status-tab--active': selectedStatus === 'published' }]"
                    @click="setStatusTab('published')"
                >
                    <span class="tab-indicator tab-indicator--green"></span>
                    <span>Tayang</span>
                    <span v-if="publishedCount > 0" class="tab-count">{{ publishedCount }}</span>
                </button>
                <button
                    type="button"
                    :class="['status-tab', { 'status-tab--active': selectedStatus === 'draft' }]"
                    @click="setStatusTab('draft')"
                >
                    <span class="tab-indicator tab-indicator--slate"></span>
                    <span>Draft</span>
                    <span v-if="draftCount > 0" class="tab-count">{{ draftCount }}</span>
                </button>
            </div>
        </div>

        <!-- News Table Card -->
        <div class="table-container">
            <div class="table-scroll">
                <table class="news-table">
                    <thead>
                        <tr>
                            <th class="col-thumb">Sampul</th>
                            <th class="col-title">Judul Berita</th>
                            <th class="col-cat">Kategori</th>
                            <th class="col-date">Tanggal Terbit</th>
                            <th class="col-status">Status</th>
                            <th class="col-action text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="art in articles" :key="art.id" class="table-row">
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
                                        <v-icon size="20" color="#94a3b8">mdi-image-outline</v-icon>
                                    </div>
                                </div>
                            </td>

                            <!-- Title & Meta -->
                            <td class="col-title">
                                <div class="title-cell">
                                    <Link :href="`/dashboard/news/${art.id}/edit`" class="article-title-link">
                                        {{ art.title }}
                                    </Link>
                                    <div class="article-meta">
                                        <span class="meta-item">
                                            <v-icon size="13" class="meta-icon">mdi-eye-outline</v-icon>
                                            {{ (art.views || 0).toLocaleString('id-ID') }} pembaca
                                        </span>
                                        <span v-if="art.is_featured" class="headline-pill">
                                            <v-icon size="11" class="mr-0.5">mdi-star</v-icon>
                                            Headline
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
                                <div class="date-cell">
                                    <div class="date-primary">
                                        <v-icon size="14" class="mr-1 text-slate-400">mdi-calendar-blank-outline</v-icon>
                                        {{ formatFullDate(art.published_at).date }}
                                    </div>
                                    <div class="date-secondary">
                                        {{ formatFullDate(art.published_at).time }}
                                    </div>
                                </div>
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
                                    title="Klik untuk mengubah status publikasi"
                                >
                                    <span class="status-pulse-dot"></span>
                                    <span class="status-text">{{ art.status === 'published' ? 'Tayang' : 'Draft' }}</span>
                                </button>
                            </td>

                            <!-- Actions -->
                            <td class="col-action">
                                <div class="actions-group">
                                    <a
                                        :href="`/berita/${art.slug}`"
                                        target="_blank"
                                        class="action-btn action-btn--view"
                                        title="Pratinjau Halaman Berita"
                                    >
                                        <v-icon size="16">mdi-eye-outline</v-icon>
                                    </a>
                                    <Link
                                        :href="`/dashboard/news/${art.id}/edit`"
                                        class="action-btn action-btn--edit"
                                        title="Sunting Berita"
                                    >
                                        <v-icon size="16">mdi-pencil-outline</v-icon>
                                    </Link>
                                    <button
                                        type="button"
                                        class="action-btn action-btn--delete"
                                        title="Hapus Berita"
                                        @click="confirmDelete(art)"
                                    >
                                        <v-icon size="16">mdi-trash-can-outline</v-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="articles.length === 0">
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon-circle">
                                        <v-icon size="36" color="#94a3b8">mdi-newspaper-variant-outline</v-icon>
                                    </div>
                                    <h3 class="empty-title">Tidak ada berita ditemukan</h3>
                                    <p class="empty-desc">
                                        {{ hasActiveFilter ? 'Tidak ada berita yang cocok dengan kriteria filter yang Anda pilih.' : 'Belum ada warta berita yang ditambahkan ke portal ini.' }}
                                    </p>
                                    <div class="empty-actions">
                                        <button
                                            v-if="hasActiveFilter"
                                            type="button"
                                            class="empty-btn-reset"
                                            @click="resetFilters"
                                        >
                                            Reset Filter
                                        </button>
                                        <Link href="/dashboard/news/create" class="text-decoration-none">
                                            <button class="empty-btn-create">
                                                <v-icon size="16" class="mr-1">mdi-plus</v-icon>
                                                Tambah Berita Baru
                                            </button>
                                        </Link>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer / Summary Bar -->
            <div v-if="articles.length > 0" class="table-footer">
                <div class="footer-summary">
                    Menampilkan <span class="footer-highlight">{{ articles.length }}</span> berita
                </div>
                <div class="footer-stats">
                    <span class="footer-dot footer-dot--green"></span>
                    <span class="footer-stat-text">{{ publishedCount }} Tayang</span>
                    <span class="footer-separator">•</span>
                    <span class="footer-dot footer-dot--slate"></span>
                    <span class="footer-stat-text">{{ draftCount }} Draft</span>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <v-dialog v-model="deleteDialog" max-width="440">
            <v-card rounded="xl" class="pa-6 border border-slate-100">
                <div class="d-flex align-center ga-3 mb-3">
                    <div class="dialog-icon-danger">
                        <v-icon color="#e11d48" size="24">mdi-alert-circle-outline</v-icon>
                    </div>
                    <div>
                        <div class="text-base font-bold text-slate-800">Hapus Berita Ini?</div>
                        <div class="text-xs text-slate-500">Artikel yang dihapus tidak dapat dipulihkan kembali.</div>
                    </div>
                </div>

                <div class="dialog-quote">
                    "{{ articleToDelete?.title }}"
                </div>

                <div class="d-flex justify-end ga-2 pt-2">
                    <button
                        type="button"
                        class="dialog-btn-cancel"
                        @click="deleteDialog = false"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="dialog-btn-delete"
                        @click="doDelete"
                    >
                        Ya, Hapus Sekarang
                    </button>
                </div>
            </v-card>
        </v-dialog>
    </div>
</template>

<style scoped>
.news-page-container {
    padding: 28px 32px;
    max-width: 1280px;
    margin: 0 auto;
}

/* ── Top Header ── */
.page-header {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 24px;
}

@media (min-width: 640px) {
    .page-header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.page-title {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.3px;
    margin: 0;
}

.total-badge {
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 9999px;
    border: 1px solid #e2e8f0;
}

.page-subtitle {
    font-size: 13px;
    color: #64748b;
    margin: 3px 0 0 0;
}

.btn-create {
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

.btn-create:hover {
    background: #00552d;
    box-shadow: 0 4px 12px rgba(0, 104, 55, 0.35);
    transform: translateY(-1px);
}

.btn-create:active {
    transform: translateY(0);
}

/* ── Toolbar Card ── */
.toolbar-card {
    display: flex;
    flex-direction: column;
    gap: 14px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 14px 18px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

@media (min-width: 900px) {
    .toolbar-card {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.toolbar-controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    flex: 1;
}

/* Search Box */
.search-box {
    position: relative;
    display: flex;
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0 12px;
    width: 100%;
    max-width: 340px;
    height: 40px;
    transition: all 0.2s ease;
}

.search-box:focus-within {
    background: #ffffff;
    border-color: #006837;
    box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.1);
}

.search-icon {
    color: #94a3b8;
    margin-right: 8px;
    flex-shrink: 0;
}

.search-input {
    width: 100%;
    border: none;
    background: transparent;
    font-size: 13px;
    color: #1e293b;
    outline: none;
    font-family: inherit;
}

.search-input::placeholder {
    color: #94a3b8;
}

.search-clear {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    transition: color 0.15s ease;
}

.search-clear:hover {
    color: #475569;
}

/* Category Select */
.select-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    height: 40px;
    padding: 0 12px;
    transition: all 0.2s ease;
}

.select-wrapper:focus-within {
    background: #ffffff;
    border-color: #006837;
    box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.1);
}

.select-icon {
    color: #94a3b8;
    margin-right: 8px;
    flex-shrink: 0;
}

.custom-select {
    border: none;
    background: transparent;
    font-size: 13px;
    color: #334155;
    font-weight: 500;
    outline: none;
    cursor: pointer;
    font-family: inherit;
    padding-right: 8px;
}

/* Reset Filter */
.btn-reset-filter {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #fef2f2;
    border: 1px solid #fecdd3;
    color: #e11d48;
    font-size: 12px;
    font-weight: 600;
    padding: 8px 12px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.btn-reset-filter:hover {
    background: #ffe4e6;
}

/* Status Tabs */
.status-tabs {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    padding: 3px;
    border-radius: 10px;
    gap: 2px;
    border: 1px solid #e2e8f0;
}

.status-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: all 0.15s ease;
}

.status-tab:hover {
    color: #1e293b;
}

.status-tab--active {
    background: #ffffff;
    color: #0f172a;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.tab-indicator {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
}

.tab-indicator--green {
    background: #10b981;
}

.tab-indicator--slate {
    background: #94a3b8;
}

.tab-count {
    background: #f1f5f9;
    color: #475569;
    font-size: 10px;
    padding: 1px 6px;
    border-radius: 9999px;
    font-weight: 700;
}

.status-tab--active .tab-count {
    background: #e2e8f0;
    color: #1e293b;
}

/* ── Table Container ── */
.table-container {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    overflow: hidden;
}

.table-scroll {
    overflow-x: auto;
}

.news-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.news-table thead th {
    background: #f8fafc;
    padding: 14px 18px;
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

.news-table td {
    padding: 14px 18px;
    vertical-align: middle;
}

/* Columns Sizing */
.col-thumb {
    width: 80px;
}

.col-title {
    min-width: 280px;
}

.col-cat {
    width: 160px;
    white-space: nowrap;
}

.col-date {
    width: 160px;
    white-space: nowrap;
}

.col-status {
    width: 120px;
    white-space: nowrap;
}

.col-action {
    width: 130px;
    white-space: nowrap;
    text-align: right;
}

/* Thumbnail */
.thumb-wrapper {
    width: 64px;
    height: 44px;
    border-radius: 10px;
    overflow: hidden;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
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
    gap: 4px;
}

.article-title-link {
    font-size: 13.5px;
    font-weight: 600;
    color: #1e293b;
    text-decoration: none;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
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
    flex-wrap: wrap;
}

.meta-item {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 11px;
    color: #94a3b8;
    font-weight: 500;
}

.meta-icon {
    color: #94a3b8;
}

.headline-pill {
    display: inline-flex;
    align-items: center;
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
    font-size: 10px;
    font-weight: 700;
    padding: 1px 7px;
    border-radius: 9999px;
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
}

.category-dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    flex-shrink: 0;
}

/* Date Cell */
.date-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.date-primary {
    display: flex;
    align-items: center;
    font-size: 12.5px;
    font-weight: 500;
    color: #334155;
}

.date-secondary {
    font-size: 11px;
    color: #94a3b8;
    padding-left: 18px;
}

/* Status Pill */
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 9999px;
    font-size: 11.5px;
    font-weight: 700;
    border-width: 1px;
    border-style: solid;
    cursor: pointer;
    background: transparent;
    transition: all 0.2s ease;
}

.status-pill:hover {
    transform: scale(1.03);
}

.status-pill--published {
    background: #ecfdf5;
    color: #047857;
    border-color: #a7f3d0;
}

.status-pill--published:hover {
    background: #d1fae5;
}

.status-pill--published .status-pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 9999px;
    background: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
}

.status-pill--draft {
    background: #f8fafc;
    color: #64748b;
    border-color: #cbd5e1;
}

.status-pill--draft:hover {
    background: #f1f5f9;
}

.status-pill--draft .status-pulse-dot {
    width: 7px;
    height: 7px;
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

.action-btn--view {
    color: #64748b;
}

.action-btn--view:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #e2e8f0;
}

.action-btn--edit {
    color: #0284c7;
}

.action-btn--edit:hover {
    background: #f0f9ff;
    color: #0369a1;
    border-color: #bae6fd;
}

.action-btn--delete {
    color: #94a3b8;
}

.action-btn--delete:hover {
    background: #fff1f2;
    color: #e11d48;
    border-color: #fecdd3;
}

/* Empty State */
.empty-state {
    padding: 56px 20px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.empty-icon-circle {
    width: 64px;
    height: 64px;
    border-radius: 9999px;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    border: 1px solid #e2e8f0;
}

.empty-title {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 6px 0;
}

.empty-desc {
    font-size: 13px;
    color: #64748b;
    max-width: 400px;
    margin: 0 0 20px 0;
    line-height: 1.5;
}

.empty-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.empty-btn-reset {
    padding: 8px 16px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}

.empty-btn-reset:hover {
    background: #f8fafc;
}

.empty-btn-create {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    border-radius: 10px;
    border: none;
    background: #006837;
    color: #ffffff;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}

.empty-btn-create:hover {
    background: #00552d;
}

/* Table Footer */
.table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    font-size: 12px;
    color: #64748b;
}

.footer-highlight {
    font-weight: 700;
    color: #1e293b;
}

.footer-stats {
    display: flex;
    align-items: center;
    gap: 6px;
}

.footer-dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
}

.footer-dot--green {
    background: #10b981;
}

.footer-dot--slate {
    background: #94a3b8;
}

.footer-stat-text {
    font-weight: 600;
    color: #475569;
}

.footer-separator {
    color: #cbd5e1;
    margin: 0 2px;
}

/* Dialog Styling */
.dialog-icon-danger {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #ffe4e6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.dialog-quote {
    font-size: 13px;
    color: #334155;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 12px 14px;
    border-radius: 10px;
    font-weight: 500;
    margin-bottom: 20px;
}

.dialog-btn-cancel {
    padding: 8px 16px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: transparent;
    color: #475569;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}

.dialog-btn-cancel:hover {
    background: #f1f5f9;
}

.dialog-btn-delete {
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    background: #e11d48;
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(225, 29, 72, 0.25);
    transition: all 0.15s ease;
}

.dialog-btn-delete:hover {
    background: #be123c;
}
</style>
