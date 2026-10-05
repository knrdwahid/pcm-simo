<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    appName: { type: String, default: 'PCM Simo' },
    officials: { type: Array, default: () => [] },
    aums: { type: Array, default: () => [] },
    prayerSchedule: { type: Array, default: () => [] },
    prayerSource: { type: String, default: 'static' },
    prayerDate: { type: String, default: '' },
});

// ── Navigation Tabs ──
const validTabs = ['pimpinan', 'visimisi', 'majelis', 'aum'];
const activeTab = ref('pimpinan');
const searchQuery = ref('');
const aumFilterCategory = ref('semua');
const aumSearch = ref('');

// Synchronize tab with URL query parameter
onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const tabParam = params.get('tab');
    if (tabParam && validTabs.includes(tabParam)) {
        activeTab.value = tabParam;
    }
});

const setTab = (tab) => {
    activeTab.value = tab;
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.replaceState({}, '', url.toString());
};

// ── Pimpinan Data ──
const ketua = computed(() => {
    return props.officials.find(o => o.sort_order === 1 || o.position?.toLowerCase().includes('ketua')) || {
        name: 'H. Sholihin, S.Pd',
        position: 'Ketua PCM Simo',
        period: '2023 - 2028',
    };
});

const getInitials = (name) => {
    if (!name) return 'PCM';
    const clean = name.replace(/^(Drs\.|H\.|Ir\.|dr\.|Prof\.|Hj\.)\s*/gi, '').trim();
    const parts = clean.split(/\s+/).filter(Boolean);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return clean.substring(0, 2).toUpperCase();
};

const anggotaList = computed(() => {
    const list = props.officials.filter(o => o.id !== ketua.value?.id && o.sort_order !== 1);
    if (!searchQuery.value.trim()) return list;

    const q = searchQuery.value.toLowerCase();
    return list.filter(o => o.name.toLowerCase().includes(q));
});

// ── Majelis & Lembaga ──
const defaultIcons = {
    organisasi: 'mdi-sitemap-outline',
    ortom: 'mdi-account-group-outline',
    amal_usaha: 'mdi-office-building-outline',
    masjid: 'mdi-mosque',
};

const byType = (type) =>
    props.aums
        .filter(a => (a.type || 'amal_usaha') === type)
        .map(a => ({ ...a, desc: a.description, icon: a.icon || defaultIcons[type] }));

const majelisList = computed(() => byType('organisasi'));
const ortomList = computed(() => byType('ortom'));
const amalUsahaList = computed(() => byType('amal_usaha'));
const masjidList = computed(() => byType('masjid'));

// All AUM + Masjid + Ortom for Directory Filter
const allDirectoryItems = computed(() => {
    let items = [...props.aums];

    // Filter by type
    if (aumFilterCategory.value === 'sekolah') {
        items = items.filter(i => (
            i.category?.toLowerCase().includes('sekolah') ||
            i.category?.toLowerCase().includes('pendidikan') ||
            i.name?.toLowerCase().includes('sd') ||
            i.name?.toLowerCase().includes('smp') ||
            i.name?.toLowerCase().includes('mi') ||
            i.name?.toLowerCase().includes('tk') ||
            i.name?.toLowerCase().includes('paud')
        ));
    } else if (aumFilterCategory.value === 'kesehatan') {
        items = items.filter(i => (
            i.category?.toLowerCase().includes('kesehatan') ||
            i.name?.toLowerCase().includes('klinik') ||
            i.name?.toLowerCase().includes('pku')
        ));
    } else if (aumFilterCategory.value === 'masjid') {
        items = items.filter(i => i.type === 'masjid' || i.category?.toLowerCase().includes('masjid'));
    } else if (aumFilterCategory.value === 'ortom') {
        items = items.filter(i => i.type === 'ortom');
    }

    // Filter by search
    if (aumSearch.value.trim()) {
        const q = aumSearch.value.toLowerCase();
        items = items.filter(i =>
            i.name?.toLowerCase().includes(q) ||
            i.address?.toLowerCase().includes(q) ||
            i.category?.toLowerCase().includes(q) ||
            i.leader?.toLowerCase().includes(q)
        );
    }

    return items;
});

const getTypeBadge = (type) => {
    switch (type) {
        case 'masjid':
            return { label: 'Masjid & Dakwah', class: 'badge--masjid' };
        case 'ortom':
            return { label: 'Organisasi Otonom', class: 'badge--ortom' };
        case 'organisasi':
            return { label: 'Majelis / Lembaga', class: 'badge--organisasi' };
        default:
            return { label: 'Amal Usaha', class: 'badge--aum' };
    }
};
</script>

<template>
    <Head title="Profil Organisasi & Pimpinan | PCM Simo" />

    <PublicLayout>
        <div class="org-wrapper">
            <!-- ── Material Design 3 Hero Surface (Clean & Uncluttered) ── -->
            <header class="org-hero">
                <div class="org-hero-inner">
                    <nav class="org-breadcrumbs" aria-label="Breadcrumb">
                        <Link href="/" class="org-breadcrumb-link">
                            <v-icon size="14" class="mr-1">mdi-home-outline</v-icon>
                            Beranda
                        </Link>
                        <v-icon size="14" class="org-breadcrumb-sep">mdi-chevron-right</v-icon>
                        <span class="org-breadcrumb-current">Profil Organisasi</span>
                    </nav>

                    <div class="org-hero-main">
                        <div class="org-hero-text">
                            <div class="org-badge-row">
                                <span class="org-badge-pill org-badge-pill--period">
                                    <v-icon size="14" class="mr-1.5 text-emerald-300">mdi-calendar-check</v-icon>
                                    Periode 2023 – 2028
                                </span>
                            </div>

                            <h1 class="org-hero-title">
                                Pimpinan Cabang Muhammadiyah Simo
                            </h1>

                            <p class="org-hero-desc">
                                Struktur kepemimpinan persyarikatan, majelis pembantu, dan jaringan amal usaha dakwah
                                pencerahan di Kecamatan Simo, Kabupaten Boyolali.
                            </p>
                        </div>
                    </div>

                    <!-- ── Quick Statistics Counter Row ── -->
                    <div class="org-hero-stats">
                        <div class="org-stat-item">
                            <span class="org-stat-num">9</span>
                            <span class="org-stat-label">Personalia Pimpinan</span>
                        </div>
                        <div class="org-stat-divider"></div>
                        <div class="org-stat-item">
                            <span class="org-stat-num">{{ majelisList.length || 6 }}</span>
                            <span class="org-stat-label">Majelis &amp; Lembaga</span>
                        </div>
                        <div class="org-stat-divider"></div>
                        <div class="org-stat-item">
                            <span class="org-stat-num">{{ ortomList.length || 2 }}</span>
                            <span class="org-stat-label">Organisasi Otonom</span>
                        </div>
                        <div class="org-stat-divider"></div>
                        <div class="org-stat-item">
                            <span class="org-stat-num">{{ (amalUsahaList.length + masjidList.length) || 12 }}+</span>
                            <span class="org-stat-label">AUM &amp; Masjid</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ── Material Design 3 Sticky Sub-Navbar ── -->
            <div class="org-sticky-nav-wrap">
                <nav class="org-sticky-nav" aria-label="Navigasi Menu Organisasi">
                    <button
                        type="button"
                        class="org-nav-tab"
                        :class="{ 'org-nav-tab--active': activeTab === 'pimpinan' }"
                        @click="setTab('pimpinan')"
                    >
                        <v-icon size="18" class="mr-2">mdi-account-group-outline</v-icon>
                        <span>Struktural Pimpinan</span>
                    </button>

                    <button
                        type="button"
                        class="org-nav-tab"
                        :class="{ 'org-nav-tab--active': activeTab === 'visimisi' }"
                        @click="setTab('visimisi')"
                    >
                        <v-icon size="18" class="mr-2">mdi-bullseye-arrow</v-icon>
                        <span>Visi &amp; Misi</span>
                    </button>

                    <button
                        type="button"
                        class="org-nav-tab"
                        :class="{ 'org-nav-tab--active': activeTab === 'majelis' }"
                        @click="setTab('majelis')"
                    >
                        <v-icon size="18" class="mr-2">mdi-sitemap-outline</v-icon>
                        <span>Majelis &amp; Lembaga</span>
                    </button>

                    <button
                        type="button"
                        class="org-nav-tab"
                        :class="{ 'org-nav-tab--active': activeTab === 'aum' }"
                        @click="setTab('aum')"
                    >
                        <v-icon size="18" class="mr-2">mdi-domain</v-icon>
                        <span>Amal Usaha (AUM) &amp; Ortom</span>
                    </button>
                </nav>
            </div>

            <!-- ── MAIN CONTENT CONTAINER (Spacious, Clean, Relaxed) ── -->
            <main class="org-content-container">
                <!-- ═════════════════════════════════════════════════════════
                     TAB 1: STRUKTURAL PIMPINAN (Clean, Spacious MD3)
                     ═════════════════════════════════════════════════════════ -->
                <section v-show="activeTab === 'pimpinan'" class="org-tab-section" aria-label="Struktural Pimpinan">
                    <!-- Section Header -->
                    <div class="org-section-heading text-center">
                        <span class="org-sub-badge">Dewan Kepemimpinan</span>
                        <h2 class="org-heading-title">Struktur Pimpinan Cabang Muhammadiyah Simo</h2>
                        <p class="org-heading-desc">
                            Masa amaliyah kepengurusan periode 2023 – 2028 yang diamanahkan untuk menggerakkan roda persyarikatan di bumi Simo.
                        </p>
                    </div>

                    <!-- Ketua Card (Featured Elevated Card) -->
                    <div class="org-ketua-hero-card">
                        <div class="org-ketua-glow"></div>
                        <div class="org-ketua-content">
                            <div class="org-ketua-avatar-wrap">
                                <div class="org-ketua-avatar">
                                    <span>{{ getInitials(ketua.name) }}</span>
                                </div>
                                <span class="org-ketua-verified" title="Pimpinan Terverifikasi">
                                    <v-icon size="16" color="#ffffff">mdi-check-decagram</v-icon>
                                </span>
                            </div>

                            <div class="org-ketua-details">
                                <div class="org-role-badge">
                                    <v-icon size="14" class="mr-1 text-emerald-700">mdi-shield-crown-outline</v-icon>
                                    Ketua Pimpinan Cabang
                                </div>
                                <h3 class="org-ketua-name">{{ ketua.name }}</h3>
                                <p class="org-ketua-meta">
                                    Pimpinan Cabang Muhammadiyah Simo &bull; Periode 2023 – 2028
                                </p>
                                <p class="org-ketua-quote">
                                    "Mengabdi dengan ikhlas, menguatkan persaudaraan jamaah, dan memajukan pendidikan serta kemaslahatan umat berlandaskan Al-Qur'an dan As-Sunnah."
                                </p>
                                <div class="org-ketua-footer">
                                    <span class="org-footer-tag">
                                        <v-icon size="13" class="mr-1 text-emerald-700">mdi-calendar-check</v-icon>
                                        Masa Khidmat 2023 – 2028
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Anggota Pimpinan Cabang Grid -->
                    <div class="org-members-section">
                        <div class="org-members-toolbar">
                            <div>
                                <h3 class="org-members-title">Anggota Pimpinan Cabang</h3>
                                <p class="org-members-sub">8 Personalia Pimpinan Cabang Muhammadiyah Simo</p>
                            </div>

                            <!-- Search Filter -->
                            <div class="org-search-box">
                                <v-icon size="18" class="org-search-icon">mdi-magnify</v-icon>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari nama anggota pimpinan..."
                                    class="org-search-input"
                                />
                                <button
                                    v-if="searchQuery"
                                    type="button"
                                    class="org-search-clear"
                                    @click="searchQuery = ''"
                                    title="Bersihkan pencarian"
                                >
                                    <v-icon size="16">mdi-close</v-icon>
                                </button>
                            </div>
                        </div>

                        <!-- Spacious 2-3 Column Card Grid -->
                        <div class="org-members-grid">
                            <article
                                v-for="(agg, index) in anggotaList"
                                :key="agg.id || index"
                                class="org-member-card"
                            >
                                <div class="org-member-card-body">
                                    <div class="org-member-avatar">
                                        <span>{{ getInitials(agg.name) }}</span>
                                    </div>
                                    <div class="org-member-info">
                                        <span class="org-member-role-tag">Anggota Pimpinan</span>
                                        <h4 class="org-member-name">{{ agg.name }}</h4>
                                        <div class="org-member-meta">
                                            <span>Masa Jabatan 2023 – 2028</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="org-member-card-footer">
                                    <v-icon size="12" class="mr-1 text-emerald-700">mdi-account-check-outline</v-icon>
                                    <span>Pimpinan Cabang Simo</span>
                                </div>
                            </article>
                        </div>

                        <!-- Empty state if search doesn't find -->
                        <div v-if="anggotaList.length === 0" class="org-empty-box">
                            <v-icon size="40" color="#94a3b8">mdi-account-search-outline</v-icon>
                            <p class="font-semibold text-slate-700 mt-2">Nama tidak ditemukan</p>
                            <p class="text-xs text-slate-500">Tidak ada anggota pimpinan dengan kata kunci "{{ searchQuery }}"</p>
                            <button type="button" class="org-btn-clear-search mt-3" @click="searchQuery = ''">
                                Tampilkan Semua Anggota
                            </button>
                        </div>
                    </div>
                </section>

                <!-- ═════════════════════════════════════════════════════════
                     TAB 2: VISI & MISI (Clean Editorial Layout)
                     ═════════════════════════════════════════════════════════ -->
                <section v-show="activeTab === 'visimisi'" class="org-tab-section" aria-label="Visi & Misi">
                    <div class="org-editorial-container">
                        <!-- Section Header -->
                        <div class="org-section-heading text-center mb-8">
                            <span class="org-sub-badge">Arah &amp; Landasan Gerak</span>
                            <h2 class="org-heading-title">Visi &amp; Misi Persyarikatan</h2>
                            <p class="org-heading-desc">
                                Panduan langkah dan komitmen dakwah pencerahan PCM Simo dalam mewujudkan masyarakat Islam yang sebenar-benarnya.
                            </p>
                        </div>

                        <!-- Visi Card -->
                        <div class="org-vision-card mb-8">
                            <div class="org-vision-watermark">
                                <v-icon size="110" color="rgba(0, 104, 55, 0.05)">mdi-format-quote-close</v-icon>
                            </div>
                            <div class="org-vision-content">
                                <div class="org-pillar-pill">
                                    <v-icon size="15" class="mr-1 text-emerald-700">mdi-eye-outline</v-icon>
                                    Visi Persyarikatan
                                </div>
                                <blockquote class="org-vision-quote">
                                    “Terwujudnya masyarakat Islam yang sebenar-benarnya di wilayah Kecamatan Simo yang berakhlak mulia, mandiri, unggul dalam pendidikan dan kesehatan, serta menjadi teladan rahmatan lil 'alamin.”
                                </blockquote>
                                <p class="org-vision-source">
                                    &mdash; Garis Kebijakan Musyawarah Cabang Muhammadiyah Simo Periode 2023–2028
                                </p>
                            </div>
                        </div>

                        <!-- Misi List -->
                        <div class="org-mission-box">
                            <div class="d-flex align-center ga-2 mb-4">
                                <div class="org-icon-badge">
                                    <v-icon size="20" color="#006837">mdi-format-list-checks</v-icon>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">Misi Utama PCM Simo</h3>
                                    <p class="text-xs text-slate-500">4 Pilar gerakan mewujudkan kemaslahatan dakwah dan umat</p>
                                </div>
                            </div>

                            <div class="org-mission-grid">
                                <div class="org-mission-item">
                                    <div class="org-mission-num">01</div>
                                    <div>
                                        <h4 class="org-mission-title">Dakwah Tarjih &amp; Tabligh</h4>
                                        <p class="org-mission-desc">Meneguhkan dakwah Islam berlandaskan Al-Qur'an dan Sunnah Shohihah melalui kajian HPT dan pembinaan ranting berkelanjutan.</p>
                                    </div>
                                </div>

                                <div class="org-mission-item">
                                    <div class="org-mission-num">02</div>
                                    <div>
                                        <h4 class="org-mission-title">Penguatan Mutu Amal Usaha</h4>
                                        <p class="org-mission-desc">Meningkatkan tata kelola pendidikan madrasah/sekolah dan fasilitas amal usaha Muhammadiyah agar berdaya saing unggul.</p>
                                    </div>
                                </div>

                                <div class="org-mission-item">
                                    <div class="org-mission-num">03</div>
                                    <div>
                                        <h4 class="org-mission-title">Pelayanan Sosial &amp; Kesehatan</h4>
                                        <p class="org-mission-desc">Menguatkan kepedulian sosial melalui Klinik Pratama PKU Muhammadiyah Simo, pendampingan dhuafa, dan sinergi Lazismu.</p>
                                    </div>
                                </div>

                                <div class="org-mission-item">
                                    <div class="org-mission-num">04</div>
                                    <div>
                                        <h4 class="org-mission-title">Kaderisasi &amp; Pemberdayaan Ortom</h4>
                                        <p class="org-mission-desc">Mengokohkan regenerasi kader pemuda, perempuan, dan relawan tanggap bencana untuk kesinambungan dakwah persyarikatan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ═════════════════════════════════════════════════════════
                     TAB 3: MAJELIS & LEMBAGA (Clean Unhurried Space)
                     ═════════════════════════════════════════════════════════ -->
                <section v-show="activeTab === 'majelis'" class="org-tab-section" aria-label="Majelis & Lembaga">
                    <div class="org-section-heading text-center mb-8">
                        <span class="org-sub-badge">Unsur Pembantu Pimpinan (UPP)</span>
                        <h2 class="org-heading-title">Majelis &amp; Lembaga Pelaksana Program</h2>
                        <p class="org-heading-desc">
                            Badan pembantu pimpinan yang menjalankan bidang operasional dakwah, pendidikan, kesehatan, dan kebencanaan di Simo.
                        </p>
                    </div>

                    <div class="org-majelis-grid">
                        <article
                            v-for="(maj, idx) in majelisList"
                            :key="maj.id || idx"
                            class="org-majelis-card"
                        >
                            <div class="org-majelis-icon-box">
                                <v-icon size="26" color="#006837">{{ maj.icon }}</v-icon>
                            </div>
                            <div class="org-majelis-body">
                                <div class="d-flex align-center justify-space-between mb-1">
                                    <span class="org-upp-tag">{{ maj.category || 'Majelis' }}</span>
                                    <span class="org-upp-status">Aktif</span>
                                </div>
                                <h3 class="org-majelis-title">{{ maj.name }}</h3>
                                <p class="org-majelis-desc">{{ maj.desc }}</p>
                            </div>
                        </article>
                    </div>

                    <div v-if="majelisList.length === 0" class="org-empty-box">
                        <v-icon size="36" color="#94a3b8">mdi-sitemap-outline</v-icon>
                        <p class="font-semibold text-slate-700 mt-2">Belum ada data Majelis/Lembaga</p>
                        <p class="text-xs text-slate-500">Data dapat dikelola melalui Dashboard Admin &rarr; AUM &amp; Organisasi</p>
                    </div>
                </section>

                <!-- ═════════════════════════════════════════════════════════
                     TAB 4: AMAL USAHA (AUM) & ORTOM (Directory Layout)
                     ═════════════════════════════════════════════════════════ -->
                <section v-show="activeTab === 'aum'" class="org-tab-section" aria-label="Amal Usaha & Ortom">
                    <div class="org-section-heading text-center mb-6">
                        <span class="org-sub-badge">Pilar Kemandirian Umat</span>
                        <h2 class="org-heading-title">Direktori Amal Usaha (AUM), Masjid &amp; Ortom</h2>
                        <p class="org-heading-desc">
                            Pusat layanan pendidikan, sarana ibadah, kesehatan, dan pergerakan otonom di bawah naungan PCM Simo.
                        </p>
                    </div>

                    <!-- Category Filter Chips & Search Bar -->
                    <div class="org-directory-toolbar">
                        <div class="org-filter-chips">
                            <button
                                type="button"
                                class="org-filter-chip"
                                :class="{ 'org-filter-chip--active': aumFilterCategory === 'semua' }"
                                @click="aumFilterCategory = 'semua'"
                            >
                                Semua Entitas ({{ props.aums.length }})
                            </button>

                            <button
                                type="button"
                                class="org-filter-chip"
                                :class="{ 'org-filter-chip--active': aumFilterCategory === 'sekolah' }"
                                @click="aumFilterCategory = 'sekolah'"
                            >
                                <v-icon size="14" class="mr-1">mdi-school-outline</v-icon>
                                Sekolah &amp; Pendidikan
                            </button>

                            <button
                                type="button"
                                class="org-filter-chip"
                                :class="{ 'org-filter-chip--active': aumFilterCategory === 'kesehatan' }"
                                @click="aumFilterCategory = 'kesehatan'"
                            >
                                <v-icon size="14" class="mr-1">mdi-hospital-box-outline</v-icon>
                                Kesehatan (PKU)
                            </button>

                            <button
                                type="button"
                                class="org-filter-chip"
                                :class="{ 'org-filter-chip--active': aumFilterCategory === 'masjid' }"
                                @click="aumFilterCategory = 'masjid'"
                            >
                                <v-icon size="14" class="mr-1">mdi-mosque</v-icon>
                                Masjid &amp; Musholla
                            </button>

                            <button
                                type="button"
                                class="org-filter-chip"
                                :class="{ 'org-filter-chip--active': aumFilterCategory === 'ortom' }"
                                @click="aumFilterCategory = 'ortom'"
                            >
                                <v-icon size="14" class="mr-1">mdi-account-group-outline</v-icon>
                                Organisasi Otonom
                            </button>
                        </div>

                        <!-- Instant Search -->
                        <div class="org-search-box min-w-[240px]">
                            <v-icon size="18" class="org-search-icon">mdi-magnify</v-icon>
                            <input
                                v-model="aumSearch"
                                type="text"
                                placeholder="Cari nama atau lokasi AUM..."
                                class="org-search-input"
                            />
                            <button
                                v-if="aumSearch"
                                type="button"
                                class="org-search-clear"
                                @click="aumSearch = ''"
                            >
                                <v-icon size="16">mdi-close</v-icon>
                            </button>
                        </div>
                    </div>

                    <!-- Directory Cards Grid -->
                    <div class="org-directory-grid">
                        <article
                            v-for="item in allDirectoryItems"
                            :key="item.id"
                            class="org-directory-card"
                        >
                            <!-- Card Header / Media -->
                            <div class="org-dir-top">
                                <div class="org-dir-icon">
                                    <v-icon size="24" color="#006837">{{ item.icon || 'mdi-office-building-outline' }}</v-icon>
                                </div>
                                <span :class="['org-dir-type-pill', getTypeBadge(item.type).class]">
                                    {{ getTypeBadge(item.type).label }}
                                </span>
                            </div>

                            <div class="org-dir-body">
                                <span v-if="item.category" class="org-dir-category">{{ item.category }}</span>
                                <h3 class="org-dir-title">{{ item.name }}</h3>

                                <p v-if="item.leader" class="org-dir-meta">
                                    <v-icon size="14" class="mr-1 text-emerald-700">mdi-account-tie</v-icon>
                                    <span>Pimpinan: <strong>{{ item.leader }}</strong></span>
                                </p>

                                <p v-if="item.address" class="org-dir-meta">
                                    <v-icon size="14" class="mr-1 text-slate-400">mdi-map-marker-outline</v-icon>
                                    <span>{{ item.address }}</span>
                                </p>

                                <p v-if="item.description" class="org-dir-desc">
                                    {{ item.description }}
                                </p>
                            </div>

                            <div class="org-dir-footer">
                                <a
                                    v-if="item.maps_url || item.address"
                                    :href="item.maps_url || `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(item.name + ' ' + (item.address || 'Simo Boyolali'))}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="org-dir-btn-action"
                                    title="Lihat titik lokasi di Google Maps"
                                >
                                    <v-icon size="14" class="mr-1">mdi-map-marker-radius</v-icon>
                                    <span>Petunjuk Lokasi</span>
                                </a>

                                <a
                                    v-if="item.phone"
                                    :href="`tel:${item.phone}`"
                                    class="org-dir-btn-icon"
                                    title="Hubungi telepon"
                                >
                                    <v-icon size="16">mdi-phone-outline</v-icon>
                                </a>

                                <a
                                    v-if="item.website"
                                    :href="item.website"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="org-dir-btn-icon"
                                    title="Kunjungi Website"
                                >
                                    <v-icon size="16">mdi-web</v-icon>
                                </a>
                            </div>
                        </article>
                    </div>

                    <!-- Empty State -->
                    <div v-if="allDirectoryItems.length === 0" class="org-empty-box">
                        <v-icon size="40" color="#94a3b8">mdi-store-search-outline</v-icon>
                        <p class="font-semibold text-slate-700 mt-2">Tidak ada data ditemukan</p>
                        <p class="text-xs text-slate-500">Coba ubah filter kategori atau kata kunci pencarian Anda.</p>
                        <button
                            type="button"
                            class="org-btn-clear-search mt-3"
                            @click="aumFilterCategory = 'semua'; aumSearch = ''"
                        >
                            Reset Filter Direktori
                        </button>
                    </div>
                </section>
            </main>
        </div>
    </PublicLayout>
</template>

<style scoped>
/* ─────────────────────────────────────────────────────────────
   MATERIAL DESIGN 3 & MODERN HTML5 DESIGN SYSTEM (AIRY & CLEAN)
   ───────────────────────────────────────────────────────────── */

.org-wrapper {
    min-height: 100vh;
    background-color: #fafbfc;
    font-family: 'Poppins', system-ui, -apple-system, sans-serif;
    color: #1e293b;
}

/* ── 1. Hero Header Surface ── */
.org-hero {
    background: linear-gradient(135deg, #00562e 0%, #006837 50%, #004624 100%);
    color: #ffffff;
    padding: 36px 20px 48px;
    position: relative;
    overflow: hidden;
}

.org-hero::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 320px;
    height: 320px;
    border-radius: 9999px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
    pointer-events: none;
}

.org-hero-inner {
    max-width: 1160px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.org-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    color: rgba(255, 255, 255, 0.75);
    margin-bottom: 20px;
}

.org-breadcrumb-link {
    color: rgba(255, 255, 255, 0.85);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: color 0.15s ease;
}

.org-breadcrumb-link:hover {
    color: #ffffff;
    text-decoration: underline;
}

.org-breadcrumb-sep {
    color: rgba(255, 255, 255, 0.4);
}

.org-breadcrumb-current {
    color: #ffffff;
    font-weight: 600;
}

.org-hero-main {
    margin-bottom: 32px;
}

.org-badge-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.org-badge-pill {
    display: inline-flex;
    align-items: center;
    font-size: 11.5px;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 9999px;
    letter-spacing: 0.02em;
}

.org-badge-pill--period {
    background: rgba(16, 185, 129, 0.2);
    color: #a7f3d0;
    border: 1px solid rgba(16, 185, 129, 0.35);
}

.org-hero-title {
    font-size: clamp(1.75rem, 3.2vw, 2.35rem);
    font-weight: 800;
    letter-spacing: -0.02em;
    line-height: 1.25;
    margin: 0 0 10px 0;
    color: #ffffff;
}

.org-hero-desc {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.85);
    line-height: 1.6;
    margin: 0;
    max-width: 660px;
}

/* Quick Statistics Counter */
.org-hero-stats {
    display: flex;
    align-items: center;
    background: rgba(0, 0, 0, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 18px;
    padding: 14px 24px;
    backdrop-filter: blur(6px);
    overflow-x: auto;
}

.org-stat-item {
    display: flex;
    flex-direction: column;
    padding: 0 18px;
}

.org-stat-num {
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1.1;
    color: #ffffff;
}

.org-stat-label {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.75);
    white-space: nowrap;
    margin-top: 2px;
}

.org-stat-divider {
    width: 1px;
    height: 32px;
    background: rgba(255, 255, 255, 0.18);
    flex-shrink: 0;
}

/* ── 2. Sticky Sub-Navbar ── */
.org-sticky-nav-wrap {
    position: sticky;
    top: 64px;
    z-index: 30;
    background: rgba(255, 255, 255, 0.94);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid #eef2f6;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.org-sticky-nav {
    max-width: 1160px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    overflow-x: auto;
    scrollbar-width: none;
}

.org-sticky-nav::-webkit-scrollbar {
    display: none;
}

.org-nav-tab {
    display: inline-flex;
    align-items: center;
    padding: 9px 18px;
    border-radius: 9999px;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    background: transparent;
    border: none;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s cubic-bezier(0.2, 0, 0, 1);
}

.org-nav-tab:hover {
    color: #006837;
    background: #f1f5f9;
}

.org-nav-tab--active {
    background: #006837;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 104, 55, 0.25);
}

.org-nav-tab--active:hover {
    background: #00562e;
    color: #ffffff;
}

/* ── 3. Main Content Container ── */
.org-content-container {
    max-width: 1160px;
    margin: 0 auto;
    padding: 44px 20px 80px;
}

.org-tab-section {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

.org-section-heading {
    margin-bottom: 32px;
}

.org-sub-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #006837;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    padding: 3px 12px;
    border-radius: 9999px;
    margin-bottom: 8px;
}

.org-heading-title {
    font-size: clamp(1.4rem, 2.2vw, 1.85rem);
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
    margin: 0 0 8px 0;
}

.org-heading-desc {
    font-size: 13.5px;
    color: #64748b;
    max-width: 620px;
    margin: 0 auto;
    line-height: 1.6;
}

/* ── 4. Ketua Hero Profile Card ── */
.org-ketua-hero-card {
    background: linear-gradient(135deg, #ffffff 0%, #f7fdf9 100%);
    border: 1px solid rgba(0, 104, 55, 0.15);
    border-radius: 24px;
    padding: 36px 32px;
    margin-bottom: 40px;
    box-shadow: 0 10px 30px rgba(0, 104, 55, 0.06);
    position: relative;
    overflow: hidden;
}

.org-ketua-glow {
    position: absolute;
    top: 0;
    right: 0;
    width: 250px;
    height: 250px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, rgba(0, 0, 0, 0) 70%);
    pointer-events: none;
}

.org-ketua-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 24px;
    position: relative;
    z-index: 1;
}

@media (min-width: 720px) {
    .org-ketua-content {
        flex-direction: row;
        align-items: center;
        text-align: left;
        gap: 36px;
    }
}

.org-ketua-avatar-wrap {
    position: relative;
    flex-shrink: 0;
}

.org-ketua-avatar {
    width: 110px;
    height: 110px;
    border-radius: 9999px;
    background: linear-gradient(135deg, #006837 0%, #10b981 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    font-weight: 800;
    box-shadow: 0 8px 24px rgba(0, 104, 55, 0.28);
    border: 4px solid #ffffff;
}

.org-ketua-verified {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 28px;
    height: 28px;
    border-radius: 9999px;
    background: #006837;
    border: 2px solid #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.org-ketua-details {
    flex: 1;
}

.org-role-badge {
    display: inline-flex;
    align-items: center;
    background: #ecfdf5;
    color: #065f46;
    font-size: 11.5px;
    font-weight: 700;
    padding: 3px 12px;
    border-radius: 9999px;
    border: 1px solid #a7f3d0;
    margin-bottom: 8px;
}

.org-ketua-name {
    font-size: clamp(1.4rem, 2vw, 1.75rem);
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 6px 0;
}

.org-ketua-meta {
    font-size: 13px;
    color: #64748b;
    margin: 0 0 12px 0;
}

.org-ketua-quote {
    font-size: 13.5px;
    font-style: italic;
    color: #334155;
    line-height: 1.6;
    margin: 0 0 14px 0;
    max-width: 620px;
}

.org-footer-tag {
    display: inline-flex;
    align-items: center;
    font-size: 11.5px;
    color: #065f46;
    background: #ecfdf5;
    padding: 3px 12px;
    border-radius: 9999px;
    font-weight: 600;
}

/* ── 5. Members Section Grid ── */
.org-members-toolbar {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 24px;
}

@media (min-width: 720px) {
    .org-members-toolbar {
        flex-direction: row;
        align-items: flex-end;
        justify-content: space-between;
    }
}

.org-members-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 4px 0;
}

.org-members-sub {
    font-size: 12.5px;
    color: #64748b;
    margin: 0;
}

.org-search-box {
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 9999px;
    padding: 6px 14px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
}

.org-search-box:focus-within {
    border-color: #006837;
    box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.12);
}

.org-search-icon {
    color: #94a3b8;
    margin-right: 8px;
}

.org-search-input {
    border: none;
    outline: none;
    font-size: 12.5px;
    color: #1e293b;
    width: 100%;
    background: transparent;
}

.org-search-clear {
    background: transparent;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.org-members-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}

@media (min-width: 640px) {
    .org-members-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1000px) {
    .org-members-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
}

.org-member-card {
    background: #ffffff;
    border: 1px solid #eef2f6;
    border-radius: 20px;
    padding: 22px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.02);
}

.org-member-card:hover {
    transform: translateY(-3px);
    border-color: #cbd5e1;
    box-shadow: 0 10px 24px rgba(0, 104, 55, 0.08);
}

.org-member-card-body {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 16px;
}

.org-member-avatar {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    background: #ecfdf5;
    color: #006837;
    font-size: 18px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 1px solid #a7f3d0;
}

.org-member-info {
    flex: 1;
    min-width: 0;
}

.org-member-role-tag {
    display: inline-block;
    font-size: 10.5px;
    font-weight: 700;
    color: #047857;
    background: #f0fdf4;
    padding: 2px 8px;
    border-radius: 6px;
    margin-bottom: 4px;
}

.org-member-name {
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
    margin: 0 0 4px 0;
}

.org-member-meta {
    font-size: 11.5px;
    color: #64748b;
}

.org-member-card-footer {
    display: flex;
    align-items: center;
    font-size: 11px;
    color: #64748b;
    padding-top: 12px;
    border-top: 1px dashed #f1f5f9;
}

/* ── 6. Tab 2: Visi & Misi Styles ── */
.org-editorial-container {
    max-width: 900px;
    margin: 0 auto;
}

.org-vision-card {
    background: linear-gradient(135deg, #00562e 0%, #006837 100%);
    border-radius: 24px;
    padding: 40px 36px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 12px 36px rgba(0, 104, 55, 0.2);
}

.org-vision-watermark {
    position: absolute;
    bottom: -20px;
    right: 20px;
    opacity: 0.6;
    pointer-events: none;
}

.org-vision-content {
    position: relative;
    z-index: 1;
}

.org-pillar-pill {
    display: inline-flex;
    align-items: center;
    background: #ecfdf5;
    color: #065f46;
    font-size: 11.5px;
    font-weight: 700;
    padding: 4px 14px;
    border-radius: 9999px;
    margin-bottom: 16px;
}

.org-vision-quote {
    font-size: clamp(1.15rem, 1.8vw, 1.45rem);
    font-weight: 600;
    line-height: 1.7;
    margin: 0 0 16px 0;
    color: #ffffff;
}

.org-vision-source {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.7);
    margin: 0;
}

/* Misi Section */
.org-mission-box {
    background: #ffffff;
    border: 1px solid #eef2f6;
    border-radius: 24px;
    padding: 32px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}

.org-icon-badge {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #ecfdf5;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.org-mission-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 18px;
    margin-top: 16px;
}

@media (min-width: 640px) {
    .org-mission-grid {
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
}

.org-mission-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 16px;
    padding: 18px;
}

.org-mission-num {
    font-size: 1.15rem;
    font-weight: 800;
    color: #006837;
    background: #ecfdf5;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.org-mission-title {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 4px 0;
}

.org-mission-desc {
    font-size: 12px;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}

/* ── 7. Tab 3: Majelis & Lembaga Grid ── */
.org-majelis-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}

@media (min-width: 640px) {
    .org-majelis-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1000px) {
    .org-majelis-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
}

.org-majelis-card {
    background: #ffffff;
    border: 1px solid #eef2f6;
    border-radius: 20px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
    transition: all 0.2s ease;
}

.org-majelis-card:hover {
    transform: translateY(-3px);
    border-color: #cbd5e1;
    box-shadow: 0 8px 24px rgba(0, 104, 55, 0.08);
}

.org-majelis-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.org-upp-tag {
    font-size: 10.5px;
    font-weight: 700;
    color: #047857;
    background: #ecfdf5;
    padding: 2px 8px;
    border-radius: 6px;
}

.org-upp-status {
    font-size: 10px;
    font-weight: 600;
    color: #10b981;
}

.org-majelis-title {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.35;
    margin: 4px 0 6px 0;
}

.org-majelis-desc {
    font-size: 12.5px;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}

/* ── 8. Tab 4: Directory Toolbar & Grid ── */
.org-directory-toolbar {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 28px;
}

@media (min-width: 860px) {
    .org-directory-toolbar {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.org-filter-chips {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.org-filter-chip {
    display: inline-flex;
    align-items: center;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #475569;
    padding: 7px 16px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
}

.org-filter-chip:hover {
    border-color: #006837;
    color: #006837;
}

.org-filter-chip--active {
    background: #006837;
    color: #ffffff;
    border-color: #006837;
    box-shadow: 0 4px 12px rgba(0, 104, 55, 0.2);
}

.org-filter-chip--active:hover {
    background: #00562e;
    color: #ffffff;
}

.org-directory-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}

@media (min-width: 640px) {
    .org-directory-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1040px) {
    .org-directory-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
}

.org-directory-card {
    background: #ffffff;
    border: 1px solid #eef2f6;
    border-radius: 20px;
    padding: 22px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.02);
}

.org-directory-card:hover {
    transform: translateY(-3px);
    border-color: #cbd5e1;
    box-shadow: 0 8px 24px rgba(0, 104, 55, 0.08);
}

.org-dir-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.org-dir-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #ecfdf5;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #a7f3d0;
}

.org-dir-type-pill {
    font-size: 10.5px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 9999px;
}

.badge--aum { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
.badge--masjid { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.badge--ortom { background: #fdf4ff; color: #86198f; border: 1px solid #f0abfc; }
.badge--organisasi { background: #fefce8; color: #854d0e; border: 1px solid #fef08a; }

.org-dir-body {
    flex: 1;
    margin-bottom: 16px;
}

.org-dir-category {
    font-size: 11px;
    color: #64748b;
    display: block;
    margin-bottom: 2px;
}

.org-dir-title {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.35;
    margin: 0 0 8px 0;
}

.org-dir-meta {
    display: flex;
    align-items: flex-start;
    font-size: 12px;
    color: #475569;
    margin: 4px 0;
    line-height: 1.4;
}

.org-dir-desc {
    font-size: 12px;
    color: #64748b;
    line-height: 1.55;
    margin-top: 8px;
}

.org-dir-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-top: 14px;
    border-top: 1px dashed #f1f5f9;
}

.org-dir-btn-action {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    color: #006837;
    border: 1px solid #e2e8f0;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11.5px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s ease;
}

.org-dir-btn-action:hover {
    background: #006837;
    color: #ffffff;
    border-color: #006837;
}

.org-dir-btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #475569;
    text-decoration: none;
    transition: all 0.15s ease;
}

.org-dir-btn-icon:hover {
    background: #006837;
    color: #ffffff;
    border-color: #006837;
}

/* ── 9. Empty Box ── */
.org-empty-box {
    text-align: center;
    padding: 48px 20px;
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 20px;
    margin: 20px 0;
}

.org-btn-clear-search {
    background: #006837;
    color: #ffffff;
    border: none;
    padding: 7px 16px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}
</style>
