<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';

const props = defineProps({
    appName: { type: String, default: 'PCM Simo' },
    articles: { type: Array, default: () => [] },
    featuredArticles: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    officials: { type: Array, default: () => [] },
    aums: { type: Array, default: () => [] },
    prayerSchedule: { type: Array, default: () => [] },
    prayerSource: { type: String, default: 'static' },
    prayerDate: { type: String, default: '' },
    filters: { type: Object, default: () => ({ kategori: 'semua', cari: '' }) },
});

// Amal Usaha & Masjid (dikelola dari Dashboard → AUM & Organisasi)
const homeAums = computed(() => props.aums.slice(0, 8));

const aumVisual = (item) => {
    const cat = `${item.category || ''} ${item.name || ''}`.toLowerCase();
    let visual = { icon: 'mdi-office-building-outline', color: '#d97706' };
    if (item.type === 'masjid' || cat.includes('masjid') || cat.includes('dakwah')) visual = { icon: 'mdi-mosque', color: '#059669' };
    else if (cat.includes('kesehatan') || cat.includes('klinik') || cat.includes('pku')) visual = { icon: 'mdi-hospital-building', color: '#008744' };
    else if (cat.includes('pendidikan') || cat.includes('sekolah')) visual = { icon: 'mdi-school', color: '#0284c7' };
    else if (cat.includes('sosial') || cat.includes('filantropi') || cat.includes('panti')) visual = { icon: 'mdi-hand-heart-outline', color: '#db2777' };
    else if (cat.includes('ekonomi') || cat.includes('usaha')) visual = { icon: 'mdi-storefront-outline', color: '#7c3aed' };
    return { ...visual, icon: item.icon || visual.icon };
};

// Format Date ID
const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const formatDayDate = (dateStr) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).toUpperCase();
};

// ── Live Clock & Active Prayer Detection ──
const currentTime = ref('');
const currentDateFormatted = ref('');
const activePrayerIndex = ref(-1);

let clockInterval = null;

const updateClock = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: 'Asia/Jakarta',
    });
    currentDateFormatted.value = now.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        timeZone: 'Asia/Jakarta',
    });

    // Detect active/next prayer
    const nowMinutes = now.getHours() * 60 + now.getMinutes();
    let foundIndex = -1;
    if (props.prayerSchedule?.length) {
        for (let i = props.prayerSchedule.length - 1; i >= 0; i--) {
            const [h, m] = props.prayerSchedule[i].time.split(':').map(Number);
            if (nowMinutes >= h * 60 + m) {
                foundIndex = i;
                break;
            }
        }
        if (foundIndex === -1) foundIndex = props.prayerSchedule.length - 1;
    }
    activePrayerIndex.value = foundIndex;
};

// ── Auto-Slide Hero ──
const currentSlide = ref(0);
const isSlideTransitioning = ref(false);
const slideProgress = ref(0);
const isHeroPaused = ref(false);

const SLIDE_INTERVAL = 6000; // 6 seconds per slide
let autoSlideTimer = null;
let progressTimer = null;

const heroArticles = computed(() => {
    if (props.featuredArticles?.length) return props.featuredArticles;
    return props.articles.slice(0, 5);
});

const activeHero = computed(() => {
    return heroArticles.value[currentSlide.value] || heroArticles.value[0] || null;
});

const goToSlide = (idx) => {
    if (isSlideTransitioning.value || idx === currentSlide.value) return;
    isSlideTransitioning.value = true;
    currentSlide.value = idx;
    resetAutoSlide();
    setTimeout(() => { isSlideTransitioning.value = false; }, 600);
};

const nextSlide = () => {
    goToSlide((currentSlide.value + 1) % heroArticles.value.length);
};

const prevSlide = () => {
    goToSlide((currentSlide.value - 1 + heroArticles.value.length) % heroArticles.value.length);
};

const startAutoSlide = () => {
    stopAutoSlide();
    if (heroArticles.value.length <= 1) return;
    slideProgress.value = 0;
    const step = 50; // update progress every 50ms
    progressTimer = setInterval(() => {
        if (!isHeroPaused.value) {
            slideProgress.value += (step / SLIDE_INTERVAL) * 100;
            if (slideProgress.value >= 100) {
                slideProgress.value = 0;
                nextSlide();
            }
        }
    }, step);
};

const stopAutoSlide = () => {
    if (progressTimer) { clearInterval(progressTimer); progressTimer = null; }
    if (autoSlideTimer) { clearTimeout(autoSlideTimer); autoSlideTimer = null; }
};

const resetAutoSlide = () => {
    slideProgress.value = 0;
    startAutoSlide();
};

const pauseSlider = () => { isHeroPaused.value = true; };
const resumeSlider = () => { isHeroPaused.value = false; };

// ── Scroll Reveal ──
let revealObserver = null;

const initScrollReveal = () => {
    const els = document.querySelectorAll('.reveal-section');
    if (!els.length) return;
    revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    els.forEach(el => revealObserver.observe(el));
};

onMounted(() => {
    updateClock();
    clockInterval = setInterval(updateClock, 1000);
    startAutoSlide();
    nextTick(() => initScrollReveal());
});

onUnmounted(() => {
    if (clockInterval) clearInterval(clockInterval);
    stopAutoSlide();
    if (revealObserver) revealObserver.disconnect();
});

// Right Tab Widget
const activeTab = ref('trending');

const trendingArticles = computed(() => {
    return [...props.articles].sort((a, b) => (b.views || 0) - (a.views || 0)).slice(0, 5);
});

const latestArticles = computed(() => {
    return [...props.articles].slice(0, 5);
});

// Newsflash titles
const newsflashList = computed(() => {
    return props.articles.slice(0, 5).map(a => a.title);
});
</script>

<template>
    <Head title="Portal Resmi | PCM Simo" />

    <PublicLayout :newsflash-items="newsflashList">
        <div class="welcome-container">
            <!-- ── Top Hero Grid (Col 8 Left + Col 4 Right) ── -->
            <section class="hero-section">
                <div class="hero-grid">
                    <!-- Left: Featured Slider -->
                    <div class="hero-slider-col" @mouseenter="pauseSlider" @mouseleave="resumeSlider">
                        <div v-if="activeHero" class="hero-main-card">
                            <!-- Crossfade Slides -->
                            <TransitionGroup name="hero-fade">
                                <div
                                    v-for="(art, idx) in heroArticles"
                                    v-show="idx === currentSlide"
                                    :key="art.id"
                                    class="hero-slide-layer"
                                >
                                    <img
                                        :src="art.image_url || 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=1200&q=80'"
                                        :alt="art.title"
                                        class="hero-main-img"
                                    />
                                </div>
                            </TransitionGroup>
                            <div class="hero-gradient-overlay"></div>

                            <!-- Content Overlay -->
                            <div class="hero-card-content">
                                <span class="hero-category-tag">
                                    {{ activeHero.category?.name || 'BERITA' }}
                                </span>

                                <Link :href="`/berita/${activeHero.slug}`" class="hero-title-link">
                                    <h2 class="hero-headline-title">
                                        {{ activeHero.title }}
                                    </h2>
                                </Link>

                                <div class="hero-meta-row">
                                    <span class="meta-author">Tim Redaksi PCMSimo</span>
                                    <span class="meta-dot">•</span>
                                    <span class="meta-date">{{ formatDayDate(activeHero.published_at) }}</span>
                                </div>
                            </div>

                            <!-- Slide Counter -->
                            <div class="slide-counter">
                                <span class="slide-counter-current">{{ String(currentSlide + 1).padStart(2, '0') }}</span>
                                <span class="slide-counter-sep">/</span>
                                <span class="slide-counter-total">{{ String(heroArticles.length).padStart(2, '0') }}</span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="slide-progress-track">
                                <div class="slide-progress-bar" :style="{ width: slideProgress + '%' }"></div>
                            </div>

                            <!-- Slider Arrows -->
                            <button type="button" class="slider-arrow slider-arrow-prev" @click="prevSlide" title="Sebelumnya">
                                <v-icon size="20">mdi-chevron-left</v-icon>
                            </button>
                            <button type="button" class="slider-arrow slider-arrow-next" @click="nextSlide" title="Berikutnya">
                                <v-icon size="20">mdi-chevron-right</v-icon>
                            </button>
                        </div>

                        <!-- Thumbnails Strip Below Slider -->
                        <div class="hero-thumb-strip">
                            <div
                                v-for="(art, idx) in heroArticles"
                                :key="art.id"
                                :class="['strip-thumb-item', { 'strip-thumb-item--active': idx === currentSlide }]"
                                @click="goToSlide(idx)"
                            >
                                <img
                                    :src="art.image_url || 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=300&q=80'"
                                    :alt="art.title"
                                    class="strip-thumb-img"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Right: Tabbed Box -->
                    <div class="hero-tabs-col">
                        <div class="tabs-card">
                            <div class="tabs-header">
                                <button type="button" :class="['tab-btn', { 'tab-btn--active': activeTab === 'trending' }]" @click="activeTab = 'trending'">Trending</button>
                                <button type="button" :class="['tab-btn', { 'tab-btn--active': activeTab === 'terbaru' }]" @click="activeTab = 'terbaru'">Latest</button>
                                <button type="button" :class="['tab-btn', { 'tab-btn--active': activeTab === 'komentar' }]" @click="activeTab = 'komentar'">Popular</button>
                            </div>

                            <Transition name="tab-fade" mode="out-in">
                                <div :key="activeTab" class="tabs-body">
                                    <div
                                        v-for="(art, idx) in (activeTab === 'trending' ? trendingArticles : latestArticles)"
                                        :key="art.id"
                                        class="tab-article-row"
                                        :style="{ animationDelay: idx * 60 + 'ms' }"
                                    >
                                        <div class="tab-art-thumb">
                                            <img
                                                :src="art.image_url || 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=200&q=80'"
                                                :alt="art.title"
                                                class="tab-thumb-img"
                                            />
                                        </div>
                                        <div class="tab-art-info">
                                            <Link :href="`/berita/${art.slug}`" class="tab-art-title">
                                                {{ art.title }}
                                            </Link>
                                            <div class="tab-art-date">
                                                <v-icon size="12" class="mr-1">mdi-clock-outline</v-icon>
                                                <span>{{ formatDayDate(art.published_at) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Jadwal Shalat Muhammadiyah KHGT ── -->
            <section class="prayer-section reveal-section">
                <div class="prayer-card-premium">
                    <!-- Header with gradient -->
                    <div class="prayer-card-header">
                        <div class="prayer-header-left">
                            <div class="prayer-mosque-icon">
                                <v-icon size="26" color="#ffffff">mdi-mosque</v-icon>
                            </div>
                            <div class="prayer-header-text">
                                <h3 class="prayer-main-title">Jadwal Shalat Wilayah Simo</h3>
                                <p class="prayer-subtitle">{{ currentDateFormatted }}</p>
                            </div>
                        </div>
                        <div class="prayer-header-right">
                            <div class="prayer-live-clock">{{ currentTime }}</div>
                            <span class="prayer-badge-khgt">KHGT Muhammadiyah</span>
                        </div>
                    </div>

                    <!-- Prayer Times Grid -->
                    <div class="prayer-times-grid">
                        <div
                            v-for="(pray, idx) in prayerSchedule"
                            :key="pray.name"
                            :class="['prayer-time-card', { 'prayer-time-card--active': idx === activePrayerIndex }]"
                        >
                            <div class="ptc-icon-wrap">
                                <v-icon :size="idx === activePrayerIndex ? 28 : 24" :color="idx === activePrayerIndex ? '#ffffff' : '#047857'">{{ pray.icon }}</v-icon>
                            </div>
                            <span class="ptc-name">{{ pray.name }}</span>
                            <span class="ptc-time">{{ pray.time }}</span>
                            <span class="ptc-wib">WIB</span>
                            <span v-if="idx === activePrayerIndex" class="ptc-active-dot"></span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="prayer-card-footer">
                        <span class="prayer-footer-source">
                            <v-icon size="12" class="mr-1">mdi-information-outline</v-icon>
                            Sumber: Hisabmu.org · Hisab Hakiki Muhammadiyah · Ihtiyat {{ prayerSource === 'hisabmu' ? '2 menit' : '—' }}
                        </span>
                        <a href="https://hisabmu.org" target="_blank" rel="noopener" class="prayer-footer-link">
                            hisabmu.org
                            <v-icon size="11" class="ml-0.5">mdi-open-in-new</v-icon>
                        </a>
                    </div>
                </div>
            </section>

            <!-- ── Featured Stories Section (Grid 2 Kolom) ── -->
            <section class="stories-section reveal-section" id="berita">
                <div class="section-heading-bar">
                    <div class="d-flex align-center ga-2">
                        <h2 class="section-title">
                            Featured <span class="title-highlight">Stories</span>
                        </h2>
                    </div>
                    <Link href="#berita" class="view-more-link">
                        Lihat Semua Warta &rarr;
                    </Link>
                </div>

                <div class="stories-grid">
                    <!-- Left: Article Cards -->
                    <div class="stories-main-list">
                        <article
                            v-for="(art, idx) in articles.slice(0, 4)"
                            :key="art.id"
                            class="story-card story-card-animated"
                            :style="{ animationDelay: idx * 100 + 'ms' }"
                        >
                            <div class="story-img-wrap">
                                <img
                                    :src="art.image_url || 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=600&q=80'"
                                    :alt="art.title"
                                    class="story-img"
                                />
                                <span class="story-cat-badge">{{ art.category?.name }}</span>
                            </div>

                            <div class="story-body">
                                <Link :href="`/berita/${art.slug}`" class="story-title-link">
                                    <h3 class="story-title">{{ art.title }}</h3>
                                </Link>

                                <div class="story-meta">
                                    <span class="meta-by">Tim Redaksi PCMSimo</span>
                                    <span class="meta-dot">•</span>
                                    <span>{{ formatDayDate(art.published_at) }}</span>
                                </div>

                                <p class="story-excerpt">
                                    {{ art.excerpt || 'Klik untuk membaca selengkapnya warta kegiatan dan informasi dakwah persyarikatan PCM Simo...' }}
                                </p>

                                <Link :href="`/berita/${art.slug}`" class="story-read-btn">
                                    READ MORE
                                </Link>
                            </div>
                        </article>
                    </div>

                    <!-- Right: Maklumat & Popular Stories Sidebar -->
                    <aside class="stories-sidebar">
                        <!-- Popular Stories Header -->
                        <div class="sidebar-heading">
                            <h3 class="side-title">Popular Stories</h3>
                        </div>

                        <div class="side-articles-list">
                            <div
                                v-for="art in trendingArticles.slice(0, 3)"
                                :key="art.id"
                                class="side-article-card"
                            >
                                <img
                                    :src="art.image_url || 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=400&q=80'"
                                    :alt="art.title"
                                    class="side-art-img"
                                />
                                <div class="side-art-content">
                                    <Link :href="`/berita/${art.slug}`" class="side-art-title">
                                        {{ art.title }}
                                    </Link>
                                    <div class="side-art-meta">
                                        <v-icon size="12" class="mr-1">mdi-calendar-blank-outline</v-icon>
                                        {{ formatDate(art.published_at) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Maklumat Card -->
                        <div class="maklumat-banner-card">
                            <div class="maklumat-badge">MAKLUMAT RESMI</div>
                            <h4 class="maklumat-title">PANDUAN IBADAH & TARJIH PCM SIMO</h4>
                            <p class="maklumat-sub">Himpunan Putusan Tarjih dan Ketetapan Resmi Persyarikatan Muhammadiyah</p>
                            <a href="#khazanah" class="maklumat-btn">
                                Unduh Dokumen
                            </a>
                        </div>
                    </aside>
                </div>
            </section>

            <!-- ── Pimpinan Cabang Muhammadiyah Simo 2023-2028 Preview ── -->
            <section class="pimpinan-preview-section reveal-section" id="organisasi">
                <div class="section-heading-bar flex-between">
                    <div>
                        <h2 class="section-title">
                            Pimpinan Cabang <span class="title-highlight">Muhammadiyah Simo</span>
                        </h2>
                        <span class="heading-desc">Periode 2023 – 2028 | SK PDM Boyolali No. 100/KEP/III.0/D/2023</span>
                    </div>
                    <Link href="/profil-organisasi" class="btn-more-pimpinan">
                        <span>Lihat Profil Lengkap</span>
                        <v-icon size="16">mdi-arrow-right</v-icon>
                    </Link>
                </div>

                <div class="pimpinan-preview-grid">
                    <!-- Featured Ketua Card -->
                    <div class="pimpinan-preview-card pimpinan-preview-card--ketua">
                        <div class="pimpinan-prev-badge">KETUA PCM SIMO</div>
                        <div class="pimpinan-prev-avatar">
                            <span class="avatar-init">HS</span>
                        </div>
                        <div class="pimpinan-prev-info">
                            <h3 class="pimpinan-prev-name">H. Sholihin, S.Pd</h3>
                            <p class="pimpinan-prev-role">Ketua Pimpinan Cabang</p>
                            <span class="pimpinan-prev-period">Periode 2023 – 2028</span>
                        </div>
                    </div>

                    <!-- Anggota Preview Cards (3 items) -->
                    <div
                        v-for="off in officials.filter(o => o.sort_order !== 1).slice(0, 3)"
                        :key="off.id"
                        class="pimpinan-preview-card"
                    >
                        <div class="pimpinan-prev-badge pimpinan-prev-badge--sub">ANGGOTA</div>
                        <div class="pimpinan-prev-avatar pimpinan-prev-avatar--sub">
                            <span class="avatar-init">
                                {{ off.name.replace(/^(Drs\.|H\.|Ir\.|dr\.)\s*/i, '').substring(0, 2).toUpperCase() }}
                            </span>
                        </div>
                        <div class="pimpinan-prev-info">
                            <h3 class="pimpinan-prev-name">{{ off.name }}</h3>
                            <p class="pimpinan-prev-role">Anggota Pimpinan Cabang</p>
                            <span class="pimpinan-prev-period">PCM Simo</span>
                        </div>
                    </div>
                </div>

                <div class="pimpinan-cta-bottom">
                    <p class="pimpinan-cta-text">
                        Total 9 Personalia Pimpinan Cabang Muhammadiyah Simo Masa Jabatan 2023 – 2028 telah ditetapkan secara sah oleh PDM Boyolali.
                    </p>
                    <Link href="/profil-organisasi" class="pimpinan-cta-link">
                        <span>Buka Susunan Lengkap & Dokumen SK</span>
                        <v-icon size="16" class="ml-1">mdi-arrow-right</v-icon>
                    </Link>
                </div>
            </section>

            <!-- ── Amal Usaha Muhammadiyah (AUM) Simo ── -->
            <section class="aum-section reveal-section" id="amal-usaha">
                <div class="section-heading-bar">
                    <h2 class="section-title">
                        Amal Usaha <span class="title-highlight">Muhammadiyah Simo</span>
                    </h2>
                    <span class="heading-desc">Pendidikan, Kesehatan, Sosial & Dakwah</span>
                </div>

                <div class="aum-grid">
                    <div v-for="item in homeAums" :key="item.id" class="aum-card">
                        <div class="aum-icon-circle">
                            <v-icon size="28" :color="aumVisual(item).color">{{ aumVisual(item).icon }}</v-icon>
                        </div>
                        <h3 class="aum-name">{{ item.name }}</h3>
                        <p v-if="item.description" class="aum-desc">{{ item.description }}</p>
                        <span class="aum-tag">{{ item.category || (item.type === 'masjid' ? 'Dakwah & Ibadah' : 'Amal Usaha') }}</span>
                    </div>
                </div>

                <div v-if="aums.length > homeAums.length" class="text-center mt-6">
                    <Link href="/profil-organisasi" class="pimpinan-cta-link">
                        Lihat semua Amal Usaha &amp; Organisasi
                        <v-icon size="16" class="ml-1">mdi-arrow-right</v-icon>
                    </Link>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>

<style scoped>
.welcome-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 24px 24px 44px;
}

/* ── Hero Grid ── */
.hero-section {
    margin-bottom: 28px;
}

.hero-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}

@media (min-width: 992px) {
    .hero-grid {
        grid-template-columns: 1fr 340px;
    }
}

/* Hero Main Card */
.hero-main-card {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    height: 420px;
    background: #0f172a;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
}

/* Slide layers for crossfade */
.hero-slide-layer {
    position: absolute;
    inset: 0;
    z-index: 1;
}

.hero-fade-enter-active,
.hero-fade-leave-active {
    transition: opacity 0.6s ease;
}
.hero-fade-enter-from { opacity: 0; }
.hero-fade-enter-to { opacity: 1; }
.hero-fade-leave-from { opacity: 1; }
.hero-fade-leave-to { opacity: 0; }

.hero-main-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 8s ease;
}

/* Ken Burns subtle zoom on active slide */
.hero-slide-layer:not([style*='display: none']) .hero-main-img {
    animation: kenBurns 8s ease forwards;
}

@keyframes kenBurns {
    0% { transform: scale(1); }
    100% { transform: scale(1.06); }
}

.hero-gradient-overlay {
    position: absolute;
    inset: 0;
    z-index: 2;
    background: linear-gradient(180deg, rgba(0, 0, 0, 0.02) 0%, rgba(0, 0, 0, 0.35) 40%, rgba(0, 0, 0, 0.88) 100%);
}

.hero-card-content {
    position: absolute;
    bottom: 24px;
    left: 24px;
    right: 24px;
    z-index: 10;
}

.hero-category-tag {
    display: inline-block;
    background: #d97706;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 4px;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
    animation: fadeSlideUp 0.5s ease 0.1s both;
}

.hero-title-link {
    text-decoration: none;
    color: #ffffff;
}

.hero-headline-title {
    font-size: 24px;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.3;
    margin: 0 0 10px 0;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.2s ease;
}

.hero-headline-title:hover {
    color: #facc15;
}

.hero-meta-row {
    font-size: 11px;
    color: #cbd5e1;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    letter-spacing: 0.5px;
}

.meta-dot {
    color: #94a3b8;
}

/* Slide Counter */
.slide-counter {
    position: absolute;
    top: 16px;
    right: 16px;
    z-index: 15;
    font-family: 'Roboto Mono', monospace;
    font-size: 13px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.8);
    background: rgba(0, 0, 0, 0.35);
    backdrop-filter: blur(6px);
    padding: 4px 12px;
    border-radius: 8px;
    letter-spacing: 1px;
}

.slide-counter-current {
    color: #facc15;
}

.slide-counter-sep {
    margin: 0 3px;
    opacity: 0.5;
}

/* Progress Bar */
.slide-progress-track {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: rgba(255, 255, 255, 0.15);
    z-index: 15;
}

.slide-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #d97706, #f59e0b);
    transition: width 0.05s linear;
    border-radius: 0 2px 2px 0;
}

/* Slider Arrows */
.slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 50px;
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(6px);
    color: #ffffff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 20;
    transition: all 0.2s ease;
    opacity: 0;
}

.hero-main-card:hover .slider-arrow {
    opacity: 1;
}

.slider-arrow:hover {
    background: rgba(217, 119, 6, 0.95);
    transform: translateY(-50%) scale(1.05);
}

.slider-arrow-prev {
    left: 0;
    border-radius: 0 8px 8px 0;
}

.slider-arrow-next {
    right: 0;
    border-radius: 8px 0 0 8px;
}

/* Thumbnails Strip */
.hero-thumb-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-top: 10px;
}

@media (min-width: 640px) {
    .hero-thumb-strip {
        grid-template-columns: repeat(6, 1fr);
    }
}

.strip-thumb-item {
    height: 64px;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    opacity: 0.55;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.strip-thumb-item:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.strip-thumb-item--active {
    opacity: 1;
    border-color: #d97706;
    box-shadow: 0 2px 8px rgba(217, 119, 6, 0.3);
}

.strip-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.strip-thumb-item:hover .strip-thumb-img {
    transform: scale(1.08);
}

/* Tab Fade Transition */
.tab-fade-enter-active,
.tab-fade-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}
.tab-fade-enter-from {
    opacity: 0;
    transform: translateY(8px);
}
.tab-fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

.tab-article-row {
    animation: fadeSlideUp 0.35s ease both;
}

/* ── Right Tabs Column ── */
.tabs-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.tabs-header {
    display: flex;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.tab-btn {
    flex: 1;
    padding: 12px 6px;
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    border: none;
    background: transparent;
    cursor: pointer;
    text-align: center;
    border-bottom: 2px solid transparent;
    transition: all 0.15s ease;
}

.tab-btn--active {
    color: #0f172a;
    background: #ffffff;
    border-bottom-color: #008744;
}

.tabs-body {
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.tab-article-row {
    display: flex;
    gap: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f1f5f9;
}

.tab-article-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.tab-art-thumb {
    width: 78px;
    height: 56px;
    border-radius: 6px;
    overflow: hidden;
    flex-shrink: 0;
}

.tab-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tab-art-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.tab-art-title {
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
    text-decoration: none;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.15s ease;
}

.tab-art-title:hover {
    color: #008744;
}

.tab-art-date {
    font-size: 10px;
    color: #94a3b8;
    display: flex;
    align-items: center;
    margin-top: 4px;
    font-weight: 600;
}

/* ── Prayer Section Premium ── */
.prayer-section {
    margin-bottom: 36px;
}

.prayer-card-premium {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0, 135, 68, 0.08), 0 1px 4px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(0, 135, 68, 0.12);
}

/* Header */
.prayer-card-header {
    background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%);
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.prayer-header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.prayer-mosque-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.prayer-header-text {
    display: flex;
    flex-direction: column;
}

.prayer-main-title {
    font-family: 'El Messiri', 'Poppins', sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
    margin: 0;
    line-height: 1.2;
    letter-spacing: 0.3px;
}

.prayer-subtitle {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.75);
    margin: 2px 0 0 0;
    font-weight: 500;
}

.prayer-header-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 6px;
}

.prayer-live-clock {
    font-family: 'Roboto Mono', monospace;
    font-size: 28px;
    font-weight: 600;
    color: #ffffff;
    letter-spacing: 2px;
    line-height: 1;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.prayer-badge-khgt {
    display: inline-flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(6px);
    color: #d1fae5;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 9999px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Prayer Times Grid */
.prayer-times-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    padding: 18px 20px;
    background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);
}

@media (min-width: 640px) {
    .prayer-times-grid {
        grid-template-columns: repeat(6, 1fr);
        gap: 12px;
        padding: 20px 24px;
    }
}

.prayer-time-card {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 16px 10px 14px;
    border-radius: 14px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: default;
}

.prayer-time-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 135, 68, 0.1);
    border-color: #86efac;
}

.prayer-time-card--active {
    background: linear-gradient(135deg, #047857 0%, #059669 100%);
    border-color: transparent;
    box-shadow: 0 6px 24px rgba(4, 120, 87, 0.3);
    transform: translateY(-3px);
}

.prayer-time-card--active:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 28px rgba(4, 120, 87, 0.35);
}

.ptc-icon-wrap {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0fdf4;
    transition: background 0.25s ease;
}

.prayer-time-card--active .ptc-icon-wrap {
    background: rgba(255, 255, 255, 0.2);
}

.ptc-name {
    font-family: 'El Messiri', 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: #064e3b;
    letter-spacing: 0.3px;
    transition: color 0.25s ease;
}

.prayer-time-card--active .ptc-name {
    color: #d1fae5;
}

.ptc-time {
    font-family: 'Roboto Mono', monospace;
    font-size: 20px;
    font-weight: 600;
    color: #047857;
    letter-spacing: 1px;
    line-height: 1;
    transition: color 0.25s ease;
}

.prayer-time-card--active .ptc-time {
    color: #ffffff;
    font-size: 22px;
}

.ptc-wib {
    font-size: 9px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: -2px;
    transition: color 0.25s ease;
}

.prayer-time-card--active .ptc-wib {
    color: rgba(255, 255, 255, 0.6);
}

.ptc-active-dot {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #4ade80;
    box-shadow: 0 0 6px rgba(74, 222, 128, 0.6);
    animation: pulse-dot 2s ease-in-out infinite;
}

@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.4); }
}

/* Footer */
.prayer-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    padding: 10px 24px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
}

.prayer-footer-source {
    font-size: 10.5px;
    color: #94a3b8;
    display: flex;
    align-items: center;
    font-weight: 500;
}

.prayer-footer-link {
    font-size: 10.5px;
    color: #047857;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: color 0.15s ease;
}

.prayer-footer-link:hover {
    color: #065f46;
}

/* ── Stories Section ── */
.stories-section {
    margin-bottom: 44px;
}

.section-heading-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 10px;
    border-bottom: 2px solid #008744;
    margin-bottom: 24px;
}

.section-title {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.title-highlight {
    color: #d97706;
}

.view-more-link {
    color: #008744;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.15s ease;
}

.view-more-link:hover {
    color: #00552d;
    padding-right: 4px;
}

.stories-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
}

@media (min-width: 992px) {
    .stories-grid {
        grid-template-columns: 1fr 340px;
    }
}

/* Stories Cards List */
.stories-main-list {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.story-card {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 24px;
    transition: transform 0.3s ease;
}

.story-card-animated {
    animation: fadeSlideUp 0.5s ease both;
}

@media (min-width: 640px) {
    .story-card {
        grid-template-columns: 240px 1fr;
    }
}

.story-img-wrap {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    height: 160px;
}

.story-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.story-card:hover .story-img {
    transform: scale(1.06);
}

.story-cat-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #008744;
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
    transition: transform 0.3s ease;
}

.story-card:hover .story-cat-badge {
    transform: translateY(-2px);
}

.story-body {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.story-title-link {
    text-decoration: none;
}

.story-title {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.4;
    margin: 0 0 6px 0;
    transition: color 0.2s ease;
}

.story-title:hover {
    color: #008744;
}

.story-meta {
    font-size: 10.5px;
    color: #94a3b8;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.meta-by {
    color: #d97706;
}

.story-excerpt {
    font-size: 12.5px;
    color: #475569;
    line-height: 1.5;
    margin: 0 0 12px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.story-read-btn {
    display: inline-block;
    align-self: flex-start;
    padding: 6px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    color: #334155;
    text-decoration: none;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.story-read-btn::after {
    content: '';
    position: absolute;
    inset: 0;
    background: #008744;
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
    z-index: -1;
    border-radius: 5px;
}

.story-read-btn:hover {
    color: #ffffff;
    border-color: #008744;
}

.story-read-btn:hover::after {
    transform: scaleX(1);
}

/* ── Sidebar ── */
.stories-sidebar {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.sidebar-heading {
    padding-bottom: 8px;
    border-bottom: 2px solid #008744;
}

.side-title {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.side-articles-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.side-article-card {
    display: flex;
    gap: 12px;
    transition: transform 0.2s ease;
}

.side-article-card:hover {
    transform: translateX(4px);
}

.side-art-img {
    width: 84px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.side-article-card:hover .side-art-img {
    transform: scale(1.05);
}

.side-art-content {
    flex: 1;
}

.side-art-title {
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
    text-decoration: none;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.2s ease;
}

.side-art-title:hover {
    color: #008744;
}

.side-art-meta {
    font-size: 10px;
    color: #94a3b8;
    margin-top: 4px;
    display: flex;
    align-items: center;
}

/* Maklumat Card */
.maklumat-banner-card {
    background: linear-gradient(135deg, #0b224d 0%, #10316b 100%);
    border-radius: 12px;
    padding: 24px 20px;
    color: #ffffff;
    text-align: center;
    box-shadow: 0 4px 14px rgba(11, 34, 77, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.maklumat-banner-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(11, 34, 77, 0.3);
}

.maklumat-badge {
    background: #f59e0b;
    color: #ffffff;
    font-size: 10px;
    font-weight: 800;
    padding: 3px 10px;
    border-radius: 9999px;
    display: inline-block;
    margin-bottom: 10px;
}

.maklumat-title {
    font-size: 14px;
    font-weight: 800;
    margin: 0 0 6px 0;
    letter-spacing: 0.5px;
}

.maklumat-sub {
    font-size: 11.5px;
    color: #cbd5e1;
    line-height: 1.4;
    margin: 0 0 16px 0;
}

.maklumat-btn {
    display: inline-block;
    background: #008744;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    padding: 8px 18px;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.maklumat-btn:hover {
    background: #006837;
    transform: translateY(-1px);
}

/* ── Amal Usaha Section ── */
.aum-section {
    margin-bottom: 20px;
}

.heading-desc {
    font-size: 12px;
    color: #64748b;
}

.aum-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 18px;
}

@media (min-width: 640px) {
    .aum-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .aum-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.aum-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 22px 18px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.aum-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #008744, #059669);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.aum-card:hover::before {
    transform: scaleX(1);
}

.aum-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
    border-color: #86efac;
}

.aum-icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #f0fdf4;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    transition: transform 0.3s ease, background 0.3s ease;
}

.aum-card:hover .aum-icon-circle {
    transform: scale(1.1) rotate(-3deg);
    background: #dcfce7;
}

.aum-name {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
    margin: 0 0 8px 0;
}

.aum-desc {
    font-size: 12px;
    color: #64748b;
    line-height: 1.5;
    margin: 0 0 14px 0;
}

.aum-tag {
    align-self: flex-start;
    background: #f1f5f9;
    color: #475569;
    font-size: 10.5px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 4px;
    transition: background 0.2s ease, color 0.2s ease;
}

.aum-card:hover .aum-tag {
    background: #dcfce7;
    color: #047857;
}

/* ── Scroll Reveal Animations ── */
.reveal-section {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.7s cubic-bezier(0.4, 0, 0.2, 1),
                transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
}

.reveal-section.revealed {
    opacity: 1;
    transform: translateY(0);
}

/* Global Keyframes */
@keyframes fadeSlideUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ── Pimpinan Preview Section ── */
.pimpinan-preview-section {
    margin-bottom: 40px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
}

.flex-between {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.btn-more-pimpinan {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ecfdf5;
    color: #006837;
    border: 1px solid #a7f3d0;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-more-pimpinan:hover {
    background: #006837;
    color: #ffffff;
}

.pimpinan-preview-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-top: 18px;
}

.pimpinan-preview-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    transition: all 0.2s ease;
}

.pimpinan-preview-card:hover {
    border-color: #86efac;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
}

.pimpinan-preview-card--ketua {
    background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);
    border: 2px solid #86efac;
}

.pimpinan-prev-badge {
    font-size: 0.68rem;
    font-weight: 800;
    color: #065f46;
    background: #dcfce7;
    padding: 2px 8px;
    border-radius: 4px;
    margin-bottom: 12px;
    letter-spacing: 0.04em;
}

.pimpinan-prev-badge--sub {
    color: #475569;
    background: #e2e8f0;
}

.pimpinan-prev-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #F59E0B, #006837);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.2rem;
    margin-bottom: 10px;
    box-shadow: 0 4px 10px rgba(0, 104, 55, 0.15);
}

.pimpinan-prev-avatar--sub {
    background: linear-gradient(135deg, #0A2540, #006837);
    font-size: 1.1rem;
}

.pimpinan-prev-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    margin-bottom: 2px;
}

.pimpinan-prev-role {
    font-size: 0.78rem;
    color: #006837;
    font-weight: 600;
    margin-bottom: 4px;
}

.pimpinan-prev-period {
    font-size: 0.72rem;
    color: #64748b;
}

.pimpinan-cta-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f1f5f9;
    border-radius: 8px;
    padding: 12px 16px;
    margin-top: 18px;
    flex-wrap: wrap;
    gap: 10px;
}

.pimpinan-cta-text {
    font-size: 0.82rem;
    color: #475569;
}

.pimpinan-cta-link {
    font-size: 0.82rem;
    font-weight: 700;
    color: #006837;
    display: inline-flex;
    align-items: center;
    text-decoration: none;
}

.pimpinan-cta-link:hover {
    text-decoration: underline;
}

@media (max-width: 900px) {
    .pimpinan-preview-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 600px) {
    .pimpinan-preview-grid {
        grid-template-columns: 1fr;
    }
}
</style>
