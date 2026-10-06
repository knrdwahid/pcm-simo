<script setup>
import showAlert from '@/Utils/sweetalert';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    items: { type: Array, default: () => [] },
    types: { type: Object, default: () => ({}) },
    counts: { type: Object, default: () => ({}) },
    categorySuggestions: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ jenis: 'semua', cari: '' }) },
});

// ── Material 3 Type Metadata (Color System & Tokens) ──
const typeMeta = {
    organisasi: {
        short: 'Majelis & Lembaga',
        icon: 'mdi-sitemap-outline',
        color: '#1d4ed8',
        container: '#eff6ff',
        onContainer: '#1e40af',
        border: '#bfdbfe',
        categories: ['Majelis', 'Lembaga'],
        hint: 'Unsur pembantu pimpinan seperti Majelis Tabligh, Dikdasmen, Lazismu, MDMC.',
    },
    ortom: {
        short: 'Ortom',
        icon: 'mdi-account-group-outline',
        color: '#7c3aed',
        container: '#f5f3ff',
        onContainer: '#6d28d9',
        border: '#ddd6fe',
        categories: ['Ortom'],
        hint: "Organisasi otonom: 'Aisyiyah, Pemuda Muhammadiyah, NA, IPM, HW, Tapak Suci.",
    },
    amal_usaha: {
        short: 'Amal Usaha',
        icon: 'mdi-office-building-outline',
        color: '#d97706',
        container: '#fffbeb',
        onContainer: '#b45309',
        border: '#fde68a',
        categories: ['Pendidikan', 'Kesehatan', 'Sosial & Filantropi', 'Ekonomi'],
        hint: 'Sekolah, klinik/PKU, panti asuhan, BMT, dan unit usaha persyarikatan.',
    },
    masjid: {
        short: 'Masjid & Musholla',
        icon: 'mdi-mosque',
        color: '#059669',
        container: '#ecfdf5',
        onContainer: '#047857',
        border: '#a7f3d0',
        categories: ['Masjid', 'Musholla', 'Masjid & Dakwah'],
        hint: 'Masjid dan musholla milik/binaan persyarikatan di wilayah Simo.',
    },
};

// ── Icon Library categorized by field ──
const iconCategories = [
    {
        name: 'Semua',
        icons: [
            'mdi-mosque', 'mdi-school', 'mdi-hospital-building', 'mdi-hand-heart-outline',
            'mdi-storefront-outline', 'mdi-book-open-page-variant-outline', 'mdi-sitemap-outline',
            'mdi-account-group-outline', 'mdi-shield-account-outline', 'mdi-flower-outline',
            'mdi-account-school-outline', 'mdi-compass-outline', 'mdi-sword-cross',
            'mdi-chart-line', 'mdi-shield-alert-outline', 'mdi-office-building-outline',
        ],
    },
    {
        name: 'Masjid & Dakwah',
        icons: ['mdi-mosque', 'mdi-book-open-page-variant-outline', 'mdi-hands-pray', 'mdi-bullhorn-outline', 'mdi-volume-high'],
    },
    {
        name: 'Pendidikan',
        icons: ['mdi-school', 'mdi-school-outline', 'mdi-account-school-outline', 'mdi-library-outline', 'mdi-certificate-outline'],
    },
    {
        name: 'Kesehatan',
        icons: ['mdi-hospital-building', 'mdi-hospital-box-outline', 'mdi-medical-bag', 'mdi-heart-pulse', 'mdi-ambulance'],
    },
    {
        name: 'Sosial & Ortom',
        icons: ['mdi-hand-heart-outline', 'mdi-account-group-outline', 'mdi-shield-account-outline', 'mdi-flower-outline', 'mdi-compass-outline', 'mdi-sword-cross'],
    },
];

const selectedIconCategory = ref('Semua');
const visibleIcons = computed(() => {
    const found = iconCategories.find(c => c.name === selectedIconCategory.value);
    return found ? found.icons : iconCategories[0].icons;
});

const totalAll = computed(() => Object.values(props.counts).reduce((a, b) => a + Number(b), 0));

const tabs = computed(() => [
    { key: 'semua', label: 'Semua', icon: 'mdi-view-grid-outline', count: totalAll.value },
    ...Object.keys(props.types).map(key => ({
        key,
        label: typeMeta[key]?.short ?? props.types[key],
        icon: typeMeta[key]?.icon,
        count: Number(props.counts[key] ?? 0),
    })),
]);

// ── Filters & Search ──
const activeType = ref(props.filters.jenis || 'semua');
const search = ref(props.filters.cari || '');

const applyFilters = () => {
    router.get(
        '/dashboard/aum',
        {
            jenis: activeType.value !== 'semua' ? activeType.value : undefined,
            cari: search.value || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const selectType = (key) => {
    activeType.value = key;
    applyFilters();
};

let searchTimer = null;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 320);
});
onUnmounted(() => clearTimeout(searchTimer));

// ── Form & Dialog State ──
const dialog = ref(false);
const editing = ref(null);
const imagePreview = ref(null);
const fileInput = ref(null);
const activeDialogTab = ref('info'); // 'info' | 'contact' | 'media'

const form = useForm({
    type: 'amal_usaha',
    name: '',
    category: '',
    icon: '',
    leader: '',
    phone: '',
    address: '',
    website: '',
    description: '',
    sort_order: null,
    is_active: true,
    image: null,
    image_url: '',
    remove_image: false,
});

const categoryOptions = computed(() => {
    const base = typeMeta[form.type]?.categories ?? [];
    return [...new Set([...base, ...props.categorySuggestions])];
});

// ── Custom Combobox & Dropdown for Kategori / Bidang ──
const categoryComboboxRef = ref(null);
const isCategoryDropdownOpen = ref(false);
const highlightedIndex = ref(-1);

const filteredCategories = computed(() => {
    const q = (form.category || '').trim().toLowerCase();
    if (!q) return categoryOptions.value;
    return categoryOptions.value.filter(cat => cat.toLowerCase().includes(q));
});

const isCustomNewCategory = computed(() => {
    const q = (form.category || '').trim();
    if (!q) return false;
    return !categoryOptions.value.some(cat => cat.toLowerCase() === q.toLowerCase());
});

const quickSuggestionPills = computed(() => {
    const primary = typeMeta[form.type]?.categories ?? [];
    return [...new Set([...primary, ...categoryOptions.value])].slice(0, 5);
});

const toggleCategoryDropdown = () => {
    isCategoryDropdownOpen.value = !isCategoryDropdownOpen.value;
    highlightedIndex.value = -1;
};

const openCategoryDropdown = () => {
    isCategoryDropdownOpen.value = true;
};

const closeCategoryDropdown = () => {
    isCategoryDropdownOpen.value = false;
    highlightedIndex.value = -1;
};

const selectCategory = (cat) => {
    form.category = cat;
    closeCategoryDropdown();
};

const clearCategory = () => {
    form.category = '';
    isCategoryDropdownOpen.value = true;
    highlightedIndex.value = -1;
};

const onCategoryInput = () => {
    isCategoryDropdownOpen.value = true;
    highlightedIndex.value = -1;
};

const highlightNextOption = () => {
    if (!isCategoryDropdownOpen.value) {
        isCategoryDropdownOpen.value = true;
        highlightedIndex.value = 0;
        return;
    }
    const max = filteredCategories.value.length - 1;
    if (highlightedIndex.value < max) {
        highlightedIndex.value++;
    } else {
        highlightedIndex.value = 0;
    }
};

const highlightPrevOption = () => {
    if (!isCategoryDropdownOpen.value) {
        isCategoryDropdownOpen.value = true;
        highlightedIndex.value = Math.max(0, filteredCategories.value.length - 1);
        return;
    }
    if (highlightedIndex.value > 0) {
        highlightedIndex.value--;
    } else {
        highlightedIndex.value = Math.max(0, filteredCategories.value.length - 1);
    }
};

const selectHighlightedOrCurrent = () => {
    if (isCategoryDropdownOpen.value && highlightedIndex.value >= 0 && filteredCategories.value[highlightedIndex.value]) {
        selectCategory(filteredCategories.value[highlightedIndex.value]);
    } else if (isCategoryDropdownOpen.value && isCustomNewCategory.value) {
        selectCategory(form.category.trim());
    } else {
        closeCategoryDropdown();
    }
};

const handleClickOutsideCategory = (e) => {
    if (categoryComboboxRef.value && !categoryComboboxRef.value.contains(e.target)) {
        closeCategoryDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutsideCategory);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutsideCategory);
});

watch(activeDialogTab, () => {
    closeCategoryDropdown();
});

watch(dialog, (val) => {
    if (!val) closeCategoryDropdown();
});

const openCreate = () => {
    closeCategoryDropdown();
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.type = activeType.value !== 'semua' ? activeType.value : 'amal_usaha';
    form.category = typeMeta[form.type]?.categories[0] ?? '';
    form.icon = typeMeta[form.type]?.icon ?? '';
    imagePreview.value = null;
    activeDialogTab.value = 'info';
    dialog.value = true;
};

const openEdit = (item) => {
    closeCategoryDropdown();
    editing.value = item;
    form.clearErrors();
    form.type = item.type || 'amal_usaha';
    form.name = item.name ?? '';
    form.category = item.category ?? '';
    form.icon = item.icon ?? '';
    form.leader = item.leader ?? '';
    form.phone = item.phone ?? '';
    form.address = item.address ?? '';
    form.website = item.website ?? '';
    form.description = item.description ?? '';
    form.sort_order = item.sort_order ?? null;
    form.is_active = !!item.is_active;
    form.image = null;
    form.image_url = '';
    form.remove_image = false;
    imagePreview.value = item.image_url || null;
    activeDialogTab.value = 'info';
    dialog.value = true;
};

// Auto-suggest category & icon when changing type in create mode
watch(() => form.type, (newType) => {
    if (!editing.value) {
        if (!form.category || Object.values(typeMeta).some(m => m.categories.includes(form.category))) {
            form.category = typeMeta[newType]?.categories[0] ?? '';
        }
        if (!form.icon || Object.values(typeMeta).some(m => m.icon === form.icon)) {
            form.icon = typeMeta[newType]?.icon ?? '';
        }
    }
});

const onFileChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    form.image = file;
    form.remove_image = false;
    form.image_url = '';
    imagePreview.value = URL.createObjectURL(file);
};

const removeImage = () => {
    form.image = null;
    form.image_url = '';
    form.remove_image = !!editing.value?.image_url;
    imagePreview.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

watch(() => form.image_url, (url) => {
    if (url && /^https?:\/\//.test(url)) {
        form.image = null;
        form.remove_image = false;
        imagePreview.value = url;
    }
});

const submit = () => {
    const url = editing.value ? `/dashboard/aum/${editing.value.id}` : '/dashboard/aum';
    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            closeCategoryDropdown();
            dialog.value = false;
            form.reset();
        },
    });
};

const toggleActive = (item) => {
    router.patch(`/dashboard/aum/${item.id}/toggle-active`, {}, { preserveScroll: true });
};

const destroy = async (item) => {
    const confirmed = await showAlert.confirm(
        'Hapus Data?',
        `Apakah Anda yakin ingin menghapus "${item.name}"? Data akan hilang dari halaman publik.`,
        'Ya, Hapus Data',
    );
    if (confirmed) {
        router.delete(`/dashboard/aum/${item.id}`, { preserveScroll: true });
    }
};

const metaOf = (item) => typeMeta[item.type] ?? typeMeta.amal_usaha;
const iconOf = (item) => item.icon || metaOf(item).icon;
</script>

<template>
    <Head title="AUM & Organisasi - Admin PCM Simo" />

    <div class="m3-page-container">
        <!-- ── Top Page Bar (Material 3 Header) ── -->
        <header class="m3-header">
            <div class="m3-header-main">
                <div class="m3-header-titles">
                    <div class="d-flex align-center ga-2 mb-1 flex-wrap">
                        <span class="m3-header-badge">
                            <v-icon size="13" class="mr-2 text-emerald-700">mdi-shield-check</v-icon>
                            Modul Pengelolaan
                        </span>
                        <span class="m3-header-badge m3-header-badge--count">
                            {{ totalAll }} Terdaftar
                        </span>
                    </div>
                    <h1 class="m3-headline">AUM &amp; Organisasi</h1>
                    <p class="m3-subhead">
                        Kelola data Majelis &amp; Lembaga, Organisasi Otonom, Amal Usaha, dan Masjid yang terintegrasi di publik.
                    </p>
                </div>

                <div class="m3-header-actions">
                    <button id="btn-aum-create" type="button" class="m3-fab-extended" @click="openCreate">
                        <v-icon size="20" class="mr-2">mdi-plus</v-icon>
                        <span>Tambah Data</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- ── Material 3 Metric / Summary Cards ── -->
        <section class="m3-metrics-grid" aria-label="Ringkasan Kategori">
            <button
                v-for="(meta, key) in typeMeta"
                :id="`metric-card-${key}`"
                :key="key"
                type="button"
                class="m3-metric-card"
                :class="{ 'm3-metric-card--active': activeType === key }"
                :style="{ '--m3-accent': meta.color, '--m3-container': meta.container }"
                @click="selectType(key)"
            >
                <div class="m3-metric-top">
                    <div class="m3-metric-icon">
                        <v-icon size="24" :color="meta.color">{{ meta.icon }}</v-icon>
                    </div>
                    <span v-if="activeType === key" class="m3-metric-active-pill">
                        <span class="m3-pulse-dot"></span>
                        Aktif
                    </span>
                </div>

                <div class="m3-metric-body">
                    <div class="m3-metric-value">{{ counts[key] ?? 0 }}</div>
                    <div class="m3-metric-label">{{ props.types[key] }}</div>
                </div>

                <div class="m3-metric-footer">
                    <span>Lihat daftar {{ meta.short }}</span>
                    <v-icon size="14" class="m3-metric-arrow">mdi-arrow-right</v-icon>
                </div>
            </button>
        </section>

        <!-- ── Material 3 Segmented Toolbar (Tabs + Search) ── -->
        <section class="m3-toolbar">
            <nav class="m3-segmented-control" role="tablist" aria-label="Filter Jenis">
                <button
                    v-for="tab in tabs"
                    :id="`tab-${tab.key}`"
                    :key="tab.key"
                    type="button"
                    role="tab"
                    class="m3-segmented-btn"
                    :class="{ 'm3-segmented-btn--selected': activeType === tab.key }"
                    :aria-selected="activeType === tab.key"
                    @click="selectType(tab.key)"
                >
                    <v-icon size="16" class="mr-2">{{ tab.icon }}</v-icon>
                    <span class="m3-segmented-text">{{ tab.label }}</span>
                    <span class="m3-segmented-badge">{{ tab.count }}</span>
                </button>
            </nav>

            <div class="m3-search-bar">
                <v-icon size="18" class="m3-search-icon">mdi-magnify</v-icon>
                <input
                    id="aum-search"
                    v-model="search"
                    type="search"
                    placeholder="Cari nama, kategori, pimpinan, alamat..."
                    class="m3-search-input"
                    aria-label="Cari data AUM"
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
        </section>

        <!-- ── Material 3 Entity Cards Grid ── -->
        <section v-if="items.length" class="m3-cards-grid">
            <article
                v-for="item in items"
                :key="item.id"
                class="m3-card"
                :class="{ 'm3-card--hidden': !item.is_active }"
                :style="{ '--card-accent': metaOf(item).color, '--card-container': metaOf(item).container }"
            >
                <!-- Visual Banner -->
                <div class="m3-card-media">
                    <img
                        v-if="item.image_url"
                        :src="item.image_url"
                        :alt="item.name"
                        loading="lazy"
                        class="m3-card-img"
                    />
                    <div v-else class="m3-card-placeholder">
                        <div class="m3-card-placeholder-pattern"></div>
                        <v-icon size="44" :color="metaOf(item).color">{{ iconOf(item) }}</v-icon>
                    </div>

                    <!-- Floating Chips -->
                    <div class="m3-media-chips">
                        <span class="m3-chip m3-chip--type">
                            <v-icon size="12" class="mr-1.5">{{ metaOf(item).icon }}</v-icon>
                            {{ metaOf(item).short }}
                        </span>
                        <span v-if="!item.is_active" class="m3-chip m3-chip--muted">
                            <v-icon size="12" class="mr-1.5">mdi-eye-off-outline</v-icon>
                            Tersembunyi
                        </span>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="m3-card-content">
                    <div class="m3-card-header">
                        <div class="m3-card-avatar">
                            <v-icon size="20" :color="metaOf(item).color">{{ iconOf(item) }}</v-icon>
                        </div>
                        <div class="m3-card-titles">
                            <h3 class="m3-card-name" :title="item.name">{{ item.name }}</h3>
                            <span v-if="item.category" class="m3-card-category">{{ item.category }}</span>
                        </div>
                    </div>

                    <p v-if="item.description" class="m3-card-desc" :title="item.description">
                        {{ item.description }}
                    </p>

                    <!-- Info list -->
                    <div class="m3-meta-list">
                        <div v-if="item.leader" class="m3-meta-row" title="Pimpinan / Kepala">
                            <v-icon size="15" class="m3-meta-icon">mdi-account-tie-outline</v-icon>
                            <span class="m3-meta-text">{{ item.leader }}</span>
                        </div>
                        <div v-if="item.address" class="m3-meta-row" title="Alamat">
                            <v-icon size="15" class="m3-meta-icon">mdi-map-marker-outline</v-icon>
                            <span class="m3-meta-text">{{ item.address }}</span>
                        </div>
                        <div v-if="item.phone" class="m3-meta-row" title="Telepon">
                            <v-icon size="15" class="m3-meta-icon">mdi-phone-outline</v-icon>
                            <span class="m3-meta-text">{{ item.phone }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer with MD3 Switch and Actions -->
                <div class="m3-card-footer">
                    <label class="m3-switch" :title="item.is_active ? 'Klik untuk sembunyikan' : 'Klik untuk tampilkan'">
                        <input
                            type="checkbox"
                            :checked="item.is_active"
                            @change="toggleActive(item)"
                        />
                        <span class="m3-switch-track">
                            <span class="m3-switch-thumb">
                                <v-icon size="10" :color="item.is_active ? '#006837' : '#94a3b8'">
                                    {{ item.is_active ? 'mdi-check' : 'mdi-close' }}
                                </v-icon>
                            </span>
                        </span>
                        <span class="m3-switch-text">{{ item.is_active ? 'Tampil' : 'Off' }}</span>
                    </label>

                    <div class="m3-actions-cluster">
                        <span class="m3-order-pill" title="Urutan tampil">#{{ item.sort_order }}</span>
                        <button
                            type="button"
                            class="m3-icon-btn m3-icon-btn--edit"
                            title="Sunting Data"
                            @click="openEdit(item)"
                        >
                            <v-icon size="16">mdi-pencil-outline</v-icon>
                        </button>
                        <button
                            type="button"
                            class="m3-icon-btn m3-icon-btn--delete"
                            title="Hapus Data"
                            @click="destroy(item)"
                        >
                            <v-icon size="16">mdi-trash-can-outline</v-icon>
                        </button>
                    </div>
                </div>
            </article>
        </section>

        <!-- ── Material 3 Empty State ── -->
        <div v-else class="m3-empty-state">
            <div class="m3-empty-art">
                <v-icon size="44" color="#006837">mdi-domain-plus</v-icon>
            </div>
            <h2 class="m3-empty-headline">Tidak ada data ditemukan</h2>
            <p class="m3-empty-support">
                {{ search ? `Tidak ada entitas yang sesuai dengan kata kunci "${search}".` : 'Mulai tambahkan Majelis, Ortom, Amal Usaha, atau Masjid pertama Anda.' }}
            </p>
            <button type="button" class="m3-btn-filled" @click="openCreate">
                <v-icon size="18" class="mr-1.5">mdi-plus</v-icon>
                <span>Tambah Data Sekarang</span>
            </button>
        </div>

        <!-- ── Material Design 3 Modal Dialog ── -->
        <v-dialog v-model="dialog" max-width="840" scrollable transition="dialog-bottom-transition">
            <div class="m3-dialog-surface">
                <!-- Dialog Hero Header -->
                <div class="m3-dialog-header">
                    <div class="m3-dialog-lead">
                        <div
                            class="m3-dialog-icon-badge"
                            :style="{ background: typeMeta[form.type]?.container, color: typeMeta[form.type]?.color }"
                        >
                            <v-icon size="24" :color="typeMeta[form.type]?.color">
                                {{ editing ? 'mdi-pencil-outline' : typeMeta[form.type]?.icon }}
                            </v-icon>
                        </div>
                        <div>
                            <div class="m3-dialog-overline">{{ editing ? 'Mode Perbarui Data' : 'Tambah Entitas Baru' }}</div>
                            <h2 class="m3-dialog-headline">
                                {{ editing ? form.name || 'Sunting Entitas' : `Tambah ${typeMeta[form.type]?.short || 'Data Baru'}` }}
                            </h2>
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

                <!-- Dialog Segmented Stepper / Tabs -->
                <div class="m3-dialog-tabs">
                    <button
                        type="button"
                        class="m3-dialog-tab-btn"
                        :class="{ 'm3-dialog-tab-btn--active': activeDialogTab === 'info' }"
                        @click="activeDialogTab = 'info'"
                    >
                        <v-icon size="16" class="mr-2">mdi-card-text-outline</v-icon>
                        <span>1. Data Utama</span>
                    </button>
                    <button
                        type="button"
                        class="m3-dialog-tab-btn"
                        :class="{ 'm3-dialog-tab-btn--active': activeDialogTab === 'contact' }"
                        @click="activeDialogTab = 'contact'"
                    >
                        <v-icon size="16" class="mr-2">mdi-map-marker-radius-outline</v-icon>
                        <span>2. Kontak &amp; Lokasi</span>
                    </button>
                    <button
                        type="button"
                        class="m3-dialog-tab-btn"
                        :class="{ 'm3-dialog-tab-btn--active': activeDialogTab === 'media' }"
                        @click="activeDialogTab = 'media'"
                    >
                        <v-icon size="16" class="mr-2">mdi-image-filter-vintage</v-icon>
                        <span>3. Ikon, Foto &amp; Status</span>
                    </button>
                </div>

                <!-- Dialog Scrollable Body -->
                <div class="m3-dialog-body">
                    <form id="aum-m3-form" @submit.prevent="submit">
                        <!-- ── TAB 1: DATA UTAMA ── -->
                        <div v-show="activeDialogTab === 'info'" class="m3-form-section">
                            <div class="m3-section-title-wrap">
                                <span class="m3-section-badge">Langkah 1</span>
                                <h3 class="m3-section-heading">Pilih Jenis Entitas &amp; Nama Resmi</h3>
                                <p class="m3-section-sub">Tentukan klasifikasi entitas persyarikatan dan identitas intinya.</p>
                            </div>

                            <!-- M3 Interactive Type Cards -->
                            <div class="m3-type-cards-grid">
                                <button
                                    v-for="(meta, key) in typeMeta"
                                    :id="`type-select-${key}`"
                                    :key="key"
                                    type="button"
                                    class="m3-type-select-card"
                                    :class="{ 'm3-type-select-card--selected': form.type === key }"
                                    :style="{ '--t-color': meta.color, '--t-container': meta.container }"
                                    @click="form.type = key"
                                >
                                    <div class="m3-type-select-icon">
                                        <v-icon size="22" :color="meta.color">{{ meta.icon }}</v-icon>
                                    </div>
                                    <div class="m3-type-select-text">
                                        <div class="m3-type-select-name">{{ meta.short }}</div>
                                        <div class="m3-type-select-hint">{{ key === 'masjid' ? 'Masjid / Musholla' : key }}</div>
                                    </div>
                                    <div v-if="form.type === key" class="m3-type-select-check">
                                        <v-icon size="14" color="#ffffff">mdi-check</v-icon>
                                    </div>
                                </button>
                            </div>

                            <div class="m3-banner-hint" :style="{ background: typeMeta[form.type]?.container, color: typeMeta[form.type]?.onContainer }">
                                <v-icon size="16" :color="typeMeta[form.type]?.color" class="mr-2">mdi-information-outline</v-icon>
                                <span>{{ typeMeta[form.type]?.hint }}</span>
                            </div>

                            <div class="m3-fields-grid mt-4">
                                <div class="m3-col-12">
                                    <label class="m3-field-label">
                                        Nama Resmi <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="m3-input-box" :class="{ 'm3-input-box--error': form.errors.name }">
                                        <v-icon size="18" class="m3-input-icon">mdi-domain</v-icon>
                                        <input
                                            id="aum-name"
                                            v-model="form.name"
                                            type="text"
                                            class="m3-native-input"
                                            placeholder="Contoh: SD Muhammadiyah Program Khusus Simo"
                                            required
                                        />
                                    </div>
                                    <p v-if="form.errors.name" class="m3-error-text">{{ form.errors.name }}</p>
                                </div>

                                <div class="m3-col-6">
                                    <label class="m3-field-label" for="aum-category">
                                        Kategori / Bidang
                                    </label>
                                    <div ref="categoryComboboxRef" class="m3-combobox-wrapper">
                                        <div
                                            class="m3-input-box m3-combobox-box"
                                            :class="{
                                                'm3-input-box--error': form.errors.category,
                                                'm3-input-box--focused': isCategoryDropdownOpen,
                                            }"
                                            @click="openCategoryDropdown"
                                        >
                                            <v-icon size="18" class="m3-input-icon" :color="isCategoryDropdownOpen ? '#006837' : '#94a3b8'">mdi-tag-outline</v-icon>
                                            <input
                                                id="aum-category"
                                                v-model="form.category"
                                                type="text"
                                                autocomplete="off"
                                                class="m3-native-input"
                                                placeholder="Pilih atau ketik kategori..."
                                                @focus="openCategoryDropdown"
                                                @input="onCategoryInput"
                                                @keydown.down.prevent="highlightNextOption"
                                                @keydown.up.prevent="highlightPrevOption"
                                                @keydown.enter.prevent="selectHighlightedOrCurrent"
                                                @keydown.esc="closeCategoryDropdown"
                                            />
                                            <button
                                                v-if="form.category"
                                                type="button"
                                                class="m3-combobox-btn m3-combobox-btn--clear"
                                                title="Hapus Kategori"
                                                @click.stop="clearCategory"
                                            >
                                                <v-icon size="16">mdi-close-circle</v-icon>
                                            </button>
                                            <button
                                                type="button"
                                                class="m3-combobox-btn m3-combobox-btn--toggle"
                                                :class="{ 'm3-combobox-btn--open': isCategoryDropdownOpen }"
                                                title="Pilihan Kategori"
                                                @click.stop="toggleCategoryDropdown"
                                            >
                                                <v-icon size="18">mdi-chevron-down</v-icon>
                                            </button>
                                        </div>

                                        <!-- Custom Material 3 Floating Dropdown Menu -->
                                        <Transition name="m3-dropdown-fade">
                                            <div v-if="isCategoryDropdownOpen" class="m3-combobox-menu">
                                                <div class="m3-combobox-header">
                                                    <span class="m3-combobox-header-title">Pilih Kategori</span>
                                                    <span class="m3-combobox-badge">{{ typeMeta[form.type]?.short || 'Umum' }}</span>
                                                </div>

                                                <div class="m3-combobox-list" role="listbox">
                                                    <!-- Opsi Kategori Baru Jika Mengetik Kustom -->
                                                    <button
                                                        v-if="isCustomNewCategory"
                                                        type="button"
                                                        class="m3-combobox-item m3-combobox-item--custom"
                                                        @click.stop="selectCategory(form.category.trim())"
                                                    >
                                                        <div class="m3-combobox-item-icon m3-combobox-item-icon--custom">
                                                            <v-icon size="15" color="#006837">mdi-plus-circle-outline</v-icon>
                                                        </div>
                                                        <div class="m3-combobox-item-text">
                                                            <span class="m3-combobox-item-label font-semibold text-slate-800">Gunakan Kategori Baru:</span>
                                                            <span class="m3-combobox-item-sub">"{{ form.category.trim() }}"</span>
                                                        </div>
                                                    </button>

                                                    <!-- Daftar Pilihan Kategori -->
                                                    <button
                                                        v-for="(cat, idx) in filteredCategories"
                                                        :key="cat"
                                                        type="button"
                                                        class="m3-combobox-item"
                                                        :class="{
                                                            'm3-combobox-item--active': form.category === cat,
                                                            'm3-combobox-item--highlighted': highlightedIndex === idx,
                                                        }"
                                                        @click.stop="selectCategory(cat)"
                                                        @mouseenter="highlightedIndex = idx"
                                                    >
                                                        <div class="m3-combobox-item-icon">
                                                            <v-icon size="15" :color="form.category === cat ? '#006837' : '#64748b'">mdi-tag-outline</v-icon>
                                                        </div>
                                                        <div class="m3-combobox-item-text">
                                                            <span class="m3-combobox-item-label">{{ cat }}</span>
                                                        </div>
                                                        <v-icon
                                                            v-if="form.category === cat"
                                                            size="16"
                                                            color="#006837"
                                                            class="m3-combobox-item-check"
                                                        >
                                                            mdi-check-circle
                                                        </v-icon>
                                                    </button>

                                                    <!-- Status Kosong -->
                                                    <div v-if="filteredCategories.length === 0 && !isCustomNewCategory" class="m3-combobox-empty">
                                                        <v-icon size="18" color="#94a3b8" class="mr-1.5">mdi-tag-off-outline</v-icon>
                                                        <span>Kategori tidak ditemukan</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </Transition>
                                    </div>

                                    <!-- Quick Interactive Recommendation Pills -->
                                    <div class="m3-combobox-chips">
                                        <span class="m3-combobox-chips-label">Rekomendasi:</span>
                                        <div class="m3-combobox-chips-list">
                                            <button
                                                v-for="cat in quickSuggestionPills"
                                                :key="cat"
                                                type="button"
                                                class="m3-chip-pill"
                                                :class="{ 'm3-chip-pill--active': form.category === cat }"
                                                @click="selectCategory(cat)"
                                            >
                                                <v-icon size="12" class="mr-1" :color="form.category === cat ? '#006837' : '#64748b'">
                                                    {{ form.category === cat ? 'mdi-check' : 'mdi-plus' }}
                                                </v-icon>
                                                <span>{{ cat }}</span>
                                            </button>
                                        </div>
                                    </div>
                                    <p v-if="form.errors.category" class="m3-error-text">{{ form.errors.category }}</p>
                                </div>

                                <div class="m3-col-6">
                                    <label class="m3-field-label">
                                        Urutan Tampil (Sort Order)
                                    </label>
                                    <div class="m3-input-box" :class="{ 'm3-input-box--error': form.errors.sort_order }">
                                        <v-icon size="18" class="m3-input-icon">mdi-sort-numeric-ascending</v-icon>
                                        <input
                                            id="aum-sort"
                                            v-model.number="form.sort_order"
                                            type="number"
                                            min="0"
                                            class="m3-native-input"
                                            placeholder="Otomatis urutan berikutnya"
                                        />
                                    </div>
                                    <p class="m3-helper-text">Angka lebih kecil tampil lebih awal</p>
                                </div>

                                <div class="m3-col-12">
                                    <label class="m3-field-label">
                                        Deskripsi Singkat Profil
                                    </label>
                                    <div class="m3-input-box m3-input-box--textarea" :class="{ 'm3-input-box--error': form.errors.description }">
                                        <textarea
                                            id="aum-description"
                                            v-model="form.description"
                                            rows="3"
                                            maxlength="1000"
                                            class="m3-native-textarea"
                                            placeholder="Gambaran umum tugas, visi, layanan, atau program unggulan..."
                                        ></textarea>
                                    </div>
                                    <div class="d-flex justify-space-between align-center mt-1">
                                        <p v-if="form.errors.description" class="m3-error-text mb-0">{{ form.errors.description }}</p>
                                        <span class="m3-counter-text ml-auto">{{ form.description?.length || 0 }} / 1000</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── TAB 2: KONTAK & LOKASI ── -->
                        <div v-show="activeDialogTab === 'contact'" class="m3-form-section">
                            <div class="m3-section-title-wrap">
                                <span class="m3-section-badge">Langkah 2</span>
                                <h3 class="m3-section-heading">Informasi Kontak &amp; Lokasi Fisik</h3>
                                <p class="m3-section-sub">Data ini akan mempermudah warga dan jamaah menghubungi entitas.</p>
                            </div>

                            <div class="m3-fields-grid">
                                <div class="m3-col-6">
                                    <label class="m3-field-label">
                                        Nama Pimpinan / Ketua / Kepala
                                    </label>
                                    <div class="m3-input-box" :class="{ 'm3-input-box--error': form.errors.leader }">
                                        <v-icon size="18" class="m3-input-icon">mdi-account-tie-outline</v-icon>
                                        <input
                                            id="aum-leader"
                                            v-model="form.leader"
                                            type="text"
                                            class="m3-native-input"
                                            placeholder="Nama lengkap pimpinan / penanggung jawab"
                                        />
                                    </div>
                                </div>

                                <div class="m3-col-6">
                                    <label class="m3-field-label">
                                        Nomor Telepon / WhatsApp
                                    </label>
                                    <div class="m3-input-box" :class="{ 'm3-input-box--error': form.errors.phone }">
                                        <v-icon size="18" class="m3-input-icon">mdi-phone-outline</v-icon>
                                        <input
                                            id="aum-phone"
                                            v-model="form.phone"
                                            type="text"
                                            class="m3-native-input"
                                            placeholder="Contoh: (0276) 3294101 / 0812..."
                                        />
                                    </div>
                                </div>

                                <div class="m3-col-12">
                                    <label class="m3-field-label">
                                        Tautan Website / Portal Resmi
                                    </label>
                                    <div class="m3-input-box" :class="{ 'm3-input-box--error': form.errors.website }">
                                        <v-icon size="18" class="m3-input-icon">mdi-web</v-icon>
                                        <input
                                            id="aum-website"
                                            v-model="form.website"
                                            type="url"
                                            class="m3-native-input"
                                            placeholder="https://..."
                                        />
                                    </div>
                                    <p v-if="form.errors.website" class="m3-error-text">{{ form.errors.website }}</p>
                                </div>

                                <div class="m3-col-12">
                                    <label class="m3-field-label">
                                        Alamat Lengkap Kantor / Gedung
                                    </label>
                                    <div class="m3-input-box" :class="{ 'm3-input-box--error': form.errors.address }">
                                        <v-icon size="18" class="m3-input-icon">mdi-map-marker-outline</v-icon>
                                        <input
                                            id="aum-address"
                                            v-model="form.address"
                                            type="text"
                                            class="m3-native-input"
                                            placeholder="Jl. Singoprono No. ..., Desa ..., Kec. Simo, Kab. Boyolali"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── TAB 3: MEDIA, IKON & VISIBILITAS ── -->
                        <div v-show="activeDialogTab === 'media'" class="m3-form-section">
                            <div class="m3-section-title-wrap">
                                <span class="m3-section-badge">Langkah 3</span>
                                <h3 class="m3-section-heading">Personalisasi Ikon, Foto &amp; Status Publikasi</h3>
                                <p class="m3-section-sub">Atur tampilan visual kartu dan visibilitas di website publik.</p>
                            </div>

                            <!-- Icon Picker with Category Filter & Live Preview -->
                            <div class="m3-icon-picker-module">
                                <div class="d-flex align-center justify-space-between mb-2">
                                    <label class="m3-field-label mb-0">Pilih Ikon Representatif</label>
                                    <div class="m3-icon-cat-pills">
                                        <button
                                            v-for="cat in iconCategories"
                                            :key="cat.name"
                                            type="button"
                                            class="m3-icon-cat-btn"
                                            :class="{ 'm3-icon-cat-btn--active': selectedIconCategory === cat.name }"
                                            @click="selectedIconCategory = cat.name"
                                        >
                                            {{ cat.name }}
                                        </button>
                                    </div>
                                </div>

                                <div class="m3-icon-selection-bar">
                                    <div class="m3-icon-active-preview">
                                        <v-icon size="28" :color="typeMeta[form.type]?.color">
                                            {{ form.icon || typeMeta[form.type]?.icon }}
                                        </v-icon>
                                        <span class="m3-icon-active-name">{{ form.icon || typeMeta[form.type]?.icon }}</span>
                                    </div>

                                    <div class="m3-icon-grid">
                                        <button
                                            v-for="ic in visibleIcons"
                                            :key="ic"
                                            type="button"
                                            class="m3-icon-tile"
                                            :class="{ 'm3-icon-tile--active': form.icon === ic }"
                                            :title="ic"
                                            @click="form.icon = form.icon === ic ? '' : ic"
                                        >
                                            <v-icon size="18">{{ ic }}</v-icon>
                                        </button>
                                    </div>
                                </div>

                                <div class="m3-input-box mt-3" :class="{ 'm3-input-box--error': form.errors.icon }">
                                    <v-icon size="18" class="m3-input-icon">mdi-feather</v-icon>
                                    <input
                                        id="aum-icon"
                                        v-model="form.icon"
                                        type="text"
                                        class="m3-native-input"
                                        placeholder="Ketik nama ikon Material Design (contoh: mdi-school)"
                                    />
                                </div>
                            </div>

                            <!-- Modern Image Uploader with Dropzone -->
                            <div class="mt-5">
                                <label class="m3-field-label">Foto / Sampul Gedung (WebP)</label>
                                <div class="m3-upload-card">
                                    <div
                                        class="m3-dropzone"
                                        :class="{ 'm3-dropzone--has-preview': !!imagePreview }"
                                        @click="fileInput?.click()"
                                    >
                                        <img v-if="imagePreview" :src="imagePreview" alt="Pratinjau Foto" class="m3-dropzone-img" />
                                        <div v-else class="m3-dropzone-placeholder">
                                            <div class="m3-dropzone-icon-circle">
                                                <v-icon size="26" color="#006837">mdi-cloud-upload-outline</v-icon>
                                            </div>
                                            <div class="m3-dropzone-title">Klik untuk memilih gambar</div>
                                            <div class="m3-dropzone-subtitle">JPG, PNG, atau WebP (Maks. 5 MB)</div>
                                            <span class="m3-dropzone-badge">Otomatis Dikonversi ke WebP</span>
                                        </div>
                                    </div>

                                    <div class="m3-upload-actions">
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="d-none"
                                            @change="onFileChange"
                                        />

                                        <div class="d-flex ga-2 flex-wrap">
                                            <button type="button" class="m3-btn-tonal" @click="fileInput?.click()">
                                                <v-icon size="16" class="mr-1.5">mdi-file-image-plus-outline</v-icon>
                                                <span>{{ imagePreview ? 'Ganti Berkas' : 'Pilih Berkas' }}</span>
                                            </button>
                                            <button
                                                v-if="imagePreview"
                                                type="button"
                                                class="m3-btn-tonal m3-btn-tonal--danger"
                                                @click="removeImage"
                                            >
                                                <v-icon size="16" class="mr-1.5">mdi-trash-can-outline</v-icon>
                                                <span>Hapus Foto</span>
                                            </button>
                                        </div>

                                        <div class="m3-upload-divider">
                                            <span>atau gunakan tautan URL eksternal</span>
                                        </div>

                                        <div class="m3-input-box" :class="{ 'm3-input-box--error': form.errors.image_url }">
                                            <v-icon size="18" class="m3-input-icon">mdi-link-variant</v-icon>
                                            <input
                                                v-model="form.image_url"
                                                type="url"
                                                class="m3-native-input"
                                                placeholder="https://images.unsplash.com/..."
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Visibility Switch Card -->
                            <div class="m3-visibility-card mt-5">
                                <div class="m3-visibility-info">
                                    <div class="m3-visibility-title">Visibilitas di Halaman Publik</div>
                                    <div class="m3-visibility-desc">
                                        {{ form.is_active ? 'Entitas ini aktif dan langsung tampil pada Profil Organisasi & Beranda publik.' : 'Entitas ini disimpan sebagai draf/arsip dan tidak tampil di publik.' }}
                                    </div>
                                </div>

                                <label class="m3-switch m3-switch--lg">
                                    <input id="aum-active" v-model="form.is_active" type="checkbox" />
                                    <span class="m3-switch-track">
                                        <span class="m3-switch-thumb">
                                            <v-icon size="12" :color="form.is_active ? '#006837' : '#94a3b8'">
                                                {{ form.is_active ? 'mdi-check' : 'mdi-close' }}
                                            </v-icon>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Dialog Sticky Footer -->
                <div class="m3-dialog-footer">
                    <div class="m3-dialog-footer-left">
                        <button
                            v-if="activeDialogTab !== 'info'"
                            type="button"
                            class="m3-btn-text"
                            @click="activeDialogTab = activeDialogTab === 'media' ? 'contact' : 'info'"
                        >
                            <v-icon size="16" class="mr-2">mdi-arrow-left</v-icon>
                            <span>Sebelumnya</span>
                        </button>
                    </div>

                    <div class="m3-dialog-footer-right">
                        <button type="button" class="m3-btn-tonal" @click="dialog = false">
                            Batal
                        </button>

                        <button
                            v-if="activeDialogTab !== 'media'"
                            type="button"
                            class="m3-btn-tonal m3-btn-tonal--primary"
                            @click="activeDialogTab = activeDialogTab === 'info' ? 'contact' : 'media'"
                        >
                            <span>Lanjut</span>
                            <v-icon size="16" class="ml-2">mdi-arrow-right</v-icon>
                        </button>

                        <button
                            id="btn-aum-save"
                            type="submit"
                            form="aum-m3-form"
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
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </v-dialog>
    </div>
</template>

<style scoped>
/* ─────────────────────────────────────────────────────────────
   Material Design 3 (M3) Expressive Tokens & Root Setup
───────────────────────────────────────────────────────────── */
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

/* MD3 Extended FAB */
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

.m3-fab-extended:active {
    transform: translateY(0);
}

/* ── MD3 Metric Cards Grid ── */
.m3-metrics-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

@media (min-width: 1024px) {
    .m3-metrics-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

.m3-metric-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 20px 22px 16px;
    text-align: left;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.m3-metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--m3-accent);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.25s ease;
}

.m3-metric-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    border-color: #cbd5e1;
}

.m3-metric-card:hover::before,
.m3-metric-card--active::before {
    transform: scaleX(1);
}

.m3-metric-card--active {
    border-color: var(--m3-accent);
    background: linear-gradient(180deg, var(--m3-container) 0%, #ffffff 60%);
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
}

.m3-metric-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.m3-metric-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: var(--m3-container);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease;
}

.m3-metric-card:hover .m3-metric-icon {
    transform: scale(1.08);
}

.m3-metric-active-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    color: var(--m3-accent);
    background: #ffffff;
    padding: 3px 9px;
    border-radius: 9999px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}

.m3-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--m3-accent);
    animation: pulseDot 1.5s infinite;
}

@keyframes pulseDot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(1.4); }
}

.m3-metric-value {
    font-size: 28px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
    letter-spacing: -0.5px;
}

.m3-metric-label {
    font-size: 13px;
    color: #475569;
    font-weight: 600;
    margin-top: 4px;
}

.m3-metric-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11.5px;
    color: #94a3b8;
    font-weight: 500;
    margin-top: 14px;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
}

.m3-metric-card:hover .m3-metric-footer {
    color: var(--m3-accent);
}

.m3-metric-arrow {
    transition: transform 0.2s ease;
}

.m3-metric-card:hover .m3-metric-arrow {
    transform: translateX(3px);
}

/* ── MD3 Toolbar ── */
.m3-toolbar {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 24px;
}

@media (min-width: 1024px) {
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
    overflow-x: auto;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.m3-segmented-btn {
    display: inline-flex;
    align-items: center;
    padding: 8px 14px;
    border-radius: 12px;
    border: none;
    background: transparent;
    color: #64748b;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.m3-segmented-btn:hover {
    color: #0f172a;
    background: #f8fafc;
}

.m3-segmented-btn--selected {
    background: #006837 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(0, 104, 55, 0.25);
}

.m3-segmented-badge {
    margin-left: 6px;
    font-size: 10.5px;
    font-weight: 700;
    padding: 1px 7px;
    border-radius: 9999px;
    background: #f1f5f9;
    color: #475569;
}

.m3-segmented-btn--selected .m3-segmented-badge {
    background: rgba(255, 255, 255, 0.22);
    color: #ffffff;
}

.m3-search-bar {
    position: relative;
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 9999px;
    padding: 0 14px 0 38px;
    min-width: 300px;
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
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s ease;
}

.m3-search-clear:hover {
    background: #e2e8f0;
    color: #0f172a;
}

/* ── MD3 Cards Grid ── */
.m3-cards-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 20px;
}

@media (min-width: 768px) {
    .m3-cards-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1200px) {
    .m3-cards-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

.m3-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 22px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.m3-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    border-color: var(--card-accent);
}

.m3-card--hidden {
    opacity: 0.72;
}

.m3-card-media {
    position: relative;
    height: 140px;
    background: var(--card-container);
    overflow: hidden;
}

.m3-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.m3-card:hover .m3-card-img {
    transform: scale(1.06);
}

.m3-card-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    background: linear-gradient(135deg, var(--card-container) 0%, #ffffff 100%);
}

.m3-card-placeholder-pattern {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(var(--card-accent) 0.8px, transparent 0.8px);
    background-size: 12px 12px;
    opacity: 0.15;
}

.m3-media-chips {
    position: absolute;
    top: 10px;
    left: 10px;
    right: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    pointer-events: none;
}

.m3-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 9999px;
    backdrop-filter: blur(8px);
}

.m3-chip--type {
    background: rgba(255, 255, 255, 0.94);
    color: var(--card-accent);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.m3-chip--muted {
    background: rgba(15, 23, 42, 0.75);
    color: #ffffff;
    margin-left: auto;
}

.m3-card-content {
    padding: 18px 20px 14px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.m3-card-header {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 8px;
}

.m3-card-avatar {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: var(--card-container);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.m3-card-titles {
    min-width: 0;
}

.m3-card-name {
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.m3-card-category {
    display: inline-block;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--card-accent);
    margin-top: 2px;
}

.m3-card-desc {
    font-size: 12.5px;
    color: #64748b;
    line-height: 1.55;
    margin: 4px 0 12px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.m3-meta-list {
    margin-top: auto;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.m3-meta-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #475569;
}

.m3-meta-icon {
    color: #94a3b8;
    flex-shrink: 0;
}

.m3-meta-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.m3-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 18px;
    border-top: 1px solid #f1f5f9;
    background: #fafbfd;
}

.m3-actions-cluster {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.m3-order-pill {
    font-size: 11px;
    font-weight: 700;
    color: #94a3b8;
    margin-right: 4px;
    font-family: ui-monospace, SFMono-Regular, monospace;
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

.m3-icon-btn--edit {
    color: #0284c7;
}

.m3-icon-btn--edit:hover {
    background: #f0f9ff;
    border-color: #bae6fd;
}

.m3-icon-btn--delete {
    color: #94a3b8;
}

.m3-icon-btn--delete:hover {
    background: #fff1f2;
    color: #e11d48;
    border-color: #fecdd3;
}

/* ── MD3 Switch Component ── */
.m3-switch {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    user-select: none;
}

.m3-switch input {
    display: none;
}

.m3-switch-track {
    width: 36px;
    height: 22px;
    border-radius: 9999px;
    background: #e2e8f0;
    position: relative;
    transition: background 0.2s ease;
    flex-shrink: 0;
}

.m3-switch-thumb {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.18);
    transition: transform 0.2s cubic-bezier(0.2, 0, 0, 1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.m3-switch input:checked + .m3-switch-track {
    background: #006837;
}

.m3-switch input:checked + .m3-switch-track .m3-switch-thumb {
    transform: translateX(14px);
}

.m3-switch-text {
    font-size: 12px;
    font-weight: 600;
    color: #475569;
}

.m3-switch--lg .m3-switch-track {
    width: 44px;
    height: 26px;
}

.m3-switch--lg .m3-switch-thumb {
    width: 20px;
    height: 20px;
}

.m3-switch--lg input:checked + .m3-switch-track .m3-switch-thumb {
    transform: translateX(18px);
}

/* ── MD3 Empty State ── */
.m3-empty-state {
    padding: 64px 24px;
    text-align: center;
    background: #ffffff;
    border: 2px dashed #cbd5e1;
    border-radius: 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.m3-empty-art {
    width: 72px;
    height: 72px;
    border-radius: 24px;
    background: #ecfdf5;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
}

.m3-empty-headline {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 6px;
}

.m3-empty-support {
    font-size: 13px;
    color: #64748b;
    max-width: 420px;
    margin: 0 0 20px;
}

/* ─────────────────────────────────────────────────────────────
   Material Design 3 (M3) Dialog Design System
───────────────────────────────────────────────────────────── */
.m3-dialog-surface {
    background: #ffffff;
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.2);
    display: flex;
    flex-direction: column;
    max-height: 88vh;
}

/* Dialog Header */
.m3-dialog-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px 28px 18px;
    border-bottom: 1px solid #f1f5f9;
    background: linear-gradient(180deg, #fafbfc 0%, #ffffff 100%);
}

.m3-dialog-lead {
    display: flex;
    align-items: center;
    gap: 14px;
}

.m3-dialog-icon-badge {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.m3-dialog-overline {
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #64748b;
}

.m3-dialog-headline {
    font-size: 19px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    line-height: 1.25;
}

.m3-dialog-close {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.m3-dialog-close:hover {
    background: #e2e8f0;
    color: #0f172a;
}

/* Dialog Tabs / Stepper Header */
.m3-dialog-tabs {
    display: flex;
    gap: 8px;
    padding: 8px 24px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    overflow-x: auto;
}

.m3-dialog-tab-btn {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    border-radius: 12px;
    border: none;
    background: transparent;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.m3-dialog-tab-btn:hover {
    color: #0f172a;
    background: #ffffff;
}

.m3-dialog-tab-btn--active {
    background: #ffffff !important;
    color: #006837 !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

/* Dialog Body */
.m3-dialog-body {
    padding: 24px 28px;
    overflow-y: auto;
    flex: 1;
}

.m3-section-title-wrap {
    margin-bottom: 20px;
}

.m3-section-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    color: #006837;
    background: #ecfdf5;
    padding: 2px 8px;
    border-radius: 6px;
    margin-bottom: 4px;
}

.m3-section-heading {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.m3-section-sub {
    font-size: 12.5px;
    color: #64748b;
    margin: 2px 0 0;
}

/* MD3 Type Selection Cards */
.m3-type-cards-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 14px;
}

@media (min-width: 640px) {
    .m3-type-cards-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

.m3-type-select-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 14px 10px;
    border-radius: 16px;
    border: 2px solid #e2e8f0;
    background: #ffffff;
    cursor: pointer;
    text-align: center;
    position: relative;
    transition: all 0.2s cubic-bezier(0.2, 0, 0, 1);
}

.m3-type-select-card:hover {
    border-color: var(--t-color);
    background: #fafbfc;
}

.m3-type-select-card--selected {
    border-color: var(--t-color) !important;
    background: var(--t-container) !important;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
}

.m3-type-select-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
}

.m3-type-select-card--selected .m3-type-select-icon {
    background: #ffffff;
}

.m3-type-select-name {
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.25;
}

.m3-type-select-hint {
    font-size: 10.5px;
    color: #64748b;
    margin-top: 2px;
}

.m3-type-select-check {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: var(--t-color);
    display: flex;
    align-items: center;
    justify-content: center;
}

.m3-banner-hint {
    display: flex;
    align-items: center;
    padding: 10px 14px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 16px;
}

/* ── MD3 Fields Grid & HTML5 Inputs ── */
.m3-fields-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 14px 16px;
}

.m3-col-12 { grid-column: span 12; }
.m3-col-6 { grid-column: span 12; }

@media (min-width: 640px) {
    .m3-col-6 { grid-column: span 6; }
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

.m3-native-input {
    flex: 1;
    border: none;
    outline: none;
    padding: 11px 0;
    font-size: 13.5px;
    color: #0f172a;
    background: transparent;
    font-family: inherit;
    width: 100%;
}

.m3-native-textarea {
    flex: 1;
    border: none;
    outline: none;
    font-size: 13.5px;
    color: #0f172a;
    background: transparent;
    font-family: inherit;
    resize: vertical;
    width: 100%;
    line-height: 1.5;
}

.m3-helper-text {
    font-size: 11px;
    color: #94a3b8;
    margin: 4px 0 0;
}

.m3-error-text {
    font-size: 11.5px;
    color: #e11d48;
    margin: 4px 0 0;
    font-weight: 500;
}

.m3-counter-text {
    font-size: 11px;
    color: #94a3b8;
}

/* ── MD3 Combobox Component (Custom Category Dropdown) ── */
.m3-combobox-wrapper {
    position: relative;
    width: 100%;
}

.m3-combobox-box {
    padding-right: 6px;
    cursor: text;
}

.m3-combobox-btn {
    border: none;
    background: transparent;
    color: #94a3b8;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.18s ease;
    flex-shrink: 0;
}

.m3-combobox-btn:hover {
    color: #0f172a;
    background: #e2e8f0;
}

.m3-combobox-btn--clear {
    margin-right: 2px;
}

.m3-combobox-btn--clear:hover {
    color: #e11d48;
    background: #ffe4e6;
}

.m3-combobox-btn--open {
    transform: rotate(180deg);
    color: #006837;
}

.m3-combobox-menu {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 12px 28px -4px rgba(15, 23, 42, 0.14), 0 4px 10px -2px rgba(15, 23, 42, 0.05);
    z-index: 60;
    overflow: hidden;
}

.m3-combobox-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px 8px;
    border-bottom: 1px solid #f1f5f9;
    background: #fafbfc;
}

.m3-combobox-header-title {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.m3-combobox-badge {
    font-size: 10.5px;
    font-weight: 600;
    color: #006837;
    background: #ecfdf5;
    padding: 2px 8px;
    border-radius: 9999px;
    border: 1px solid #d1fae5;
}

.m3-combobox-list {
    max-height: 220px;
    overflow-y: auto;
    padding: 6px;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.m3-combobox-list::-webkit-scrollbar {
    width: 5px;
}

.m3-combobox-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.m3-combobox-item {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 8px 10px;
    border-radius: 10px;
    border: 1px solid transparent;
    background: transparent;
    cursor: pointer;
    text-align: left;
    transition: all 0.15s ease;
    gap: 10px;
    font-family: inherit;
    font-size: 13px;
    color: #1e293b;
}

.m3-combobox-item:hover,
.m3-combobox-item--highlighted {
    background: #f8fafc;
    border-color: #e2e8f0;
    color: #0f172a;
}

.m3-combobox-item--active {
    background: #ecfdf5 !important;
    border-color: #a7f3d0 !important;
    color: #006837 !important;
    font-weight: 600;
}

.m3-combobox-item--custom {
    background: #f0fdf4;
    border: 1px dashed #86efac;
    margin-bottom: 4px;
}

.m3-combobox-item--custom:hover {
    background: #dcfce7;
    border-color: #4ade80;
}

.m3-combobox-item-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.15s ease;
}

.m3-combobox-item--active .m3-combobox-item-icon {
    background: #d1fae5;
}

.m3-combobox-item-icon--custom {
    background: #dcfce7;
}

.m3-combobox-item-text {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    line-height: 1.3;
}

.m3-combobox-item-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.m3-combobox-item-sub {
    font-size: 11px;
    color: #006837;
    font-weight: 600;
    margin-top: 1px;
}

.m3-combobox-item-check {
    flex-shrink: 0;
    margin-left: auto;
}

.m3-combobox-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px 12px;
    color: #94a3b8;
    font-size: 12.5px;
}

/* ── Interactive Chips (Pills) ── */
.m3-combobox-chips {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 7px;
    flex-wrap: wrap;
}

.m3-combobox-chips-label {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    flex-shrink: 0;
}

.m3-combobox-chips-list {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    align-items: center;
}

.m3-chip-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 9px;
    border-radius: 8px;
    font-size: 11.5px;
    font-weight: 500;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}

.m3-chip-pill:hover {
    border-color: #006837;
    color: #006837;
    background: #ecfdf5;
    transform: translateY(-1px);
}

.m3-chip-pill--active {
    border-color: #006837;
    background: #ecfdf5;
    color: #006837;
    font-weight: 600;
    box-shadow: 0 1px 4px rgba(0, 104, 55, 0.15);
}

/* ── Dropdown Transition ── */
.m3-dropdown-fade-enter-active,
.m3-dropdown-fade-leave-active {
    transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}

.m3-dropdown-fade-enter-from,
.m3-dropdown-fade-leave-to {
    opacity: 0;
    transform: translateY(-6px) scale(0.98);
}

/* ── MD3 Icon Picker Component ── */
.m3-icon-picker-module {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 16px;
}

.m3-icon-cat-pills {
    display: flex;
    gap: 4px;
    overflow-x: auto;
}

.m3-icon-cat-btn {
    border: none;
    background: transparent;
    font-size: 11.5px;
    font-weight: 600;
    color: #64748b;
    padding: 3px 8px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.m3-icon-cat-btn--active {
    background: #006837;
    color: #ffffff;
}

.m3-icon-selection-bar {
    display: flex;
    gap: 12px;
    align-items: center;
}

.m3-icon-active-preview {
    width: 76px;
    height: 76px;
    border-radius: 16px;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    padding: 6px;
}

.m3-icon-active-name {
    font-size: 9px;
    color: #64748b;
    margin-top: 4px;
    max-width: 64px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: center;
}

.m3-icon-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    flex: 1;
}

.m3-icon-tile {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.m3-icon-tile:hover {
    border-color: #006837;
    color: #006837;
    transform: scale(1.06);
}

.m3-icon-tile--active {
    background: #006837 !important;
    border-color: #006837 !important;
    color: #ffffff !important;
}

/* ── MD3 Upload Dropzone ── */
.m3-upload-card {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 16px;
}

@media (min-width: 640px) {
    .m3-upload-card {
        grid-template-columns: 220px 1fr;
    }
}

.m3-dropzone {
    height: 150px;
    border-radius: 14px;
    border: 2px dashed #cbd5e1;
    background: #ffffff;
    cursor: pointer;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    transition: all 0.2s ease;
}

.m3-dropzone:hover {
    border-color: #006837;
    background: #ecfdf5;
}

.m3-dropzone--has-preview {
    border-style: solid;
    border-color: #e2e8f0;
}

.m3-dropzone-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.m3-dropzone-placeholder {
    padding: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.m3-dropzone-icon-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #ecfdf5;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 6px;
}

.m3-dropzone-title {
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
}

.m3-dropzone-subtitle {
    font-size: 10.5px;
    color: #94a3b8;
    margin-top: 2px;
}

.m3-dropzone-badge {
    margin-top: 8px;
    font-size: 9.5px;
    font-weight: 700;
    color: #006837;
    background: #ecfdf5;
    padding: 2px 7px;
    border-radius: 6px;
}

.m3-upload-actions {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
}

.m3-upload-divider {
    display: flex;
    align-items: center;
    text-align: center;
    font-size: 11px;
    color: #94a3b8;
    margin: 4px 0;
}

.m3-upload-divider::before,
.m3-upload-divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #e2e8f0;
}

.m3-upload-divider span {
    padding: 0 8px;
}

/* ── Visibility Card ── */
.m3-visibility-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
}

.m3-visibility-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
}

.m3-visibility-desc {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
    max-width: 540px;
}

/* ── Dialog Footer Buttons (Material 3 Standard) ── */
.m3-dialog-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 28px;
    border-top: 1px solid #f1f5f9;
    background: #fafbfc;
}

.m3-dialog-footer-right {
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.m3-btn-text {
    border: none;
    background: transparent;
    color: #475569;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 10px;
    transition: all 0.15s ease;
}

.m3-btn-text:hover {
    background: #f1f5f9;
    color: #0f172a;
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
    transition: all 0.15s ease;
}

.m3-btn-tonal:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.m3-btn-tonal--primary {
    background: #ecfdf5;
    color: #006837;
}

.m3-btn-tonal--primary:hover {
    background: #d1fae5;
    color: #00502a;
}

.m3-btn-tonal--danger {
    background: #fff1f2;
    color: #e11d48;
}

.m3-btn-tonal--danger:hover {
    background: #ffe4e6;
}

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
    transition: all 0.2s ease;
}

.m3-btn-filled:hover {
    background: linear-gradient(135deg, #00502a 0%, #006837 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(0, 104, 55, 0.35);
}

.m3-btn-filled:disabled {
    opacity: 0.65;
    cursor: wait;
}

/* Responsive adjust */
@media (max-width: 640px) {
    .m3-page-container {
        padding: 20px 16px 40px;
    }
    .m3-dialog-header,
    .m3-dialog-body,
    .m3-dialog-footer {
        padding-left: 16px;
        padding-right: 16px;
    }
}
</style>
