<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    appName: { type: String, default: 'PCM Simo' },
    officials: { type: Array, default: () => [] },
    aums: { type: Array, default: () => [] },
    prayerSchedule: { type: Array, default: () => [] },
    prayerSource: { type: String, default: 'static' },
    prayerDate: { type: String, default: '' },
    skInfo: {
        type: Object,
        default: () => ({
            nomor: '100 / KEP / III.0 / D / 2023',
            tentang: 'Penetapan Ketua dan Anggota Pimpinan Cabang Muhammadiyah Simo Periode 2023 - 2028',
            penerbit: 'Pimpinan Daerah Muhammadiyah Boyolali',
            tanggal_hijriyah: '15 Rabiul Akhir 1445 H',
            tanggal_masehi: '30 Oktober 2023 M',
            surat_permohonan: 'Nomor 064/IV.0/A/2023 tanggal 20 Oktober 2023 M / 5 Robiul Akhir 1445 H',
            ketua_pdm: 'Drs. H. Ali Muhson, M.Ag., M.PdI., M.H., M.M.',
            nbm_ketua: '772695',
            sekretaris_pdm: 'Drs. H. Aminudin Aziz',
            nbm_sekretaris: '919303',
            periode: '2023 - 2028',
        }),
    },
});

// Active Tab
const activeTab = ref('pimpinan');
const searchQuery = ref('');
const showSkDialog = ref(false);

// Ketua & Anggota
const ketua = computed(() => {
    return props.officials.find(o => o.sort_order === 1 || o.position.toLowerCase().includes('ketua')) || {
        name: 'H. Sholihin, S.Pd',
        position: 'Ketua PCM Simo',
        period: '2023 - 2028',
    };
});

const getInitials = (name) => {
    if (!name) return 'PCM';
    const clean = name.replace(/^(Drs\.|H\.|Ir\.|dr\.|Prof\.)\s*/gi, '').trim();
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

// Data organisasi dikelola melalui Dashboard → AUM & Organisasi
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

// Majelis & Lembaga
const majelisList = computed(() => byType('organisasi'));

// Organisasi Otonom
const ortomList = computed(() => byType('ortom'));

// Amal Usaha & Masjid
const amalUsahaList = computed(() => byType('amal_usaha'));
const masjidList = computed(() => byType('masjid'));
</script>

<template>
    <Head title="Profil Organisasi & Pimpinan | PCM Simo" />

    <PublicLayout>
        <div class="m3-container">
            <!-- ── Page Header (Clean Material Surface) ── -->
            <div class="m3-page-header">
                <nav class="m3-breadcrumbs" aria-label="Breadcrumb">
                    <Link href="/" class="m3-breadcrumb-link">Beranda</Link>
                    <v-icon size="14" class="m3-breadcrumb-sep">mdi-chevron-right</v-icon>
                    <span class="m3-breadcrumb-current">Profil Organisasi</span>
                </nav>

                <div class="m3-header-content">
                    <div class="m3-title-wrap">
                        <span class="m3-badge">Periode 2023 – 2028</span>
                        <h1 class="m3-headline">Pimpinan Cabang Muhammadiyah Simo</h1>
                        <p class="m3-subhead">
                            Susunan kepemimpinan persyarikatan berdasarkan Surat Keputusan PDM Boyolali Nomor: 100/KEP/III.0/D/2023.
                        </p>
                    </div>

                    <div class="m3-header-action">
                        <button type="button" class="m3-btn-tonal" @click="showSkDialog = true">
                            <v-icon size="18" class="mr-2">mdi-file-certificate-outline</v-icon>
                            <span>Dokumen SK</span>
                        </button>
                    </div>
                </div>

                <!-- ── MD3 Segmented Tabs ── -->
                <div class="m3-tab-bar" role="tablist">
                    <button
                        type="button"
                        class="m3-tab-item"
                        :class="{ 'm3-tab-item--active': activeTab === 'pimpinan' }"
                        role="tab"
                        :aria-selected="activeTab === 'pimpinan'"
                        @click="activeTab = 'pimpinan'"
                    >
                        <v-icon size="18" class="mr-2">mdi-account-group-outline</v-icon>
                        <span>Susunan Pimpinan</span>
                    </button>

                    <button
                        type="button"
                        class="m3-tab-item"
                        :class="{ 'm3-tab-item--active': activeTab === 'sk' }"
                        role="tab"
                        :aria-selected="activeTab === 'sk'"
                        @click="activeTab = 'sk'"
                    >
                        <v-icon size="18" class="mr-2">mdi-file-document-outline</v-icon>
                        <span>Dasar SK Resmi</span>
                    </button>

                    <button
                        type="button"
                        class="m3-tab-item"
                        :class="{ 'm3-tab-item--active': activeTab === 'visimisi' }"
                        role="tab"
                        :aria-selected="activeTab === 'visimisi'"
                        @click="activeTab = 'visimisi'"
                    >
                        <v-icon size="18" class="mr-2">mdi-compass-rose</v-icon>
                        <span>Visi, Misi & Majelis</span>
                    </button>

                    <button
                        type="button"
                        class="m3-tab-item"
                        :class="{ 'm3-tab-item--active': activeTab === 'aum' }"
                        role="tab"
                        :aria-selected="activeTab === 'aum'"
                        @click="activeTab = 'aum'"
                    >
                        <v-icon size="18" class="mr-2">mdi-domain</v-icon>
                        <span>Amal Usaha & Ortom</span>
                    </button>
                </div>
            </div>

            <!-- ── TAB 1: Susunan Pimpinan ── -->
            <div v-show="activeTab === 'pimpinan'" class="m3-tab-pane">
                <!-- Ketua Card (Featured Tonal Card) -->
                <div class="m3-card m3-card--featured">
                    <div class="m3-card-body m3-ketua-layout">
                        <div class="m3-avatar m3-avatar--large">
                            <span>{{ getInitials(ketua.name) }}</span>
                        </div>
                        <div class="m3-ketua-info">
                            <div class="m3-role-pill">Ketua Pimpinan Cabang</div>
                            <h2 class="m3-name m3-name--large">{{ ketua.name }}</h2>
                            <p class="m3-meta-text">Pimpinan Cabang Muhammadiyah Simo Periode 2023 – 2028</p>
                            <p class="m3-caption-text">Ditetapkan berdasarkan Diktum Pertama SK PDM Boyolali No. 100/KEP/III.0/D/2023.</p>
                        </div>
                    </div>
                </div>

                <!-- Anggota Section Header & Search -->
                <div class="m3-section-bar">
                    <div>
                        <h3 class="m3-section-title">Anggota Pimpinan Cabang</h3>
                        <p class="m3-section-subtitle">8 personalia yang ditetapkan pada Diktum Kedua SK Penetapan</p>
                    </div>

                    <div class="m3-search-field">
                        <v-icon size="18" class="m3-search-icon">mdi-magnify</v-icon>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama anggota..."
                            class="m3-search-input"
                        />
                        <button
                            v-if="searchQuery"
                            type="button"
                            class="m3-search-clear"
                            @click="searchQuery = ''"
                            title="Hapus pencarian"
                        >
                            <v-icon size="16">mdi-close</v-icon>
                        </button>
                    </div>
                </div>

                <!-- Anggota Cards Grid (Outlined Cards) -->
                <div class="m3-grid-3">
                    <div
                        v-for="(agg, index) in anggotaList"
                        :key="agg.id || index"
                        class="m3-card m3-card--outlined"
                    >
                        <div class="m3-card-body m3-member-layout">
                            <div class="m3-avatar">
                                <span>{{ getInitials(agg.name) }}</span>
                            </div>
                            <div class="m3-member-info">
                                <h4 class="m3-name">{{ agg.name }}</h4>
                                <p class="m3-member-role">Anggota Pimpinan Cabang</p>
                                <p class="m3-meta-sub">Masa Jabatan 2023 – 2028</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="anggotaList.length === 0" class="m3-empty-state">
                    <v-icon size="36" color="#94a3b8">mdi-account-search-outline</v-icon>
                    <p>Tidak ditemukan anggota dengan nama "{{ searchQuery }}"</p>
                </div>
            </div>

            <!-- ── TAB 2: Dasar Surat Keputusan (SK) ── -->
            <div v-show="activeTab === 'sk'" class="m3-tab-pane">
                <div class="m3-card m3-card--outlined">
                    <div class="m3-card-body">
                        <div class="m3-sk-header">
                            <div class="m3-sk-meta">
                                <span class="m3-badge">Surat Keputusan Resmi</span>
                                <h2 class="m3-headline-sk">Pimpinan Daerah Muhammadiyah Boyolali</h2>
                                <p class="m3-sk-num">Nomor: <b>{{ skInfo.nomor }}</b></p>
                                <p class="m3-sk-about">Tentang: {{ skInfo.tentang }}</p>
                            </div>
                            <button type="button" class="m3-btn-filled" @click="showSkDialog = true">
                                <v-icon size="18" class="mr-2">mdi-eye-outline</v-icon>
                                <span>Baca Naskah Lengkap</span>
                            </button>
                        </div>

                        <hr class="m3-divider" />

                        <!-- Key Metadata List -->
                        <div class="m3-meta-grid">
                            <div class="m3-meta-item">
                                <span class="m3-meta-label">Tanggal Penetapan</span>
                                <span class="m3-meta-val">{{ skInfo.tanggal_hijriyah }} / {{ skInfo.tanggal_masehi }}</span>
                            </div>
                            <div class="m3-meta-item">
                                <span class="m3-meta-label">Penerbit SK</span>
                                <span class="m3-meta-val">{{ skInfo.penerbit }}</span>
                            </div>
                            <div class="m3-meta-item">
                                <span class="m3-meta-label">Ketua PDM Boyolali</span>
                                <span class="m3-meta-val">{{ skInfo.ketua_pdm }} (NBM. {{ skInfo.nbm_ketua }})</span>
                            </div>
                            <div class="m3-meta-item">
                                <span class="m3-meta-label">Sekretaris PDM Boyolali</span>
                                <span class="m3-meta-val">{{ skInfo.sekretaris_pdm }} (NBM. {{ skInfo.nbm_sekretaris }})</span>
                            </div>
                        </div>

                        <!-- Table of Officials Appointed -->
                        <div class="m3-table-wrapper">
                            <table class="m3-table">
                                <thead>
                                    <tr>
                                        <th class="w-12 text-center">No</th>
                                        <th>Nama Personalia</th>
                                        <th>Jabatan Ditetapkan</th>
                                        <th>Dasar Diktum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="m3-table-highlight">
                                        <td class="text-center font-bold">1</td>
                                        <td class="font-bold text-slate-900">{{ ketua.name }}</td>
                                        <td><span class="m3-role-tag">Ketua PCM</span></td>
                                        <td class="text-slate-500 text-xs">Diktum Pertama</td>
                                    </tr>
                                    <tr
                                        v-for="(agg, i) in props.officials.filter(o => o.sort_order !== 1)"
                                        :key="agg.id || i"
                                    >
                                        <td class="text-center text-slate-500">{{ i + 2 }}</td>
                                        <td class="font-medium text-slate-800">{{ agg.name }}</td>
                                        <td><span class="m3-role-tag m3-role-tag--sub">Anggota</span></td>
                                        <td class="text-slate-500 text-xs">Diktum Kedua (No. {{ i + 1 }})</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── TAB 3: Visi, Misi & Majelis ── -->
            <div v-show="activeTab === 'visimisi'" class="m3-tab-pane">
                <!-- Visi & Misi Cards -->
                <div class="m3-grid-2 mb-6">
                    <div class="m3-card m3-card--tonal">
                        <div class="m3-card-body">
                            <div class="m3-card-icon-title">
                                <v-icon size="22" class="text-emerald-800 mr-2">mdi-bullseye-arrow</v-icon>
                                <h3 class="m3-title">Visi Persyarikatan</h3>
                            </div>
                            <p class="m3-body-text">
                                Terwujudnya masyarakat Islam yang sebenar-benarnya di wilayah Kecamatan Simo yang berakhlak mulia,
                                mandiri, unggul dalam pendidikan dan kesehatan, serta menjadi teladan rahmatan lil 'alamin.
                            </p>
                        </div>
                    </div>

                    <div class="m3-card m3-card--tonal">
                        <div class="m3-card-body">
                            <div class="m3-card-icon-title">
                                <v-icon size="22" class="text-emerald-800 mr-2">mdi-format-list-checks</v-icon>
                                <h3 class="m3-title">Misi Utama</h3>
                            </div>
                            <ul class="m3-list">
                                <li>Meneguhkan dakwah Tarjih & Tabligh berlandaskan Al-Qur'an dan Sunnah Shohihah.</li>
                                <li>Meningkatkan tata kelola pendidikan dan Amal Usaha Muhammadiyah (AUM) bermutu.</li>
                                <li>Menguatkan kepedulian sosial melalui Klinik PKU Muhammadiyah dan Lazismu.</li>
                                <li>Memberdayakan pimpinan ranting dan kaderisasi Ortom secara berkelanjutan.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Majelis & Lembaga -->
                <div class="m3-section-bar">
                    <div>
                        <h3 class="m3-section-title">Majelis & Lembaga Pembantu Pimpinan</h3>
                        <p class="m3-section-subtitle">Unsur pembantu pelaksana program kerja PCM Simo</p>
                    </div>
                </div>

                <div class="m3-grid-3">
                    <div
                        v-for="(maj, idx) in majelisList"
                        :key="maj.id || idx"
                        class="m3-card m3-card--outlined"
                    >
                        <div class="m3-card-body">
                            <div class="m3-majelis-header">
                                <v-icon size="24" class="text-emerald-800 mr-3">{{ maj.icon }}</v-icon>
                                <h4 class="m3-majelis-title">{{ maj.name }}</h4>
                            </div>
                            <p class="m3-majelis-desc">{{ maj.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── TAB 4: Amal Usaha & Ortom ── -->
            <div v-show="activeTab === 'aum'" class="m3-tab-pane">
                <!-- Amal Usaha Section -->
                <div class="m3-section-bar">
                    <div>
                        <h3 class="m3-section-title">Amal Usaha Muhammadiyah (AUM) di Simo</h3>
                        <p class="m3-section-subtitle">Layanan persyarikatan di bidang pendidikan, kesehatan, dan dakwah</p>
                    </div>
                </div>

                <div class="m3-grid-3 mb-8">
                    <div
                        v-for="(aumItem, index) in amalUsahaList"
                        :key="aumItem.id || index"
                        class="m3-card m3-card--outlined"
                    >
                        <div class="m3-card-body">
                            <span class="m3-aum-category">{{ aumItem.category }}</span>
                            <h4 class="m3-aum-name">{{ aumItem.name }}</h4>
                            <p v-if="aumItem.address" class="m3-aum-meta">
                                <v-icon size="14" class="mr-1 text-slate-400">mdi-map-marker-outline</v-icon>
                                <span>{{ aumItem.address }}</span>
                            </p>
                            <p v-if="aumItem.leader" class="m3-aum-meta">
                                <v-icon size="14" class="mr-1 text-slate-400">mdi-account-tie-outline</v-icon>
                                <span>{{ aumItem.leader }}</span>
                            </p>
                            <p v-if="aumItem.description" class="m3-aum-desc">{{ aumItem.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Masjid Section -->
                <template v-if="masjidList.length">
                    <div class="m3-section-bar">
                        <div>
                            <h3 class="m3-section-title">Masjid & Musholla Muhammadiyah</h3>
                            <p class="m3-section-subtitle">Pusat ibadah, kajian, dan dakwah jamaah persyarikatan</p>
                        </div>
                    </div>

                    <div class="m3-grid-3 mb-8">
                        <div
                            v-for="(msj, index) in masjidList"
                            :key="msj.id || index"
                            class="m3-card m3-card--outlined"
                        >
                            <div class="m3-card-body">
                                <div class="m3-majelis-header">
                                    <v-icon size="24" class="text-emerald-800 mr-3">{{ msj.icon }}</v-icon>
                                    <h4 class="m3-majelis-title">{{ msj.name }}</h4>
                                </div>
                                <p v-if="msj.address" class="m3-aum-meta">
                                    <v-icon size="14" class="mr-1 text-slate-400">mdi-map-marker-outline</v-icon>
                                    <span>{{ msj.address }}</span>
                                </p>
                                <p v-if="msj.desc" class="m3-majelis-desc">{{ msj.desc }}</p>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Ortom Section -->
                <div class="m3-section-bar">
                    <div>
                        <h3 class="m3-section-title">Organisasi Otonom (Ortom)</h3>
                        <p class="m3-section-subtitle">Sayap pergerakan dan kaderisasi persyarikatan tingkat cabang</p>
                    </div>
                </div>

                <div class="m3-grid-3">
                    <div
                        v-for="(ort, i) in ortomList"
                        :key="ort.id || i"
                        class="m3-card m3-card--outlined"
                    >
                        <div class="m3-card-body">
                            <div class="m3-majelis-header">
                                <v-icon size="24" class="text-emerald-800 mr-3">{{ ort.icon }}</v-icon>
                                <h4 class="m3-majelis-title">{{ ort.name }}</h4>
                            </div>
                            <p class="m3-majelis-desc">{{ ort.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Sekretariat Card ── -->
            <div class="m3-card m3-card--outlined mt-8">
                <div class="m3-card-body m3-office-layout">
                    <div class="m3-office-info">
                        <span class="m3-role-pill">Sekretariat PCM Simo</span>
                        <h3 class="m3-name m3-name--large">Gedung Dakwah Muhammadiyah Simo</h3>
                        <p class="m3-meta-text">
                            Jl. Raya Simo - Bangak Km. 1, Simo, Kabupaten Boyolali, Jawa Tengah 57377
                        </p>
                        <p class="m3-caption-text">
                            Pelayanan persyarikatan, administrasi, dan koordinasi jamaah setiap hari kerja Senin – Sabtu pukul 08.00 – 16.00 WIB.
                        </p>
                    </div>
                    <div class="m3-office-action">
                        <a
                            href="https://maps.google.com/?q=Gedung+Dakwah+Muhammadiyah+Simo"
                            target="_blank"
                            class="m3-btn-outlined"
                        >
                            <v-icon size="18" class="mr-2">mdi-map-marker-outline</v-icon>
                            <span>Buka di Google Maps</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── MD3 Dialog: Naskah SK ── -->
        <transition name="dialog-fade">
            <div v-if="showSkDialog" class="m3-dialog-backdrop" @click.self="showSkDialog = false">
                <div class="m3-dialog">
                    <div class="m3-dialog-header">
                        <div class="m3-dialog-title">
                            <v-icon size="20" class="mr-2 text-emerald-800">mdi-file-certificate-outline</v-icon>
                            <span>Surat Keputusan PDM Boyolali</span>
                        </div>
                        <button type="button" class="m3-dialog-close" @click="showSkDialog = false">
                            <v-icon size="20">mdi-close</v-icon>
                        </button>
                    </div>

                    <div class="m3-dialog-body">
                        <!-- Official Letterhead Details -->
                        <div class="text-center pb-4 mb-4 border-b border-slate-200">
                            <h3 class="text-sm font-bold text-slate-800">PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN BOYOLALI</h3>
                            <p class="text-xs text-slate-500">Jl. Pandanaran No. 68 Tegalsari Siswodipuran Boyolali Telp. 0276 - 324279</p>
                            <div class="mt-3 text-xs">
                                <p class="font-bold underline text-slate-900">SURAT KEPUTUSAN PIMPINAN DAERAH MUHAMMADIYAH BOYOLALI</p>
                                <p class="text-slate-600">Nomor: {{ skInfo.nomor }}</p>
                                <p class="font-semibold text-emerald-800 mt-1">Tentang: {{ skInfo.tentang.toUpperCase() }}</p>
                            </div>
                        </div>

                        <div class="text-xs text-slate-700 space-y-3 leading-relaxed">
                            <p><b>Memperhatikan:</b> {{ skInfo.surat_permohonan }} perihal permohonan SK Penetapan.</p>
                            <p><b>Menimbang:</b> Bahwa untuk ketertiban persyarikatan perlu segera menetapkan Pimpinan Cabang Muhammadiyah Simo Periode 2023 - 2028.</p>
                            <p><b>Mengingat:</b> Anggaran Dasar Muhammadiyah Pasal 13 & 26 serta Keputusan Rapat PDM Boyolali tanggal 23 Oktober 2023.</p>

                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 my-2">
                                <p class="font-bold text-slate-900 mb-1">MEMUTUSKAN / MENETAPKAN:</p>
                                <p><b>Pertama:</b> Mengangkat <b>{{ ketua.name }}</b> sebagai Ketua PCM Kecamatan Simo Masa Jabatan 2023 – 2028.</p>
                                <p class="mt-1"><b>Kedua:</b> Mengangkat Anggota Pimpinan Cabang Muhammadiyah Simo 2023 – 2028:</p>
                                <ol class="list-decimal list-inside mt-1 space-y-0.5 font-medium text-slate-800 pl-2">
                                    <li v-for="off in props.officials.filter(o => o.sort_order !== 1)" :key="off.id">
                                        {{ off.name }}
                                    </li>
                                </ol>
                            </div>

                            <p><b>Ketiga:</b> Keputusan ini berlaku mulai tanggal ditetapkan (30 Oktober 2023 M / 15 Rabiul Akhir 1445 H) sampai dengan akhir periode jabatan 2028.</p>
                        </div>

                        <!-- Signatures -->
                        <div class="mt-6 pt-4 border-t border-slate-200 grid grid-cols-2 text-center text-xs">
                            <div>
                                <p class="font-bold text-slate-800">Ketua,</p>
                                <div class="py-4 text-emerald-800 italic">[Tertanda & Tercap]</div>
                                <p class="font-bold underline text-slate-900">{{ skInfo.ketua_pdm }}</p>
                                <p class="text-slate-500">NBM. {{ skInfo.nbm_ketua }}</p>
                            </div>
                            <div>
                                <p class="font-bold text-slate-800">Sekretaris,</p>
                                <div class="py-4 text-emerald-800 italic">[Tertanda]</div>
                                <p class="font-bold underline text-slate-900">{{ skInfo.sekretaris_pdm }}</p>
                                <p class="text-slate-500">NBM. {{ skInfo.nbm_sekretaris }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="m3-dialog-footer">
                        <button type="button" class="m3-btn-tonal" @click="showSkDialog = false">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </PublicLayout>
</template>

<style scoped>
/* ── Material Design 3 Design System (Clean, Honest, Authentic) ── */

.m3-container {
    max-width: 1120px;
    margin: 0 auto;
    padding: 32px 20px 64px;
}

/* ── 1. Page Header ── */
.m3-page-header {
    margin-bottom: 28px;
}

.m3-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    color: #475569;
    margin-bottom: 16px;
}

.m3-breadcrumb-link {
    color: #006837;
    text-decoration: none;
    font-weight: 500;
}

.m3-breadcrumb-link:hover {
    text-decoration: underline;
}

.m3-breadcrumb-sep {
    color: #94a3b8;
}

.m3-breadcrumb-current {
    color: #1e293b;
    font-weight: 600;
}

.m3-header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 24px;
    margin-bottom: 24px;
}

.m3-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #006837;
    background-color: #e8f5e9;
    padding: 4px 10px;
    border-radius: 6px;
    margin-bottom: 8px;
    letter-spacing: 0.02em;
}

.m3-headline {
    font-size: 2rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.25;
    letter-spacing: -0.02em;
    margin-bottom: 6px;
}

.m3-subhead {
    font-size: 0.9375rem;
    color: #475569;
    line-height: 1.5;
    max-width: 680px;
}

/* ── 2. MD3 Segmented Tab Bar ── */
.m3-tab-bar {
    display: flex;
    gap: 8px;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 2px;
    overflow-x: auto;
}

.m3-tab-item {
    display: inline-flex;
    align-items: center;
    padding: 10px 18px;
    font-size: 0.875rem;
    font-weight: 600;
    color: #64748b;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.m3-tab-item:hover {
    color: #006837;
    background-color: #f8fafc;
    border-radius: 8px 8px 0 0;
}

.m3-tab-item--active {
    color: #006837;
    border-bottom-color: #006837;
    background-color: #f1f8f3;
    border-radius: 8px 8px 0 0;
}

/* ── 3. Cards (MD3 Outlined & Tonal) ── */
.m3-card {
    background-color: #ffffff;
    border-radius: 16px;
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

.m3-card--outlined {
    border: 1px solid #e2e8f0;
}

.m3-card--outlined:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.m3-card--featured {
    background-color: #f0fdf4;
    border: 1px solid #bbf7d0;
    margin-bottom: 24px;
}

.m3-card--tonal {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
}

.m3-card-body {
    padding: 24px;
}

/* ── 4. Layouts Inside Cards ── */
.m3-ketua-layout {
    display: flex;
    align-items: center;
    gap: 24px;
}

.m3-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background-color: #006837;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.125rem;
    flex-shrink: 0;
}

.m3-avatar--large {
    width: 72px;
    height: 72px;
    font-size: 1.5rem;
    background-color: #006837;
}

.m3-role-pill {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 700;
    color: #006837;
    background-color: #dcfce7;
    padding: 2px 8px;
    border-radius: 4px;
    margin-bottom: 4px;
}

.m3-name {
    font-size: 1.125rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
}

.m3-name--large {
    font-size: 1.375rem;
}

.m3-meta-text {
    font-size: 0.875rem;
    color: #475569;
    margin-top: 2px;
}

.m3-caption-text {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 6px;
}

/* Member layout */
.m3-member-layout {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
}

.m3-member-info {
    flex: 1;
    min-width: 0;
}

.m3-member-role {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #006837;
    margin-top: 2px;
}

.m3-meta-sub {
    font-size: 0.75rem;
    color: #64748b;
}

/* ── 5. Grids ── */
.m3-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.m3-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

/* ── 6. Section Bar & Search ── */
.m3-section-bar {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin: 28px 0 16px;
    flex-wrap: wrap;
    gap: 12px;
}

.m3-section-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #0f172a;
}

.m3-section-subtitle {
    font-size: 0.8125rem;
    color: #64748b;
    margin-top: 2px;
}

.m3-search-field {
    position: relative;
    display: flex;
    align-items: center;
}

.m3-search-icon {
    position: absolute;
    left: 12px;
    color: #94a3b8;
}

.m3-search-input {
    padding: 8px 32px 8px 36px;
    border: 1px solid #cbd5e1;
    border-radius: 20px;
    font-size: 0.8125rem;
    width: 220px;
    background-color: #ffffff;
    outline: none;
    transition: all 0.2s ease;
}

.m3-search-input:focus {
    border-color: #006837;
    box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.1);
    width: 260px;
}

.m3-search-clear {
    position: absolute;
    right: 10px;
    border: none;
    background: transparent;
    color: #94a3b8;
    cursor: pointer;
}

/* Empty state */
.m3-empty-state {
    text-align: center;
    padding: 48px 20px;
    color: #64748b;
    font-size: 0.875rem;
}

/* ── 7. Buttons (MD3 Standard) ── */
.m3-btn-filled {
    display: inline-flex;
    align-items: center;
    background-color: #006837;
    color: #ffffff;
    font-size: 0.875rem;
    font-weight: 600;
    padding: 10px 20px;
    border-radius: 20px;
    border: none;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.m3-btn-filled:hover {
    background-color: #047857;
}

.m3-btn-tonal {
    display: inline-flex;
    align-items: center;
    background-color: #e8f5e9;
    color: #006837;
    font-size: 0.875rem;
    font-weight: 600;
    padding: 8px 18px;
    border-radius: 20px;
    border: 1px solid #c8e6c9;
    cursor: pointer;
    transition: all 0.2s ease;
}

.m3-btn-tonal:hover {
    background-color: #c8e6c9;
}

.m3-btn-outlined {
    display: inline-flex;
    align-items: center;
    background-color: #ffffff;
    color: #006837;
    font-size: 0.875rem;
    font-weight: 600;
    padding: 8px 18px;
    border-radius: 20px;
    border: 1px solid #cbd5e1;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.m3-btn-outlined:hover {
    background-color: #f8fafc;
    border-color: #006837;
}

/* ── 8. SK Tab Details ── */
.m3-sk-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
}

.m3-headline-sk {
    font-size: 1.375rem;
    font-weight: 700;
    color: #0f172a;
    margin: 4px 0 2px;
}

.m3-sk-num {
    font-size: 0.9375rem;
    color: #006837;
}

.m3-sk-about {
    font-size: 0.875rem;
    color: #475569;
    margin-top: 2px;
}

.m3-divider {
    border: 0;
    border-top: 1px solid #e2e8f0;
    margin: 20px 0;
}

.m3-meta-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.m3-meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.m3-meta-label {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 500;
}

.m3-meta-val {
    font-size: 0.875rem;
    color: #0f172a;
    font-weight: 600;
}

/* ── 9. Clean MD3 Table ── */
.m3-table-wrapper {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.m3-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
    text-align: left;
}

.m3-table th {
    background-color: #f8fafc;
    color: #475569;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
}

.m3-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
}

.m3-table tbody tr:last-child td {
    border-bottom: none;
}

.m3-table-highlight {
    background-color: #f0fdf4;
}

.m3-role-tag {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #006837;
    background-color: #dcfce7;
    padding: 2px 8px;
    border-radius: 4px;
}

.m3-role-tag--sub {
    color: #475569;
    background-color: #f1f5f9;
}

/* ── 10. Visi, Misi & Majelis ── */
.m3-card-icon-title {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.m3-title {
    font-size: 1.0625rem;
    font-weight: 700;
    color: #0f172a;
}

.m3-body-text {
    font-size: 0.875rem;
    color: #334155;
    line-height: 1.6;
}

.m3-list {
    font-size: 0.875rem;
    color: #334155;
    line-height: 1.6;
    padding-left: 20px;
    list-style-type: disc;
}

.m3-list li {
    margin-bottom: 6px;
}

.m3-majelis-header {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.m3-majelis-title {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #0f172a;
}

.m3-majelis-desc {
    font-size: 0.8125rem;
    color: #475569;
    line-height: 1.5;
}

/* ── 11. AUM Cards ── */
.m3-aum-category {
    font-size: 0.6875rem;
    font-weight: 700;
    color: #006837;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: inline-block;
    margin-bottom: 4px;
}

.m3-aum-name {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 4px;
}

.m3-aum-meta {
    font-size: 0.75rem;
    color: #64748b;
    display: flex;
    align-items: center;
    margin-bottom: 6px;
}

.m3-aum-desc {
    font-size: 0.8125rem;
    color: #475569;
    line-height: 1.5;
}

/* ── 12. Office Layout ── */
.m3-office-layout {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
}

/* ── 13. Dialog (MD3 Modal) ── */
.m3-dialog-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background-color: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.m3-dialog {
    background-color: #ffffff;
    width: 100%;
    max-width: 640px;
    max-height: 85vh;
    border-radius: 20px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.m3-dialog-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
}

.m3-dialog-title {
    display: flex;
    align-items: center;
    font-size: 0.9375rem;
    font-weight: 700;
    color: #0f172a;
}

.m3-dialog-close {
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    border-radius: 50%;
    padding: 4px;
}

.m3-dialog-close:hover {
    background-color: #f1f5f9;
    color: #0f172a;
}

.m3-dialog-body {
    padding: 20px;
    overflow-y: auto;
}

.m3-dialog-footer {
    padding: 12px 20px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
}

.dialog-fade-enter-active,
.dialog-fade-leave-active {
    transition: opacity 0.2s ease;
}

.dialog-fade-enter-from,
.dialog-fade-leave-to {
    opacity: 0;
}

/* ── Responsive Queries ── */
@media (max-width: 1024px) {
    .m3-grid-3 {
        grid-template-columns: repeat(2, 1fr);
    }
    .m3-meta-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .m3-container {
        padding: 20px 16px 40px;
    }
    .m3-header-content {
        flex-direction: column;
        gap: 16px;
    }
    .m3-headline {
        font-size: 1.5rem;
    }
    .m3-ketua-layout {
        flex-direction: column;
        text-align: center;
    }
    .m3-grid-3 {
        grid-template-columns: 1fr;
    }
    .m3-grid-2 {
        grid-template-columns: 1fr;
    }
    .m3-sk-header {
        flex-direction: column;
    }
    .m3-meta-grid {
        grid-template-columns: 1fr;
    }
    .m3-office-layout {
        flex-direction: column;
        text-align: center;
    }
    .m3-section-bar {
        flex-direction: column;
        align-items: flex-start;
    }
    .m3-search-input {
        width: 100%;
    }
}
</style>
