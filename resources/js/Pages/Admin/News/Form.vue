<script setup>
import { showAlert } from '@/Utils/sweetalert';
import RichEditor from '@/Components/RichEditor.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    isEdit: { type: Boolean, default: false },
    article: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
});

const form = useForm({
    title: props.article?.title || '',
    category_id: props.article?.category_id || (props.categories[0]?.id || ''),
    excerpt: props.article?.excerpt || '',
    content: props.article?.content || '',
    image: null,
    image_url: props.article?.image_url || '',
    is_featured: Boolean(props.article?.is_featured || false),
    status: props.article?.status || 'published',
});

const imagePreview = ref(props.article?.image_url || '');
const fileInput = ref(null);
const categoryDropdownRef = ref(null);
const isCategoryDropdownOpen = ref(false);

const selectedCategoryObj = computed(() => {
    return props.categories.find((c) => c.id === form.category_id) || props.categories[0] || null;
});

const toggleCategoryDropdown = () => {
    isCategoryDropdownOpen.value = !isCategoryDropdownOpen.value;
};

const selectCategory = (cat) => {
    form.category_id = cat.id;
    isCategoryDropdownOpen.value = false;
};

const handleClickOutside = (e) => {
    if (categoryDropdownRef.value && !categoryDropdownRef.value.contains(e.target)) {
        isCategoryDropdownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const wordCount = computed(() => {
    if (!form.content) return 0;
    return form.content.trim().split(/\s+/).filter(Boolean).length;
});

const triggerFileInput = () => {
    fileInput.value?.click();
};

const handleImageChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;

    if (!file.type.match(/^image\/(jpeg|png|webp|jpg)$/i)) {
        showAlert.warning(
            'Format Tidak Didukung',
            'Format foto sampul harus berupa JPG, PNG, atau WebP.'
        );
        if (fileInput.value) fileInput.value.value = '';
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        showAlert.warning(
            'Ukuran Terlalu Besar',
            'Ukuran foto sampul melebihi batas 5MB. Silakan pilih foto dengan resolusi atau ukuran lebih kecil.'
        );
        if (fileInput.value) fileInput.value.value = '';
        return;
    }

    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
    showAlert.toast('Foto sampul berhasil dipilih', 'success');
};

const clearImage = () => {
    form.image = null;
    imagePreview.value = '';
    if (fileInput.value) fileInput.value.value = '';
};

const submit = (overrideStatus = null) => {
    if (overrideStatus) {
        form.status = overrideStatus;
    }

    if (props.isEdit) {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(`/dashboard/news/${props.article.id}`, {
            onError: (errors) => {
                const firstError = Object.values(errors)[0] || 'Mohon periksa kembali isian formulir.';
                showAlert.error('Gagal Menyimpan Berita', firstError);
            },
        });
    } else {
        form.post('/dashboard/news', {
            onError: (errors) => {
                const firstError = Object.values(errors)[0] || 'Mohon periksa kembali isian formulir.';
                showAlert.error('Gagal Menyimpan Berita', firstError);
            },
        });
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Sunting Berita - Admin PCM Simo' : 'Tulis Berita Baru - Admin PCM Simo'" />

    <div class="form-page-container">
        <!-- Top Sticky Action Bar & Breadcrumbs -->
        <div class="form-header-bar">
            <div class="header-left">
                <!-- Breadcrumbs -->
                <nav class="breadcrumb-trail">
                    <Link href="/dashboard/news" class="trail-item trail-link">
                        <v-icon size="14" class="mr-1 text-slate-400">mdi-newspaper-variant-outline</v-icon>
                        <span>Berita</span>
                    </Link>
                    <v-icon size="13" class="trail-separator">mdi-chevron-right</v-icon>
                    <span class="trail-item trail-current">{{ isEdit ? 'Sunting Berita' : 'Tulis Baru' }}</span>
                </nav>

                <div class="d-flex align-center ga-3 mt-1.5">
                    <h1 class="header-main-title">
                        {{ isEdit ? 'Sunting Berita' : 'Tulis Berita Baru' }}
                    </h1>
                    <span
                        :class="[
                            'status-indicator-badge',
                            form.status === 'published' ? 'status-indicator--published' : 'status-indicator--draft'
                        ]"
                    >
                        <span class="pulse-dot"></span>
                        {{ form.status === 'published' ? 'Siap Tayang' : 'Draft' }}
                    </span>
                </div>
            </div>

            <!-- Header Quick Action Buttons -->
            <div class="header-actions">
                <Link href="/dashboard/news" class="btn-secondary-action">
                    <v-icon size="16" class="mr-1">mdi-arrow-left</v-icon>
                    <span>Kembali</span>
                </Link>

                <button
                    v-if="form.status === 'published'"
                    type="button"
                    class="btn-secondary-action"
                    :disabled="form.processing"
                    @click="submit('draft')"
                    title="Simpan sebagai draft terlebih dahulu"
                >
                    <v-icon size="16" class="mr-1">mdi-file-document-edit-outline</v-icon>
                    <span>Simpan Draft</span>
                </button>

                <button
                    type="button"
                    class="btn-primary-action"
                    :disabled="form.processing"
                    @click="submit()"
                >
                    <v-progress-circular
                        v-if="form.processing"
                        indeterminate
                        size="15"
                        width="2"
                        color="white"
                        class="mr-2"
                    />
                    <v-icon v-else size="17" class="mr-1.5">mdi-check-circle-outline</v-icon>
                    <span>{{ isEdit ? 'Simpan Perubahan' : (form.status === 'published' ? 'Terbitkan Berita' : 'Simpan Draft') }}</span>
                </button>
            </div>
        </div>

        <!-- Form Layout Grid -->
        <form @submit.prevent="submit()">
            <div class="form-grid">
                <!-- Left Column: Main Article Body -->
                <div class="main-column">
                    <div class="content-card">
                        <!-- Judul Berita -->
                        <div class="input-block">
                            <div class="label-row">
                                <label for="news-title" class="input-label-primary">
                                    Judul Berita <span class="required-asterisk">*</span>
                                </label>
                                <span class="input-counter">{{ form.title.length }} karakter</span>
                            </div>
                            <p class="input-sublabel">Tuliskan judul yang padat, lugas, dan memikat pembaca.</p>
                            <input
                                id="news-title"
                                v-model="form.title"
                                type="text"
                                class="text-input text-input--title"
                                placeholder="Contoh: Musyawarah Cabang PCM Simo Berlangsung Khidmat"
                                required
                                autocomplete="off"
                            />
                            <p v-if="form.errors.title" class="field-error-message">{{ form.errors.title }}</p>
                        </div>

                        <!-- Ringkasan Singkat (Lead / Excerpt) -->
                        <div class="input-block">
                            <div class="label-row">
                                <label for="news-excerpt" class="input-label-primary">
                                    Ringkasan Singkat (Lead / Cuplikan)
                                </label>
                                <span class="input-counter">{{ form.excerpt.length }} karakter</span>
                            </div>
                            <p class="input-sublabel">Intisari 1–2 kalimat untuk kartu berita di beranda dan pratinjau media sosial.</p>
                            <textarea
                                id="news-excerpt"
                                v-model="form.excerpt"
                                rows="3"
                                class="text-input text-textarea--excerpt"
                                placeholder="Tuliskan intisari atau ringkasan 1-2 kalimat dari artikel berita ini..."
                            ></textarea>
                            <p v-if="form.errors.excerpt" class="field-error-message">{{ form.errors.excerpt }}</p>
                        </div>

                        <!-- Isi Lengkap Berita (Canvas WYSIWYG) -->
                        <div class="input-block mb-0">
                            <div class="label-row mb-1">
                                <label class="input-label-primary">
                                    Naskah Berita Lengkap <span class="required-asterisk">*</span>
                                </label>
                                <span class="text-xs text-slate-500 font-medium">Canvas Editor Aktif</span>
                            </div>
                            <p class="input-sublabel">Tuliskan naskah berita lengkap. Gunakan toolbar di atas canvas untuk format teks, kutipan, foto, dan galeri.</p>

                            <!-- Rich Editor Component -->
                            <RichEditor
                                v-model="form.content"
                                placeholder="Mulai menuliskan naskah berita di canvas ini... (Gunakan toolbar di atas untuk Bold, Heading, Sisipkan Foto, atau Galeri)"
                            />
                            <p v-if="form.errors.content" class="field-error-message">{{ form.errors.content }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Publishing Sidebar -->
                <div class="sidebar-column">
                    <!-- Card 1: Pengaturan Penerbitan & Kategori -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <div class="header-icon-circle">
                                <v-icon size="18" color="#006837">mdi-send-check-outline</v-icon>
                            </div>
                            <div>
                                <h2 class="sidebar-card-title">Pengaturan Publikasi</h2>
                                <p class="sidebar-card-subtitle">Status tayang dan rubrikasi berita</p>
                            </div>
                        </div>

                        <!-- Status Publikasi Segmented Tabs -->
                        <div class="settings-group">
                            <label class="sidebar-field-label">Status Tayang</label>
                            <div class="segmented-control">
                                <button
                                    type="button"
                                    :class="['segmented-btn', { 'segmented-btn--active-green': form.status === 'published' }]"
                                    @click="form.status = 'published'"
                                >
                                    <span class="status-dot dot-green"></span>
                                    <span>Tayang Sekarang</span>
                                </button>
                                <button
                                    type="button"
                                    :class="['segmented-btn', { 'segmented-btn--active-slate': form.status === 'draft' }]"
                                    @click="form.status = 'draft'"
                                >
                                    <span class="status-dot dot-slate"></span>
                                    <span>Simpan Draft</span>
                                </button>
                            </div>
                            <p v-if="form.errors.status" class="field-error-message">{{ form.errors.status }}</p>
                        </div>

                        <!-- Custom Rubrik / Kategori Dropdown (NO native browser select!) -->
                        <div class="settings-group" ref="categoryDropdownRef">
                            <label class="sidebar-field-label">
                                Rubrik / Kategori <span class="required-asterisk">*</span>
                            </label>

                            <!-- Custom Select Trigger -->
                            <div class="custom-dropdown-container">
                                <button
                                    type="button"
                                    class="custom-dropdown-trigger"
                                    :class="{ 'custom-dropdown-trigger--open': isCategoryDropdownOpen }"
                                    @click="toggleCategoryDropdown"
                                >
                                    <div class="d-flex align-center ga-2 text-truncate">
                                        <span
                                            class="category-color-dot"
                                            :style="{ backgroundColor: selectedCategoryObj?.color || '#006837' }"
                                        ></span>
                                        <span class="font-medium text-slate-800 text-sm">
                                            {{ selectedCategoryObj?.name || 'Pilih Kategori...' }}
                                        </span>
                                    </div>
                                    <v-icon
                                        size="18"
                                        color="#64748b"
                                        :class="['dropdown-chevron', { 'dropdown-chevron--flipped': isCategoryDropdownOpen }]"
                                    >
                                        mdi-chevron-down
                                    </v-icon>
                                </button>

                                <!-- Custom Dropdown Menu -->
                                <div v-if="isCategoryDropdownOpen" class="custom-dropdown-menu">
                                    <div class="dropdown-menu-header">Pilih Rubrik Kategori</div>
                                    <div class="dropdown-menu-list">
                                        <div
                                            v-for="cat in categories"
                                            :key="cat.id"
                                            :class="['dropdown-item', { 'dropdown-item--selected': form.category_id === cat.id }]"
                                            @click="selectCategory(cat)"
                                        >
                                            <div class="d-flex align-center ga-2.5">
                                                <span
                                                    class="category-color-dot"
                                                    :style="{ backgroundColor: cat.color || '#006837' }"
                                                ></span>
                                                <span class="item-name">{{ cat.name }}</span>
                                            </div>
                                            <v-icon
                                                v-if="form.category_id === cat.id"
                                                size="16"
                                                color="#006837"
                                            >
                                                mdi-check
                                            </v-icon>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-if="form.errors.category_id" class="field-error-message">{{ form.errors.category_id }}</p>
                        </div>

                        <!-- Berita Utama (Headline Switch) -->
                        <div class="headline-box">
                            <label class="headline-label-toggle">
                                <input
                                    v-model="form.is_featured"
                                    type="checkbox"
                                    class="headline-checkbox"
                                />
                                <div class="headline-text-content">
                                    <div class="d-flex align-center ga-1.5 font-bold text-xs text-amber-900">
                                        <v-icon size="16" color="#d97706">mdi-star-circle</v-icon>
                                        <span>Berita Utama (Headline)</span>
                                    </div>
                                    <p class="headline-subtext">Tampilkan di slider carousel beranda utama website</p>
                                </div>
                            </label>
                        </div>

                        <!-- Main Submit Button inside Sidebar -->
                        <div class="sidebar-action-divider"></div>
                        <button
                            type="button"
                            class="btn-sidebar-submit"
                            :disabled="form.processing"
                            @click="submit()"
                        >
                            <v-progress-circular
                                v-if="form.processing"
                                indeterminate
                                size="16"
                                width="2"
                                color="white"
                                class="mr-2"
                            />
                            <v-icon v-else size="18" class="mr-1.5">mdi-check-circle-outline</v-icon>
                            <span>{{ isEdit ? 'Simpan Perubahan' : (form.status === 'published' ? 'Terbitkan Berita Sekarang' : 'Simpan sebagai Draft') }}</span>
                        </button>
                    </div>

                    <!-- Card 2: Foto Sampul (Cover Image) -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <div class="header-icon-circle">
                                <v-icon size="18" color="#006837">mdi-image-multiple-outline</v-icon>
                            </div>
                            <div>
                                <h2 class="sidebar-card-title">Foto Sampul (Cover)</h2>
                                <p class="sidebar-card-subtitle">Format lanskap 16:9 disarankan</p>
                            </div>
                        </div>

                        <!-- Hidden Native File Input -->
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/png, image/jpeg, image/jpg, image/webp"
                            class="sr-only"
                            @change="handleImageChange"
                        />

                        <!-- Image Preview Area -->
                        <div v-if="imagePreview" class="cover-preview-card">
                            <div class="preview-img-container">
                                <img :src="imagePreview" alt="Foto Sampul" class="preview-img" />
                            </div>
                            <div class="preview-actions-bar">
                                <button
                                    type="button"
                                    class="preview-btn preview-btn--change"
                                    @click="triggerFileInput"
                                >
                                    <v-icon size="15" class="mr-1">mdi-camera-retake-outline</v-icon>
                                    <span>Ganti Foto</span>
                                </button>
                                <button
                                    type="button"
                                    class="preview-btn preview-btn--remove"
                                    @click="clearImage"
                                    title="Hapus foto ini"
                                >
                                    <v-icon size="15">mdi-trash-can-outline</v-icon>
                                </button>
                            </div>
                        </div>

                        <!-- Empty Dropzone State -->
                        <div
                            v-else
                            class="upload-dropzone"
                            @click="triggerFileInput"
                        >
                            <div class="upload-icon-circle">
                                <v-icon size="26" color="#006837">mdi-cloud-upload-outline</v-icon>
                            </div>
                            <span class="dropzone-main-text">Pilih File Foto Sampul</span>
                            <span class="dropzone-sub-text">Klik di sini untuk memilih gambar</span>
                            <div class="dropzone-formats">
                                <span class="format-chip">JPG</span>
                                <span class="format-chip">PNG</span>
                                <span class="format-chip">WebP</span>
                            </div>
                        </div>

                        <p v-if="form.errors.image" class="field-error-message mt-2">{{ form.errors.image }}</p>

                        <!-- Compression Notice -->
                        <div class="compression-notice mt-3">
                            <v-icon size="14" color="#006837" class="mr-1.5 flex-shrink-0">mdi-lightning-bolt-circle</v-icon>
                            <span>Otomatis dioptimasi & dikonversi ke format <strong>WebP</strong> untuk akses cepat.</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>
.form-page-container {
    padding: 24px 32px 48px;
    max-width: 1360px;
    margin: 0 auto;
}

/* ── Top Header Bar ── */
.form-header-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}

.breadcrumb-trail {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
}

.trail-link {
    display: inline-flex;
    align-items: center;
    color: #64748b;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.15s ease;
}

.trail-link:hover {
    color: #006837;
}

.trail-separator {
    color: #cbd5e1;
}

.trail-current {
    font-weight: 600;
    color: #1e293b;
}

.header-main-title {
    font-size: 23px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.02em;
    margin: 0;
    line-height: 1.25;
}

.status-indicator-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 9999px;
    letter-spacing: 0.2px;
}

.status-indicator--published {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}

.status-indicator--draft {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

.pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: currentColor;
}

/* Header Actions */
.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-secondary-action {
    display: inline-flex;
    align-items: center;
    background: #ffffff;
    color: #475569;
    border: 1px solid #cbd5e1;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 20px;
    border-radius: 9999px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}

.btn-secondary-action:hover:not(:disabled) {
    background: #f8fafc;
    color: #0f172a;
    border-color: #94a3b8;
    transform: translateY(-1px);
}

.btn-primary-action {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(135deg, #006837 0%, #008744 100%);
    color: #ffffff;
    border: none;
    font-size: 13.5px;
    font-weight: 600;
    padding: 10px 22px;
    border-radius: 9999px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(0, 104, 55, 0.3);
    transition: all 0.2s cubic-bezier(0.2, 0, 0, 1);
}

.btn-primary-action:hover:not(:disabled) {
    background: linear-gradient(135deg, #00502a 0%, #006837 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 104, 55, 0.4);
}

.btn-primary-action:disabled,
.btn-secondary-action:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* ── Form Grid Layout ── */
.form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    align-items: start;
}

@media (min-width: 1024px) {
    .form-grid {
        grid-template-columns: 1fr 380px;
    }
}

/* ── Content Card (Left) ── */
.content-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 28px 32px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
}

.input-block {
    margin-bottom: 24px;
}

.label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 3px;
}

.input-label-primary {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.required-asterisk {
    color: #e11d48;
}

.input-counter {
    font-size: 11px;
    font-weight: 500;
    color: #94a3b8;
}

.input-sublabel {
    font-size: 12px;
    color: #64748b;
    margin: 0 0 10px 0;
    line-height: 1.4;
}

/* Text Inputs */
.text-input {
    width: 100%;
    background: #fbfcfd;
    border: 1px solid #dcdfe4;
    border-radius: 11px;
    padding: 11px 16px;
    color: #0f172a;
    font-size: 14px;
    font-family: inherit;
    outline: none;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02) inset;
}

.text-input:focus {
    background: #ffffff;
    border-color: #006837;
    box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.12);
}

.text-input--title {
    font-size: 17px;
    font-weight: 600;
    padding: 13px 16px;
    letter-spacing: -0.015em;
}

.text-input--title::placeholder {
    font-size: 15px;
    font-weight: 400;
    color: #94a3b8;
}

.text-textarea--excerpt {
    font-size: 13.5px;
    line-height: 1.6;
    resize: vertical;
    min-height: 86px;
}

.text-textarea--content {
    font-size: 14.5px;
    line-height: 1.75;
    resize: vertical;
    min-height: 380px;
    padding: 16px 18px;
    border-radius: 12px;
}

/* Editor Guide Bar */
.editor-guide-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 7px 12px;
    margin-bottom: 8px;
    font-size: 11.5px;
    color: #475569;
}

.guide-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.stats-badge-group {
    display: flex;
    align-items: center;
    gap: 6px;
}

.stat-pill {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
}

.field-error-message {
    font-size: 11.5px;
    font-weight: 500;
    color: #e11d48;
    margin-top: 6px;
    margin-bottom: 0;
}

/* ── Sidebar Cards ── */
.sidebar-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 22px 24px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
    margin-bottom: 20px;
}

.sidebar-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 18px;
}

.header-icon-circle {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #ecfdf5;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sidebar-card-title {
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    line-height: 1.2;
}

.sidebar-card-subtitle {
    font-size: 11.5px;
    color: #64748b;
    margin: 2px 0 0 0;
}

.settings-group {
    margin-bottom: 18px;
}

.sidebar-field-label {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 7px;
}

/* Segmented Control */
.segmented-control {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    background: #f1f5f9;
    padding: 4px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.segmented-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 8px 10px;
    border-radius: 9px;
    border: none;
    background: transparent;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.segmented-btn--active-green {
    background: #ffffff;
    color: #006837;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}

.segmented-btn--active-slate {
    background: #ffffff;
    color: #1e293b;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 9999px;
}

.dot-green {
    background: #10b981;
}

.dot-slate {
    background: #94a3b8;
}

/* Custom Dropdown for Categories */
.custom-dropdown-container {
    position: relative;
    width: 100%;
}

.custom-dropdown-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fbfcfd;
    border: 1px solid #dcdfe4;
    border-radius: 10px;
    padding: 10px 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: left;
}

.custom-dropdown-trigger:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.custom-dropdown-trigger--open {
    background: #ffffff;
    border-color: #006837;
    box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.1);
}

.dropdown-chevron {
    transition: transform 0.2s ease;
}

.dropdown-chevron--flipped {
    transform: rotate(180deg);
}

.category-color-dot {
    width: 9px;
    height: 9px;
    border-radius: 9999px;
    flex-shrink: 0;
}

.custom-dropdown-menu {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12);
    z-index: 50;
    overflow: hidden;
    animation: fadeIn 0.15s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-menu-header {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #94a3b8;
    padding: 8px 14px;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
}

.dropdown-menu-list {
    max-height: 220px;
    overflow-y: auto;
    padding: 4px;
}

.dropdown-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 9px 12px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    color: #334155;
    transition: background 0.15s ease;
}

.dropdown-item:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.dropdown-item--selected {
    background: #ecfdf5 !important;
    color: #006837 !important;
    font-weight: 600;
}

/* Headline Box */
.headline-box {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 12px 14px;
    margin-bottom: 20px;
}

.headline-label-toggle {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    cursor: pointer;
}

.headline-checkbox {
    width: 17px;
    height: 17px;
    accent-color: #006837;
    margin-top: 2px;
    cursor: pointer;
}

.headline-text-content {
    display: flex;
    flex-direction: column;
}

.headline-subtext {
    font-size: 11px;
    color: #92400e;
    margin: 2px 0 0 0;
    line-height: 1.35;
}

/* Sidebar Submit Button */
.sidebar-action-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 16px 0;
}

.btn-sidebar-submit {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #006837 0%, #008744 100%);
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 600;
    padding: 12px 20px;
    border-radius: 9999px;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(0, 104, 55, 0.3);
    transition: all 0.2s cubic-bezier(0.2, 0, 0, 1);
}

.btn-sidebar-submit:hover:not(:disabled) {
    background: linear-gradient(135deg, #00502a 0%, #006837 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 104, 55, 0.4);
}

.btn-sidebar-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Cover Image Upload */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}

.cover-preview-card {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
}

.preview-img-container {
    aspect-ratio: 16 / 9;
    width: 100%;
    background: #0f172a;
    overflow: hidden;
}

.preview-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-actions-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
}

.preview-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    padding: 6px 12px;
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px solid transparent;
}

.preview-btn--change {
    flex: 1;
    background: #f1f5f9;
    color: #334155;
    border-color: #e2e8f0;
}

.preview-btn--change:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.preview-btn--remove {
    width: 32px;
    height: 32px;
    padding: 0;
    background: #fff1f2;
    color: #e11d48;
    border-color: #ffe4e6;
}

.preview-btn--remove:hover {
    background: #e11d48;
    color: #ffffff;
}

/* Upload Dropzone */
.upload-dropzone {
    border: 2px dashed #cbd5e1;
    border-radius: 14px;
    padding: 24px 16px;
    text-align: center;
    background: #fafbfe;
    cursor: pointer;
    transition: all 0.2s ease;
}

.upload-dropzone:hover {
    border-color: #006837;
    background: #f0fdf4;
}

.upload-icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #ecfdf5;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
}

.dropzone-main-text {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 2px;
}

.dropzone-sub-text {
    display: block;
    font-size: 11.5px;
    color: #64748b;
    margin-bottom: 10px;
}

.dropzone-formats {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.format-chip {
    font-size: 10px;
    font-weight: 700;
    background: #e2e8f0;
    color: #475569;
    padding: 2px 7px;
    border-radius: 6px;
    letter-spacing: 0.3px;
}

.compression-notice {
    display: flex;
    align-items: flex-start;
    font-size: 11px;
    color: #64748b;
    line-height: 1.4;
}
</style>
