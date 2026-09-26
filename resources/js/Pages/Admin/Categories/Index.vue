<script setup>
import showAlert from '@/Utils/sweetalert';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    categories: { type: Array, default: () => [] },
});

const dialog = ref(false);
const isEdit = ref(false);
const selectedCategory = ref(null);

const form = useForm({
    name: '',
    color: '#006837',
    description: '',
});

const openCreate = () => {
    isEdit.value = false;
    form.reset();
    form.color = '#006837';
    dialog.value = true;
};

const openEdit = (cat) => {
    isEdit.value = true;
    selectedCategory.value = cat;
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
        'Ya, Hapus Kategori'
    );

    if (confirmed) {
        form.delete(`/dashboard/categories/${cat.id}`);
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
</script>

<template>
    <Head title="Kategori Berita - Admin PCM Simo" />

    <div class="category-page-container">
        <!-- Top Page Header -->
        <div class="page-header">
            <div>
                <div class="d-flex align-center ga-2 mb-1">
                    <h1 class="page-title">Kategori Berita</h1>
                    <span class="total-badge">{{ categories.length }} Kategori</span>
                </div>
                <p class="page-subtitle">Kelola rubrikasi artikel, taksonomi, dan warta persyarikatan</p>
            </div>

            <button type="button" class="btn-create" @click="openCreate">
                <v-icon size="18" class="mr-1">mdi-plus</v-icon>
                <span>Tambah Kategori</span>
            </button>
        </div>

        <!-- Categories Table Card -->
        <div class="table-container">
            <div class="table-scroll">
                <table class="category-table">
                    <thead>
                        <tr>
                            <th class="col-name">Nama Kategori</th>
                            <th class="col-slug">Slug</th>
                            <th class="col-color">Warna Aksen</th>
                            <th class="col-count">Jumlah Artikel</th>
                            <th class="col-desc">Deskripsi</th>
                            <th class="col-action text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="cat in categories" :key="cat.id" class="table-row">
                            <!-- Category Name -->
                            <td class="col-name">
                                <div class="name-wrapper">
                                    <span
                                        class="color-dot"
                                        :style="{ backgroundColor: cat.color || '#006837' }"
                                    ></span>
                                    <span class="category-name-text">{{ cat.name }}</span>
                                </div>
                            </td>

                            <!-- Slug -->
                            <td class="col-slug">
                                <code class="slug-badge">{{ cat.slug }}</code>
                            </td>

                            <!-- Color Accent -->
                            <td class="col-color">
                                <span
                                    class="color-badge"
                                    :style="{
                                        backgroundColor: hexToRgba(cat.color, 0.08),
                                        color: cat.color || '#006837',
                                        borderColor: hexToRgba(cat.color, 0.25),
                                    }"
                                >
                                    <span
                                        class="color-preview-box"
                                        :style="{ backgroundColor: cat.color || '#006837' }"
                                    ></span>
                                    {{ cat.color || '#006837' }}
                                </span>
                            </td>

                            <!-- Articles Count -->
                            <td class="col-count">
                                <span class="count-badge">
                                    <v-icon size="13" class="mr-1 text-slate-400">mdi-newspaper-variant-outline</v-icon>
                                    {{ cat.articles_count ?? 0 }} artikel
                                </span>
                            </td>

                            <!-- Description -->
                            <td class="col-desc">
                                <span class="desc-text" :title="cat.description">
                                    {{ cat.description || '-' }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="col-action">
                                <div class="actions-group">
                                    <button
                                        type="button"
                                        class="action-btn action-btn--edit"
                                        title="Sunting Kategori"
                                        @click="openEdit(cat)"
                                    >
                                        <v-icon size="16">mdi-pencil-outline</v-icon>
                                    </button>
                                    <button
                                        type="button"
                                        class="action-btn action-btn--delete"
                                        title="Hapus Kategori"
                                        @click="deleteCategory(cat)"
                                    >
                                        <v-icon size="16">mdi-trash-can-outline</v-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="categories.length === 0">
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon-circle">
                                        <v-icon size="36" color="#94a3b8">mdi-tag-outline</v-icon>
                                    </div>
                                    <h3 class="empty-title">Belum ada kategori</h3>
                                    <p class="empty-desc">Tambahkan kategori baru untuk mengelompokkan berita Anda.</p>
                                    <button type="button" class="btn-create" @click="openCreate">
                                        <v-icon size="16" class="mr-1">mdi-plus</v-icon>
                                        Tambah Kategori
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div v-if="categories.length > 0" class="table-footer">
                <div class="footer-summary">
                    Total <span class="footer-highlight">{{ categories.length }}</span> kategori terdaftar
                </div>
            </div>
        </div>

        <!-- Create/Edit Dialog -->
        <v-dialog v-model="dialog" max-width="480">
            <v-card rounded="xl" class="pa-6 border border-slate-100">
                <div class="text-base font-bold text-slate-800 mb-4 pb-3 border-b border-slate-100 d-flex align-center ga-2">
                    <v-icon size="20" color="#006837">{{ isEdit ? 'mdi-pencil-outline' : 'mdi-folder-plus-outline' }}</v-icon>
                    <span>{{ isEdit ? 'Sunting Kategori' : 'Tambah Kategori Baru' }}</span>
                </div>
                <v-form @submit.prevent="submit">
                    <div class="mb-4">
                        <label class="input-label">
                            Nama Kategori <span class="text-rose-500">*</span>
                        </label>
                        <v-text-field
                            v-model="form.name"
                            density="compact"
                            variant="outlined"
                            placeholder="Contoh: Berita Cabang"
                            :error-messages="form.errors.name"
                            hide-details="auto"
                        />
                    </div>
                    <div class="mb-4">
                        <label class="input-label">
                            Warna Aksen (Hex)
                        </label>
                        <div class="d-flex align-center ga-2">
                            <input
                                v-model="form.color"
                                type="color"
                                class="color-picker-input"
                            />
                            <v-text-field
                                v-model="form.color"
                                density="compact"
                                variant="outlined"
                                placeholder="#006837"
                                :error-messages="form.errors.color"
                                hide-details="auto"
                                class="flex-1"
                            />
                        </div>
                    </div>
                    <div class="mb-5">
                        <label class="input-label">
                            Deskripsi Singkat
                        </label>
                        <v-textarea
                            v-model="form.description"
                            density="compact"
                            variant="outlined"
                            rows="2"
                            placeholder="Keterangan singkat tentang isi kategori ini..."
                            :error-messages="form.errors.description"
                            hide-details="auto"
                        />
                    </div>
                    <div class="d-flex justify-end ga-2 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            class="dialog-btn-cancel"
                            @click="dialog = false"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="dialog-btn-save"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Kategori' }}
                        </button>
                    </div>
                </v-form>
            </v-card>
        </v-dialog>
    </div>
</template>

<style scoped>
.category-page-container {
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

.category-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.category-table thead th {
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

.category-table td {
    padding: 14px 18px;
    vertical-align: middle;
}

/* Columns Sizing */
.col-name {
    min-width: 200px;
}

.col-slug {
    width: 180px;
}

.col-color {
    width: 140px;
}

.col-count {
    width: 150px;
}

.col-desc {
    min-width: 220px;
}

.col-action {
    width: 100px;
    white-space: nowrap;
    text-align: right;
}

/* Name */
.name-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

.color-dot {
    width: 10px;
    height: 10px;
    border-radius: 9999px;
    flex-shrink: 0;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
}

.category-name-text {
    font-size: 13.5px;
    font-weight: 600;
    color: #1e293b;
}

/* Slug */
.slug-badge {
    background: #f1f5f9;
    color: #475569;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 11.5px;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

/* Color Badge */
.color-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 9999px;
    font-size: 11.5px;
    font-weight: 600;
    border-width: 1px;
    border-style: solid;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

.color-preview-box {
    width: 8px;
    height: 8px;
    border-radius: 2px;
}

/* Count Badge */
.count-badge {
    display: inline-flex;
    align-items: center;
    background: #f8fafc;
    color: #334155;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

/* Description */
.desc-text {
    color: #64748b;
    font-size: 12.5px;
    display: block;
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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

/* Dialog & Form */
.input-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 6px;
}

.color-picker-input {
    width: 40px;
    height: 40px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    background: none;
    padding: 2px;
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

.dialog-btn-save {
    padding: 8px 18px;
    border-radius: 8px;
    border: none;
    background: #006837;
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 104, 55, 0.25);
    transition: all 0.15s ease;
}

.dialog-btn-save:hover {
    background: #00552d;
}
</style>
