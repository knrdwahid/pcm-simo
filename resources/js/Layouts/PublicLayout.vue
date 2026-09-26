<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    newsflashItems: { type: Array, default: () => [] },
});

// Real-Time Indonesian Date
const currentDate = ref('');
const updateDate = () => {
    const now = new Date();
    currentDate.value = now.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

// Mobile Drawer
const mobileMenuOpen = ref(false);
const organisasiDropdownOpen = ref(false);

// Search Modal
const searchModalOpen = ref(false);
const searchQuery = ref('');
const handleSearch = () => {
    if (searchQuery.value.trim()) {
        router.get('/', { cari: searchQuery.value }, { preserveState: true });
        searchModalOpen.value = false;
    }
};

// Newsflash rotation
const currentFlashIndex = ref(0);
const defaultNewsflash = [
    'Musycab PCM Simo Meneguhkan Dakwah Pencerahan Menuju Simo Berkemajuan',
    'SD Muhammadiyah Simo Raih Juara Umum Olimpiade Sains & Al-Qur’an Kabupaten Boyolali',
    'Kajian Rutin Ahad Pagi Masjid At-Taqwa Simo: Fiqih Ibadah Praktis Sesuai HPT',
    'Klinik Pratama PKU Muhammadiyah Simo Buka Layanan Fisioterapi & USG 4D',
];

const activeNewsflash = ref(props.newsflashItems?.length ? props.newsflashItems : defaultNewsflash);

const nextNewsflash = () => {
    currentFlashIndex.value = (currentFlashIndex.value + 1) % activeNewsflash.value.length;
};

const prevNewsflash = () => {
    currentFlashIndex.value =
        (currentFlashIndex.value - 1 + activeNewsflash.value.length) % activeNewsflash.value.length;
};

let flashTimer = null;
onMounted(() => {
    updateDate();
    flashTimer = setInterval(nextNewsflash, 5000);
});

onUnmounted(() => {
    if (flashTimer) clearInterval(flashTimer);
});
</script>

<template>
    <div class="public-wrapper font-poppins">
        <!-- ── 1. Top Bar (Deep Navy) ── -->
        <div class="topbar-navy">
            <div class="topbar-container">
                <!-- Date Left -->
                <div class="topbar-date">
                    <v-icon size="14" class="mr-1 text-slate-300">mdi-calendar-today</v-icon>
                    <span>{{ currentDate || 'Selasa, 22 September 2026' }}</span>
                </div>

                <!-- Right Links & Socials -->
                <div class="topbar-right">
                    <div class="social-icons">
                        <a href="https://facebook.com" target="_blank" class="social-link" title="Facebook">
                            <v-icon size="14">mdi-facebook</v-icon>
                        </a>
                        <a href="https://twitter.com" target="_blank" class="social-link" title="Twitter / X">
                            <v-icon size="14">mdi-twitter</v-icon>
                        </a>
                        <a href="https://instagram.com" target="_blank" class="social-link" title="Instagram">
                            <v-icon size="14">mdi-instagram</v-icon>
                        </a>
                        <a href="https://youtube.com" target="_blank" class="social-link" title="YouTube">
                            <v-icon size="14">mdi-youtube</v-icon>
                        </a>
                    </div>

                    <span class="topbar-sep">|</span>

                    <Link href="/dashboard/login" class="topbar-admin-link">
                        <v-icon size="13" class="mr-1">mdi-lock-outline</v-icon>
                        <span>Portal Pengurus</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- ── 2. Main Navbar Bar (Navy Left + Green Right Split) ── -->
        <header class="main-header">
            <div class="header-inner">
                <!-- Left: Brand Logo on Navy -->
                <div class="header-brand-navy">
                    <Link href="/" class="brand-link">
                        <img
                            src="/images/logo-pcmsimo-white.png"
                            alt="PCM Simo"
                            class="brand-logo-img"
                        />
                    </Link>
                </div>

                <!-- Right: Nav Menu on Green -->
                <nav class="header-nav-green">
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <Link href="/" class="nav-link nav-link--active">BERANDA</Link>
                        </li>

                        <!-- Organisasi Dropdown -->
                        <li
                            class="nav-item nav-item--has-dropdown"
                            @mouseenter="organisasiDropdownOpen = true"
                            @mouseleave="organisasiDropdownOpen = false"
                        >
                            <a href="#organisasi" class="nav-link" @click.prevent>
                                <span>ORGANISASI</span>
                                <v-icon size="14" class="ml-0.5">mdi-chevron-down</v-icon>
                            </a>
                            <!-- Dropdown Menu -->
                            <transition name="fade">
                                <ul v-if="organisasiDropdownOpen" class="dropdown-menu">
                                    <li><a href="#pimpinan" class="dropdown-link">Pimpinan Cabang</a></li>
                                    <li><a href="#majelis" class="dropdown-link">Majelis & Lembaga</a></li>
                                    <li><a href="#ortom" class="dropdown-link">Organisasi Otonom (Ortom)</a></li>
                                    <li><a href="#ranting" class="dropdown-link">Pimpinan Ranting (PRM)</a></li>
                                </ul>
                            </transition>
                        </li>

                        <li class="nav-item">
                            <a href="#berita" class="nav-link">BERITA</a>
                        </li>
                        <li class="nav-item">
                            <a href="#khazanah" class="nav-link">KHAZANAH ISLAM</a>
                        </li>
                        <li class="nav-item">
                            <a href="#amal-usaha" class="nav-link">AMAL USAHA</a>
                        </li>
                        <li class="nav-item">
                            <a href="#agenda" class="nav-link">AGENDA</a>
                        </li>
                        <li class="nav-item">
                            <a href="#layanan" class="nav-link">LAYANAN</a>
                        </li>

                        <!-- Search Button -->
                        <li class="nav-item nav-item--search">
                            <button
                                type="button"
                                class="btn-search-trigger"
                                @click="searchModalOpen = true"
                                title="Cari Berita & Informasi"
                            >
                                <v-icon size="17">mdi-magnify</v-icon>
                            </button>
                        </li>
                    </ul>

                    <!-- Mobile Burger Button -->
                    <button
                        type="button"
                        class="mobile-burger-btn"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        aria-label="Buka Menu"
                    >
                        <v-icon size="24">{{ mobileMenuOpen ? 'mdi-close' : 'mdi-menu' }}</v-icon>
                    </button>
                </nav>
            </div>

            <!-- Mobile Drawer Menu -->
            <transition name="slide-down">
                <div v-if="mobileMenuOpen" class="mobile-drawer">
                    <ul class="mobile-menu-list">
                        <li><Link href="/" class="mobile-nav-link" @click="mobileMenuOpen = false">BERANDA</Link></li>
                        <li><a href="#organisasi" class="mobile-nav-link" @click="mobileMenuOpen = false">ORGANISASI</a></li>
                        <li><a href="#berita" class="mobile-nav-link" @click="mobileMenuOpen = false">BERITA</a></li>
                        <li><a href="#khazanah" class="mobile-nav-link" @click="mobileMenuOpen = false">KHAZANAH ISLAM</a></li>
                        <li><a href="#amal-usaha" class="mobile-nav-link" @click="mobileMenuOpen = false">AMAL USAHA</a></li>
                        <li><a href="#agenda" class="mobile-nav-link" @click="mobileMenuOpen = false">AGENDA</a></li>
                        <li><a href="#layanan" class="mobile-nav-link" @click="mobileMenuOpen = false">LAYANAN</a></li>
                        <li><Link href="/dashboard/login" class="mobile-nav-link mobile-nav-admin" @click="mobileMenuOpen = false">PORTAL PENGURUS</Link></li>
                    </ul>
                </div>
            </transition>
        </header>

        <!-- ── 3. Newsflash / Running Ticker Bar ── -->
        <div class="newsflash-bar">
            <div class="newsflash-container">
                <div class="newsflash-badge">
                    <v-icon size="14" class="mr-1">mdi-flash</v-icon>
                    <span>NEWSFLASH</span>
                </div>

                <div class="newsflash-content">
                    <span class="newsflash-text">
                        {{ activeNewsflash[currentFlashIndex] }}
                    </span>
                </div>

                <div class="newsflash-controls">
                    <button type="button" class="flash-btn" @click="prevNewsflash" title="Sebelumnya">
                        <v-icon size="16">mdi-chevron-left</v-icon>
                    </button>
                    <button type="button" class="flash-btn" @click="nextNewsflash" title="Berikutnya">
                        <v-icon size="16">mdi-chevron-right</v-icon>
                    </button>
                </div>
            </div>
        </div>

        <!-- ── 4. Main Page Content Slot ── -->
        <main class="main-page-content">
            <slot />
        </main>

        <!-- ── 5. Upper Directory Footer (White) ── -->
        <section class="upper-footer">
            <div class="upper-footer-container">
                <!-- Column 1: Ortom -->
                <div class="footer-dir-col">
                    <h3 class="dir-title">Organisasi Otonom</h3>
                    <ul class="dir-list">
                        <li><a href="#" class="dir-link">Pimpinan Cabang 'Aisyiyah Simo</a></li>
                        <li><a href="#" class="dir-link">Pemuda Muhammadiyah Simo</a></li>
                        <li><a href="#" class="dir-link">Nasyiatul 'Aisyiyah Cabang Simo</a></li>
                        <li><a href="#" class="dir-link">Ikatan Pelajar Muhammadiyah (IPM)</a></li>
                        <li><a href="#" class="dir-link">Kwartir Cabang Hizbul Wathan</a></li>
                        <li><a href="#" class="dir-link">Tapak Suci Putera Muhammadiyah</a></li>
                    </ul>
                </div>

                <!-- Column 2: Majelis Cabang -->
                <div class="footer-dir-col">
                    <h3 class="dir-title">Majelis Cabang</h3>
                    <ul class="dir-list">
                        <li><a href="#" class="dir-link">Majelis Tabligh & Dakwah Khusus</a></li>
                        <li><a href="#" class="dir-link">Majelis Tarjih dan Tajdid</a></li>
                        <li><a href="#" class="dir-link">Majelis Dikdasmen & PNF</a></li>
                        <li><a href="#" class="dir-link">Majelis Pembina Kesehatan Umum (MPKU)</a></li>
                        <li><a href="#" class="dir-link">Majelis Ekonomi & Ketenagakerjaan</a></li>
                        <li><a href="#" class="dir-link">Majelis Pemberdayaan Masyarakat (MPM)</a></li>
                    </ul>
                </div>

                <!-- Column 3: Lembaga & AUM -->
                <div class="footer-dir-col">
                    <h3 class="dir-title">Lembaga & AUM Cabang</h3>
                    <ul class="dir-list">
                        <li><a href="#" class="dir-link">LAZISMU Kantor Layanan Simo</a></li>
                        <li><a href="#" class="dir-link">Lembaga Resiliensi Bencana (MDMC)</a></li>
                        <li><a href="#" class="dir-link">Klinik Pratama PKU Muhammadiyah</a></li>
                        <li><a href="#" class="dir-link">SD Muhammadiyah Program Khusus</a></li>
                        <li><a href="#" class="dir-link">SMP Muhammadiyah 6 Simo</a></li>
                        <li><a href="#" class="dir-link">Masjid Besar At-Taqwa Muhammadiyah</a></li>
                    </ul>
                </div>

                <!-- Column 4: PRM -->
                <div class="footer-dir-col">
                    <h3 class="dir-title">Pimpinan Ranting (PRM)</h3>
                    <ul class="dir-list">
                        <li><a href="#" class="dir-link">PRM Simo Kota & PRM Pelem</a></li>
                        <li><a href="#" class="dir-link">PRM Bendungan & PRM Sumber</a></li>
                        <li><a href="#" class="dir-link">PRM Walen & PRM Pentur</a></li>
                        <li><a href="#" class="dir-link">PRM Kedunglengkong & PRM Blagung</a></li>
                        <li><a href="#" class="dir-link">PRM Temon & PRM Wates</a></li>
                        <li><a href="#" class="dir-link">PRM Batur & PRM Gunung</a></li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- ── 6. Signature Main Footer (Navy & Green Split) ── -->
        <footer class="main-footer-split">
            <div class="footer-split-inner">
                <!-- Left: Navy Brand & Info -->
                <div class="footer-brand-navy">
                    <Link href="/" class="footer-brand-link">
                        <img
                            src="/images/logo-pcmsimo-square-white.png"
                            alt="PCM Simo"
                            class="footer-square-logo"
                        />
                    </Link>

                    <!-- Address & Phone Info -->
                    <div class="footer-contact-info">
                        <div class="footer-contact-item">
                            <span><b>Gedung Dakwah Muhammadiyah Simo</b><br>
                                  Jl. Raya Simo - Bangak Km. 1, Simo, Boyolali 57377<br>
                                  Telp. (0276) 3294404
                            </span>
                        </div>
                    </div>

                    <!-- Social Icons -->
                    <div class="footer-socials">
                        <a href="https://facebook.com" target="_blank" class="footer-soc-link" title="Facebook"><v-icon size="16">mdi-facebook</v-icon></a>
                        <a href="https://twitter.com" target="_blank" class="footer-soc-link" title="Twitter / X"><v-icon size="16">mdi-twitter</v-icon></a>
                        <a href="https://instagram.com" target="_blank" class="footer-soc-link" title="Instagram"><v-icon size="16">mdi-instagram</v-icon></a>
                        <a href="https://youtube.com" target="_blank" class="footer-soc-link" title="YouTube"><v-icon size="16">mdi-youtube</v-icon></a>
                    </div>
                </div>

                <!-- Right: Green 4 Columns -->
                <div class="footer-menu-green">
                    <div class="footer-col">
                        <h4 class="col-heading">Kategori</h4>
                        <ul class="col-links">
                            <li><a href="#">Kabar Cabang</a></li>
                            <li><a href="#">Khazanah Islam</a></li>
                            <li><a href="#">Kajian & Tarjih</a></li>
                            <li><a href="#">Tokoh & Opini</a></li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h4 class="col-heading">Tentang</h4>
                        <ul class="col-links">
                            <li><a href="#">Sejarah PCM Simo</a></li>
                            <li><a href="#">Profil Pimpinan</a></li>
                            <li><a href="#">Visi & Misi</a></li>
                            <li><a href="#">Amal Usaha</a></li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h4 class="col-heading">Layanan</h4>
                        <ul class="col-links">
                            <li><a href="#">Ambulans Gratis</a></li>
                            <li><a href="#">Baitul Arqam</a></li>
                            <li><a href="#">ZISWAF Lazismu</a></li>
                            <li><a href="#">Konsultasi Dakwah</a></li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h4 class="col-heading">Informasi</h4>
                        <ul class="col-links">
                            <li><a href="#">Kontak Sekretariat</a></li>
                            <li><a href="#">Gedung Dakwah</a></li>
                            <li><a href="#">Kotak Saran</a></li>
                            <li><Link href="/dashboard/login">Portal Admin</Link></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- ── 7. Copyright Bottom Bar ── -->
            <div class="footer-copyright-bar">
                <div class="copyright-container">
                    <span>&copy; 2026 Pimpinan Cabang Muhammadiyah Simo. Gerakan Islam Berkemajuan.</span>
                </div>
            </div>
        </footer>

        <!-- ── 8. Floating WhatsApp Contact Button ── -->
        <a
            href="https://wa.me/6281234567890?text=Assalamu%27alaikum%20PCM%20Simo"
            target="_blank"
            class="floating-whatsapp-btn"
            title="Hubungi Kami di WhatsApp"
        >
            <span class="whatsapp-label">Contact us</span>
            <div class="whatsapp-icon-circle">
                <v-icon size="20">mdi-whatsapp</v-icon>
            </div>
        </a>

        <!-- ── Search Modal ── -->
        <v-dialog v-model="searchModalOpen" max-width="500">
            <v-card rounded="2xl" class="pa-5">
                <div class="d-flex align-center justify-space-between mb-3">
                    <span class="text-sm font-bold text-slate-800">Cari Berita & Informasi</span>
                    <button type="button" @click="searchModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <v-icon size="18">mdi-close</v-icon>
                    </button>
                </div>
                <form @submit.prevent="handleSearch">
                    <div class="search-modal-box">
                        <v-icon size="18" class="text-slate-400 mr-2">mdi-magnify</v-icon>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Ketik kata kunci pencarian..."
                            class="search-modal-input"
                            autofocus
                        />
                        <button type="submit" class="btn-modal-search">
                            Cari
                        </button>
                    </div>
                </form>
            </v-card>
        </v-dialog>
    </div>
</template>

<style scoped>
.public-wrapper {
    background-color: #ffffff;
    color: #1e293b;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ── 1. Top Bar ── */
.topbar-navy {
    background: #0b224d;
    color: #cbd5e1;
    font-size: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.copyright-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 16px 24px;
    text-align: center;
    color: rgba(255, 255, 255, 0.7);
    font-size: 11.5px;
}

.topbar-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 6px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.topbar-date {
    display: inline-flex;
    align-items: center;
    font-size: 11.5px;
    font-weight: 500;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.social-icons {
    display: flex;
    align-items: center;
    gap: 10px;
}

.social-link {
    color: #cbd5e1;
    transition: color 0.15s ease;
    display: flex;
    align-items: center;
}

.social-link:hover {
    color: #ffffff;
}

.topbar-sep {
    color: rgba(255, 255, 255, 0.2);
    font-size: 11px;
}

.topbar-admin-link {
    color: #cbd5e1;
    text-decoration: none;
    font-size: 11.5px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    transition: color 0.15s ease;
}

.topbar-admin-link:hover {
    color: #facc15;
}

/* ── 2. Main Header (Continuous Smooth Gradient) ── */
.main-header {
    width: 100%;
    position: sticky;
    top: 0;
    z-index: 50;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    background: linear-gradient(90deg, #0b2452 0%, #0e3770 20%, #064b4c 45%, #026b42 70%, #008744 100%);
}

.header-inner {
    display: flex;
    height: 96px;
    background: transparent;
    max-width: 1280px;
    margin: 0 auto;
    width: 100%;
    padding: 0 24px;
}

/* Brand Section */
.header-brand-navy {
    background: transparent;
    display: flex;
    align-items: center;
    padding: 0;
    flex-shrink: 0;
}

.brand-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    padding: 6px 0;
}

.brand-logo-img {
    height: 80px;
    width: auto;
    max-width: 420px;
    object-fit: contain;
    filter: drop-shadow(0 3px 10px rgba(0, 0, 0, 0.35));
    transition: transform 0.2s ease;
}

.brand-logo-img:hover {
    transform: scale(1.02);
}

@media (max-width: 768px) {
    .header-inner {
        height: 78px;
        padding: 0 16px;
    }
    .brand-logo-img {
        height: 60px;
        max-width: 280px;
    }
}

/* Nav Menu Section */
.header-nav-green {
    background: transparent;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0;
}

.nav-menu {
    display: none;
    list-style: none;
    margin: 0;
    padding: 0;
    align-items: center;
    gap: 4px;
    height: 100%;
}

@media (min-width: 992px) {
    .nav-menu {
        display: flex;
    }
}

.nav-item {
    position: relative;
    height: 100%;
    display: flex;
    align-items: center;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0 14px;
    height: 100%;
    color: #ffffff;
    font-size: 12.5px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-decoration: none;
    transition: all 0.15s ease;
    border-bottom: 3px solid transparent;
}

.nav-link:hover,
.nav-link--active {
    color: #ffffff;
    background: rgba(0, 0, 0, 0.1);
    border-bottom-color: #facc15;
}

/* Dropdown */
.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 200px;
    background: #ffffff;
    border-radius: 0 0 10px 10px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    list-style: none;
    padding: 8px 0;
    margin: 0;
    border-top: 3px solid #008744;
}

.dropdown-link {
    display: block;
    padding: 10px 18px;
    color: #1e293b;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s ease;
}

.dropdown-link:hover {
    background: #f0fdf4;
    color: #008744;
    padding-left: 22px;
}

/* Search Trigger */
.btn-search-trigger {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    border: none;
    cursor: pointer;
    margin-left: 8px;
    transition: all 0.15s ease;
}

.btn-search-trigger:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* Mobile Burger */
.mobile-burger-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    color: #ffffff;
    cursor: pointer;
}

@media (min-width: 992px) {
    .mobile-burger-btn {
        display: none;
    }
}

.mobile-drawer {
    background: #008744;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    padding: 12px 20px 20px;
}

.mobile-menu-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.mobile-nav-link {
    display: block;
    padding: 10px 0;
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.mobile-nav-admin {
    color: #fde047;
}

/* ── 3. Newsflash Bar ── */
.newsflash-bar {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
}

.newsflash-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    height: 42px;
}

.newsflash-badge {
    display: inline-flex;
    align-items: center;
    background: #f59e0b;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 4px;
    letter-spacing: 0.5px;
    flex-shrink: 0;
    margin-right: 14px;
}

.newsflash-content {
    flex: 1;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.newsflash-text {
    font-size: 12.5px;
    font-weight: 600;
    color: #334155;
}

.newsflash-controls {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-left: 12px;
}

.flash-btn {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
}

.flash-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
}

/* ── 4. Main Page Content ── */
.main-page-content {
    flex: 1;
}

/* ── 5. Upper Directory Footer (White) ── */
.upper-footer {
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
}

.upper-footer-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 44px 24px 40px;
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
}

@media (min-width: 640px) {
    .upper-footer-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .upper-footer-container {
        grid-template-columns: repeat(4, 1fr);
        gap: 32px;
    }
}

.dir-title {
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 16px 0;
    padding-bottom: 8px;
    border-bottom: 2px solid #008744;
    display: block;
}

.dir-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 9px;
}

.dir-link {
    color: #475569;
    font-size: 12.5px;
    text-decoration: none;
    transition: all 0.15s ease;
    line-height: 1.45;
    display: inline-block;
}

.dir-link:hover {
    color: #008744;
    transform: translateX(3px);
}

/* ── 6. Signature Main Footer (Continuous Smooth Gradient) ── */
.main-footer-split {
    width: 100%;
    background: linear-gradient(100deg, #091d3e 0%, #0d3466 22%, #064d47 48%, #016b41 72%, #008744 100%);
}

.footer-split-inner {
    display: flex;
    flex-direction: column;
    background: transparent;
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
}

@media (min-width: 900px) {
    .footer-split-inner {
        flex-direction: row;
        align-items: flex-start;
    }
}

/* Left: Brand Area */
.footer-brand-navy {
    background: transparent;
    padding: 44px 32px 44px 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

@media (min-width: 900px) {
    .footer-brand-navy {
        width: 320px;
        flex-shrink: 0;
    }
}

.footer-brand-link {
    display: block;
    margin-bottom: 14px;
    text-decoration: none;
}

.footer-square-logo {
    width: 175px;
    height: auto;
    max-height: 175px;
    object-fit: contain;
    filter: drop-shadow(0 4px 14px rgba(0, 0, 0, 0.4));
    transition: transform 0.2s ease;
}

.footer-square-logo:hover {
    transform: scale(1.03);
}

.footer-contact-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 18px;
    max-width: 290px;
}

.footer-contact-item {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    font-size: 11.5px;
    color: #cbd5e1;
    line-height: 1.45;
    text-align: center;
    justify-content: center;
}

.footer-contact-icon {
    color: #4ade80;
    flex-shrink: 0;
    margin-top: 2px;
}

.footer-contact-link {
    color: #cbd5e1;
    text-decoration: none;
    transition: color 0.15s ease;
}

.footer-contact-link:hover {
    color: #facc15;
}

.footer-socials {
    display: flex;
    align-items: center;
    gap: 12px;
    justify-content: center;
}

.footer-soc-link {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.footer-soc-link:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

/* Right: Menu Area */
.footer-menu-green {
    background: transparent;
    flex: 1;
    padding: 44px 0 44px 32px;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 32px;
}

@media (min-width: 640px) {
    .footer-menu-green {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 899px) {
    .footer-brand-navy {
        padding: 40px 0 24px;
    }
    .footer-menu-green {
        padding: 0 0 40px;
    }
}

.col-heading {
    font-size: 14px;
    font-weight: 700;
    color: #ffffff;
    margin: 0 0 14px 0;
    position: relative;
    padding-bottom: 6px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.3);
}

.col-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.col-links a {
    color: #e2e8f0;
    font-size: 12px;
    text-decoration: none;
    transition: all 0.15s ease;
}

.col-links a:hover {
    color: #ffffff;
    padding-left: 4px;
}

/* ── 7. Copyright Bar ── */
.footer-copyright-bar {
    background: #071936;
    padding: 12px 20px;
    text-align: center;
    font-size: 11.5px;
    color: #94a3b8;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.copyright-container {
    max-width: 1240px;
    margin: 0 auto;
}

/* ── 8. Floating WhatsApp ── */
.floating-whatsapp-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 99;
    display: flex;
    align-items: center;
    background: #ffffff;
    padding: 4px 6px 4px 14px;
    border-radius: 9999px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    text-decoration: none;
    gap: 8px;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.floating-whatsapp-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 211, 102, 0.3);
}

.whatsapp-label {
    font-size: 12px;
    font-weight: 600;
    color: #334155;
}

.whatsapp-icon-circle {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #25d366;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── Search Modal ── */
.search-modal-box {
    display: flex;
    align-items: center;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 6px 12px;
}

.search-modal-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 13.5px;
    outline: none;
    color: #1e293b;
}

.btn-modal-search {
    background: #008744;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    padding: 6px 14px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
}
</style>
