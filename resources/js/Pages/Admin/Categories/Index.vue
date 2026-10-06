<script setup>
import showAlert from '@/Utils/sweetalert';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    categories: { type: Array, default: () => [] },
});

const dialog = ref(false);
const isEdit = ref(false);
const selectedCategory = ref(null);
const search = ref('');

const colorPalette = [
    '#006837', '#0A2540', '#D97706', '#059669', '#4338CA',
    '#DC2626', '#2563EB', '#7C3AED', '#DB2777', '#0891B2',
];

const form = useForm({
    name: '',
    color: '#006837',
    description: '',
});

const openCreate = () => {
    isEdit.value = false;
    form.reset();
    form.clearErrors();
    form.color = '#006837';
    dialog.value = true;
};

const openEdit = (cat) => {
    isEdit.value = true;
    selectedCategory.value = cat;
    form.clearErrors();
    form.name = cat.name;
    form.color = cat.color || '#006837';
    form.description = cat.description || '';
    dialog.value = true;
};

const submit = () => {
    if (isEdit.value) {
        form.put(`/dashboard/categories/${selectedCategory.value.id}`, {
            onSuccess: () => {
                dialog.value = false;
            },
        });
    } else {
        form.post('/dashboard/categories', {
            onSuccess: () => {
                dialog.value = false;
                form.reset();
            },
        });
    }
};

const deleteCategory = async (cat) => {
    const confirmed = await showAlert.confirm(
        'Hapus Kategori?',
        `Apakah Anda yakin ingin menghapus kategori "${cat.name}"? Kategori yang memiliki artikel tidak dapat dihapus.`,
        'Ya, Hapus Kategori',
    );

    if (confirmed) {
        form.delete(`/dashboard/categories/${cat.id}`);
    }
};

const totalArticles = computed(() => {
    return props.categories.reduce((acc, cat) => acc + (cat.articles_count ?? 0), 0);
});

const filteredCategories = computed(() => {
    if (!search.value.trim()) return props.categories;
    const q = search.value.toLowerCase();
    return props.categories.filter(c =>
        c.name.toLowerCase().includes(q) ||
        (c.description && c.description.toLowerCase().includes(q)) ||
        c.slug.toLowerCase().includes(q),
    );
});

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
</script>

<template>
    <Head title="Kategori Berita - Admin PCM Simo" />

    <div class="m3-page-container">
        <!-- ── Material 3 Top Page Header ── -->
        <header class="m3-header">
            <div class="m3-header-main">
                <div class="m3-header-titles">
                    <div class="d-flex align-center ga-2 mb-1 flex-wrap">
                        <span class="m3-header-badge">
                            <v-icon size="13" class="mr-2 text-emerald-700">mdi-tag-multiple-outline</v-icon>
                            Taksonomi Portal
                        </span>
                        <span class="m3-header-badge m3-header-badge--count">
                            {{ categories.length }} Kategori
                        </span>
                    </div>
                    <h1 class="m3-headline">Kategori Berita</h1>
                    <p class="m3-subhead">
                        Kelola rubrikasi, taksonomi warta dakwah, dan pewarnaan topik publikasi PCM Simo.
                    </p>
                </div>

                <div class="m3-header-actions">
                    <button type="button" class="m3-fab-extended" @click="openCreate">
                        <v-icon size="20" class="mr-2">mdi-plus</v-icon>
                        <span>Tambah Kategori</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- ── Material 3 Summary Quick Cards ── -->
        <section class="m3-summary-grid">
            <div class="m3-summary-tile" style="--tile-color: #006837; --tile-bg: #ecfdf5;">
                <div class="m3-summary-icon">
                    <v-icon size="24" color="#006837">mdi-tag-outline</v-icon>
                </div>
                <div>
                    <div class="m3-summary-num">{{ categories.length }}</div>
                    <div class="m3-summary-txt">Kategori Aktif</div>
                </div>
            </div>

            <div class="m3-summary-tile" style="--tile-color: #2563eb; --tile-bg: #eff6ff;">
                <div class="m3-summary-icon">
                    <v-icon size="24" color="#2563eb">mdi-newspaper-variant-outline</v-icon>
                </div>
                <div>
                    <div class="m3-summary-num">{{ totalArticles }}</div>
                    <div class="m3-summary-txt">Total Berita Terkategori</div>
                </div>
            </div>

            <div class="m3-summary-tile" style="--tile-color: #7c3aed; --tile-bg: #f5f3ff;">
                <div class="m3-summary-icon">
                    <v-icon size="24" color="#7c3aed">mdi-palette-outline</v-icon>
                </div>
                <div>
                    <div class="m3-summary-num">100%</div>
                    <div class="m3-summary-txt">Warna Aksen Unik</div>
                </div>
            </div>
        </section>

        <!-- ── Search & Filter Bar ── -->
        <div class="m3-toolbar">
            <div class="m3-search-bar">
                <v-icon size="18" class="m3-search-icon">mdi-magnify</v-icon>
                <input
                    v-model="search"
                    type="search"
                    placeholder="Cari nama kategori, slug, deskripsi..."
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
                            <th class="col-name">Nama Kategori</th>
                            <th class="col-slug">Slug URL</th>
                            <th class="col-color">Warna Aksen</th>
                            <th class="col-count">Jumlah Berita</th>
                            <th class="col-desc">Deskripsi</th>
                            <th class="col-action text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="cat in filteredCategories" :key="cat.id" class="m3-table-row">
                            <!-- Category Name -->
                            <td class="col-name">
                                <div class="m3-name-cell">
                                    <span
                                        class="m3-color-dot"
                                        :style="{ backgroundColor: cat.color || '#006837' }"
                                    ></span>
                                    <span class="m3-cat-title">{{ cat.name }}</span>
                                </div>
                            </td>

                            <!-- Slug -->
                            <td class="col-slug">
                                <code class="m3-slug-pill">{{ cat.slug }}</code>
                            </td>

                            <!-- Color Accent -->
                            <td class="col-color">
                                <span
                                    class="m3-color-tag"
                                    :style="{
                                        backgroundColor: hexToRgba(cat.color, 0.08),
                                        color: cat.color || '#006837',
                                        borderColor: hexToRgba(cat.color, 0.25),
                                    }"
                                >
                                    <span
                                        class="m3-color-chip"
                                        :style="{ backgroundColor: cat.color || '#006837' }"
                                    ></span>
                                    {{ cat.color || '#006837' }}
                                </span>
                            </td>

                            <!-- Count -->
                            <td class="col-count">
                                <span class="m3-count-badge">
                                    <v-icon size="14" class="mr-1.5 text-slate-400">mdi-newspaper-variant-outline</v-icon>
                                    {{ cat.articles_count ?? 0 }} artikel
                                </span>
                            </td>

                            <!-- Description -->
                            <td class="col-desc">
                                <span class="m3-desc-preview" :title="cat.description">
                                    {{ cat.description || '-' }}
                                </span>
                            </td>

                            <!-- Action -->
                            <td class="col-action text-right">
                                <div class="m3-actions-cluster justify-end">
                                    <button
                                        type="button"
                                        class="m3-icon-btn m3-icon-btn--edit"
                                        title="Sunting Kategori"
                                        @click="openEdit(cat)"
                                    >
                                        <v-icon size="16">mdi-pencil-outline</v-icon>
                                    </button>
                                    <button
                                        type="button"
                                        class="m3-icon-btn m3-icon-btn--delete"
                                        title="Hapus Kategori"
                                        @click="deleteCategory(cat)"
                                    >
                                        <v-icon size="16">mdi-trash-can-outline</v-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="filteredCategories.length === 0">
                            <td colspan="6">
                                <div class="m3-empty-state-table">
                                    <div class="m3-empty-art">
                                        <v-icon size="36" color="#94a3b8">mdi-tag-off-outline</v-icon>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-800 mb-1">
                                        {{ search ? `Tidak ada kategori sesuai "${search}"` : 'Belum ada kategori' }}
                                    </h3>
                                    <p class="text-xs text-slate-500 mb-4">Tambahkan rubrikasi artikel untuk mengelompokkan konten portal.</p>
                                    <button type="button" class="m3-btn-filled" @click="openCreate">
                                        <v-icon size="16" class="mr-2">mdi-plus</v-icon>
                                        Tambah Kategori Baru
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div v-if="filteredCategories.length > 0" class="m3-table-footer">
                <span>Menampilkan <b>{{ filteredCategories.length }}</b> dari total {{ categories.length }} kategori</span>
            </div>
        </div>

        <!-- ── Material Design 3 Modal Dialog ── -->
        <v-dialog v-model="dialog" max-width="520" transition="dialog-bottom-transition">
            <div class="m3-dialog-surface">
                <!-- Dialog Header -->
                <div class="m3-dialog-header">
                    <div class="m3-dialog-lead">
                        <div
                            class="m3-dialog-icon-badge"
                            :style="{ background: hexToRgba(form.color, 0.12), color: form.color || '#006837' }"
                        >
                            <v-icon size="24" :color="form.color || '#006837'">
                                {{ isEdit ? 'mdi-pencil-outline' : 'mdi-folder-plus-outline' }}
                            </v-icon>
                        </div>
                        <div>
                            <div class="m3-dialog-overline">{{ isEdit ? 'Mode Sunting' : 'Kategori Baru' }}</div>
                            <h2 class="m3-dialog-headline">{{ isEdit ? 'Sunting Kategori' : 'Tambah Kategori' }}</h2>
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
                    <form id="category-form" @submit.prevent="submit">
                        <div class="mb-4">
                            <label class="m3-field-label">
                                Nama Kategori <span class="text-rose-500">*</span>
                            </label>
                            <div class="m3-input-box" :class="{ 'm3-input-box--error': form.errors.name }">
                                <v-icon size="18" class="m3-input-icon">mdi-tag-outline</v-icon>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="m3-native-input"
                                    placeholder="Contoh: Berita Cabang, Tabligh & Tarjih..."
                                    required
                                />
                            </div>
                            <p v-if="form.errors.name" class="m3-error-text">{{ form.errors.name }}</p>
                        </div>

                        <!-- Color Preset Picker -->
                        <div class="mb-4">
                            <label class="m3-field-label">Warna Aksen Kategori</label>
                            <div class="m3-color-presets mb-2">
                                <button
                                    v-for="clr in colorPalette"
                                    :key="clr"
                                    type="button"
                                    class="m3-color-swatch"
                                    :class="{ 'm3-color-swatch--active': form.color.toLowerCase() === clr.toLowerCase() }"
                                    :style="{ backgroundColor: clr }"
                                    :title="clr"
                                    @click="form.color = clr"
                                >
                                    <v-icon v-if="form.color.toLowerCase() === clr.toLowerCase()" size="14" color="#ffffff">
                                        mdi-check
                                    </v-icon>
                                </button>
                            </div>

                            <div class="d-flex align-center ga-2">
                                <input
                                    v-model="form.color"
                                    type="color"
                                    class="m3-color-input-native"
                                />
                                <div class="m3-input-box flex-1" :class="{ 'm3-input-box--error': form.errors.color }">
                                    <v-icon size="18" class="m3-input-icon">mdi-pound</v-icon>
                                    <input
                                        v-model="form.color"
                                        type="text"
                                        class="m3-native-input font-mono"
                                        placeholder="#006837"
                                    />
                                </div>
                            </div>
                            <p v-if="form.errors.color" class="m3-error-text">{{ form.errors.color }}</p>
                        </div>

                        <!-- Description -->
                        <div class="mb-2">
                            <label class="m3-field-label">Deskripsi Singkat</label>
                            <div class="m3-input-box m3-input-box--textarea" :class="{ 'm3-input-box--error': form.errors.description }">
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    maxlength="255"
                                    class="m3-native-textarea"
                                    placeholder="Keterangan singkat cakupan konten rubrikasi ini..."
                                ></textarea>
                            </div>
                            <p v-if="form.errors.description" class="m3-error-text">{{ form.errors.description }}</p>
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
                            form="category-form"
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
                            <v-icon v-else size="18" class="mr-1.5">mdi-check</v-icon>
                            <span>{{ form.processing ? 'Menyimpan...' : (isEdit ? 'Perbarui Kategori' : 'Simpan Kategori') }}</span>
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

/* ── Top Header ── */
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

/* ── Summary Quick Cards ── */
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
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.m3-summary-tile:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
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
    margin-bottom: 20px;
    display: flex;
    justify-content: flex-end;
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

.m3-search-icon {
    position: absolute;
    left: 13px;
    color: #94a3b8;
}

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

.m3-table-scroll {
    overflow-x: auto;
}

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

.m3-table-row:last-child {
    border-bottom: none;
}

.m3-table-row:hover {
    background: #f8fafc;
}

.m3-table td {
    padding: 16px 20px;
    vertical-align: middle;
}

.col-name { min-width: 220px; }
.col-slug { width: 180px; }
.col-color { width: 140px; }
.col-count { width: 150px; }
.col-desc { min-width: 240px; }
.col-action { width: 100px; }

.m3-name-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.m3-color-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.m3-cat-title {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
}

.m3-slug-pill {
    background: #f1f5f9;
    color: #475569;
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-size: 11.5px;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

.m3-color-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 9999px;
    font-size: 11.5px;
    font-weight: 700;
    border-width: 1px;
    border-style: solid;
    font-family: ui-monospace, SFMono-Regular, monospace;
}

.m3-color-chip {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.m3-count-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f8fafc;
    color: #334155;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 11px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.m3-desc-preview {
    color: #64748b;
    font-size: 12.5px;
    display: block;
    max-width: 320px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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

.m3-input-box--textarea {
    padding: 10px 14px;
    align-items: flex-start;
}

.m3-input-icon {
    color: #94a3b8;
    margin-right: 10px;
    flex-shrink: 0;
}

.m3-native-input,
.m3-native-textarea {
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

.m3-error-text {
    font-size: 11.5px;
    color: #e11d48;
    margin: 4px 0 0;
}

/* Color Presets */
.m3-color-presets {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.m3-color-swatch {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 2px solid #ffffff;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.18);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.15s ease;
}

.m3-color-swatch:hover {
    transform: scale(1.15);
}

.m3-color-swatch--active {
    box-shadow: 0 0 0 2px #006837, 0 2px 6px rgba(0, 0, 0, 0.2);
}

.m3-color-input-native {
    width: 44px;
    height: 44px;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 3px;
    cursor: pointer;
    background: #ffffff;
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
