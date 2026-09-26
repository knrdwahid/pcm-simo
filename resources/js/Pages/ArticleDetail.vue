<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    article: { type: Object, required: true },
    relatedArticles: { type: Array, default: () => [] },
    popularArticles: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    prayerSchedule: { type: Array, default: () => [] },
});

// Current URL for sharing
const currentUrl = ref('');
const copySuccess = ref(false);

onMounted(() => {
    currentUrl.value = window.location.href;
});

// Font Size Control
const fontSizes = ['text-sm', 'text-base', 'text-lg', 'text-xl'];
const currentFontIndex = ref(1); // Default text-base

const increaseFont = () => {
    if (currentFontIndex.value < fontSizes.length - 1) {
        currentFontIndex.value++;
    }
};

const decreaseFont = () => {
    if (currentFontIndex.value > 0) {
        currentFontIndex.value--;
    }
};

// Date Formatter
const formatFullDate = (dateStr) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const formatShortDate = (dateStr) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

// Reading Time Estimator
const readingTime = computed(() => {
    if (!props.article?.content) return '1 menit baca';
    const words = props.article.content.trim().split(/\s+/).length;
    const minutes = Math.max(1, Math.ceil(words / 200));
    return `${minutes} menit baca`;
});

// Share Links
const shareWhatsApp = () => {
    const text = encodeURIComponent(`${props.article.title}\n\nBaca selengkapnya di: ${currentUrl.value}`);
    window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
};

const shareFacebook = () => {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl.value)}`, '_blank');
};

const shareTwitter = () => {
    const text = encodeURIComponent(props.article.title);
    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${encodeURIComponent(currentUrl.value)}`, '_blank');
};

const shareTelegram = () => {
    const text = encodeURIComponent(props.article.title);
    window.open(`https://t.me/share/url?url=${encodeURIComponent(currentUrl.value)}&text=${text}`, '_blank');
};

const copyShareLink = async () => {
    try {
        await navigator.clipboard.writeText(currentUrl.value);
        copySuccess.value = true;
        setTimeout(() => {
            copySuccess.value = false;
        }, 2500);
    } catch (e) {
        // fallback
    }
};
</script>

<template>
    <Head :title="`${article.title} - Portal Resmi PCM Simo`">
        <meta name="description" :content="article.excerpt || article.title" />
    </Head>

    <PublicLayout>
        <div class="article-detail-page font-poppins">
            <div class="article-container">
                <!-- ── 1. Breadcrumbs ── -->
                <nav class="breadcrumb-nav">
                    <Link href="/" class="breadcrumb-item">
                        <v-icon size="14" class="mr-1 text-slate-400">mdi-home-outline</v-icon>
                        <span>Beranda</span>
                    </Link>
                    <span class="breadcrumb-sep">/</span>
                    <Link href="/#berita" class="breadcrumb-item">
                        <span>Warta Berita</span>
                    </Link>
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-current">{{ article.category?.name || 'Berita' }}</span>
                </nav>

                <!-- ── 2. Main Two-Column Layout ── -->
                <div class="article-layout-grid">
                    <!-- Left Column: The Main Article Content (Width ~68%) -->
                    <main class="article-main-col">
                        <article class="article-card">
                            <!-- Category Badge & Top Meta Header -->
                            <div class="article-header-meta">
                                <span class="article-cat-pill">
                                    {{ article.category?.name || 'KABAR CABANG' }}
                                </span>
                            </div>

                            <!-- Article Title / Headline -->
                            <h1 class="article-headline font-messiri">
                                {{ article.title }}
                            </h1>

                            <!-- Author & Publishing Meta Box -->
                            <div class="author-meta-bar">
                                <div class="author-profile">
                                    <div class="author-avatar-circle">
                                        <v-icon size="20" color="#008744">mdi-account-edit</v-icon>
                                    </div>
                                    <div class="author-details">
                                        <div class="author-name-row">
                                            <span class="author-name">Tim Redaksi PCMSimo</span>
                                            <span class="author-verified-badge" title="Terverifikasi Resmi">
                                                <v-icon size="13" color="#008744">mdi-check-decagram</v-icon>
                                            </span>
                                        </div>
                                        <span class="author-subtitle">Portal Resmi Pimpinan Cabang Muhammadiyah Simo</span>
                                    </div>
                                </div>

                                <div class="article-stats-group">
                                    <div class="meta-stat-item">
                                        <v-icon size="14" class="text-slate-400 mr-1.5">mdi-calendar-clock</v-icon>
                                        <span>{{ formatFullDate(article.published_at) }}</span>
                                    </div>
                                    <div class="meta-stat-divider">•</div>
                                    <div class="meta-stat-item">
                                        <v-icon size="14" class="text-slate-400 mr-1.5">mdi-book-open-outline</v-icon>
                                        <span>{{ readingTime }}</span>
                                    </div>
                                    <div class="meta-stat-divider">•</div>
                                    <div class="meta-stat-item">
                                        <v-icon size="14" class="text-slate-400 mr-1.5">mdi-eye-outline</v-icon>
                                        <span>{{ article.views || 1 }} kali dibaca</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Action Bar: Social Share & Font Resizer -->
                            <div class="reading-toolbar">
                                <div class="share-label-group">
                                    <span class="toolbar-label">Bagikan:</span>
                                    <div class="share-buttons-row">
                                        <button
                                            type="button"
                                            class="share-pill share-pill--wa"
                                            @click="shareWhatsApp"
                                            title="Bagikan ke WhatsApp"
                                        >
                                            <v-icon size="16">mdi-whatsapp</v-icon>
                                            <span class="share-text">WhatsApp</span>
                                        </button>
                                        <button
                                            type="button"
                                            class="share-pill share-pill--fb"
                                            @click="shareFacebook"
                                            title="Bagikan ke Facebook"
                                        >
                                            <v-icon size="16">mdi-facebook</v-icon>
                                            <span class="share-text">Facebook</span>
                                        </button>
                                        <button
                                            type="button"
                                            class="share-pill share-pill--tw"
                                            @click="shareTwitter"
                                            title="Bagikan ke Twitter / X"
                                        >
                                            <v-icon size="16">mdi-twitter</v-icon>
                                        </button>
                                        <button
                                            type="button"
                                            class="share-pill share-pill--tg"
                                            @click="shareTelegram"
                                            title="Bagikan ke Telegram"
                                        >
                                            <v-icon size="16">mdi-send</v-icon>
                                        </button>
                                        <button
                                            type="button"
                                            class="share-pill share-pill--copy"
                                            @click="copyShareLink"
                                            :title="copySuccess ? 'Tersalin!' : 'Salin Tautan Berita'"
                                        >
                                            <v-icon size="16">{{ copySuccess ? 'mdi-check' : 'mdi-link-variant' }}</v-icon>
                                            <span class="share-text">{{ copySuccess ? 'Tersalin!' : 'Salin' }}</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="font-resizer-group">
                                    <span class="toolbar-label">Ukuran Font:</span>
                                    <div class="font-controls">
                                        <button
                                            type="button"
                                            class="font-btn"
                                            @click="decreaseFont"
                                            :disabled="currentFontIndex === 0"
                                            title="Perkecil Ukuran Teks"
                                        >
                                            A-
                                        </button>
                                        <button
                                            type="button"
                                            class="font-btn"
                                            @click="increaseFont"
                                            :disabled="currentFontIndex === fontSizes.length - 1"
                                            title="Perbesar Ukuran Teks"
                                        >
                                            A+
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Featured Image with Photo Caption -->
                            <figure class="featured-media-wrapper">
                                <div class="featured-img-container">
                                    <img
                                        :src="article.image_url || 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=1200&q=80'"
                                        :alt="article.title"
                                        class="featured-img"
                                    />
                                </div>
                                <figcaption class="featured-caption">
                                    <v-icon size="13" class="mr-1 text-slate-400">mdi-camera-outline</v-icon>
                                    <span>Dokumentasi Warta Pimpinan Cabang Muhammadiyah Simo / Istimewa</span>
                                </figcaption>
                            </figure>

                            <!-- Excerpt / Lead Box -->
                            <div v-if="article.excerpt" class="article-lead-box">
                                <p class="lead-text">
                                    {{ article.excerpt }}
                                </p>
                            </div>

                            <!-- Article Content Body -->
                            <div
                                class="article-body-content"
                                :class="fontSizes[currentFontIndex]"
                                v-html="article.content"
                            ></div>

                            <!-- Topics / Tags Section -->
                            <div class="article-tags-section">
                                <div class="tags-header">
                                    <v-icon size="16" color="#008744" class="mr-1.5">mdi-tag-multiple-outline</v-icon>
                                    <span class="tags-title">Topik Terkait:</span>
                                </div>
                                <div class="tags-cloud">
                                    <span class="topic-tag">#PCMSimo</span>
                                    <span class="topic-tag">#MuhammadiyahBoyolali</span>
                                    <span class="topic-tag">#{{ article.category?.name?.replace(/\s+/g, '') || 'BeritaCabang' }}</span>
                                    <span class="topic-tag">#GerakanIslamBerkemajuan</span>
                                    <span class="topic-tag">#KabarSimo</span>
                                </div>
                            </div>

                            <!-- Bottom Share Bar -->
                            <div class="bottom-share-card">
                                <div class="bottom-share-text">
                                    <h4 class="bottom-share-title">Sukai warta berita ini?</h4>
                                    <p class="bottom-share-desc">Bagikan informasi bermanfaat ini kepada kerabat dan grup warga persyarikatan.</p>
                                </div>
                                <div class="bottom-share-actions">
                                    <button type="button" class="action-share-btn btn-wa" @click="shareWhatsApp">
                                        <v-icon size="18" class="mr-1">mdi-whatsapp</v-icon>
                                        <span>WhatsApp</span>
                                    </button>
                                    <button type="button" class="action-share-btn btn-fb" @click="shareFacebook">
                                        <v-icon size="18" class="mr-1">mdi-facebook</v-icon>
                                        <span>Facebook</span>
                                    </button>
                                    <button type="button" class="action-share-btn btn-copy" @click="copyShareLink">
                                        <v-icon size="18" class="mr-1">{{ copySuccess ? 'mdi-check' : 'mdi-link-variant' }}</v-icon>
                                        <span>{{ copySuccess ? 'Tersalin!' : 'Salin Tautan' }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Editorial Attribution Card -->
                            <div class="editorial-author-card">
                                <div class="editorial-emblem">
                                    <img src="/images/logo-muhammadiyah-warna.png" alt="Muhammadiyah" class="emblem-img" />
                                </div>
                                <div class="editorial-bio">
                                    <div class="editorial-role-badge">REDAKSI RESMI</div>
                                    <h3 class="editorial-name">Tim Redaksi PCMSimo</h3>
                                    <p class="editorial-desc">
                                        Dikelola oleh Majelis Pustaka & Informasi (MPI) Pimpinan Cabang Muhammadiyah Simo, Kabupaten Boyolali. Menyajikan kabar persyarikatan yang aktual, mencerahkan, dan berkeadaban.
                                    </p>
                                </div>
                            </div>
                        </article>

                        <!-- ── 3. Rekomendasi Berita Lainnya Grid ── -->
                        <section class="related-stories-section">
                            <div class="section-title-strip">
                                <div class="d-flex align-center ga-2">
                                    <div class="title-accent-pill"></div>
                                    <h3 class="section-heading">Rekomendasi Berita Lainnya</h3>
                                </div>
                                <Link href="/#berita" class="see-all-link">
                                    <span>Lihat Semua Warta</span>
                                    <v-icon size="14">mdi-arrow-right</v-icon>
                                </Link>
                            </div>

                            <div class="related-grid">
                                <article
                                    v-for="rel in relatedArticles"
                                    :key="rel.id"
                                    class="related-card"
                                >
                                    <div class="related-thumb-wrap">
                                        <img
                                            :src="rel.image_url || 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=600&q=80'"
                                            :alt="rel.title"
                                            class="related-thumb"
                                        />
                                        <span class="related-cat-tag">{{ rel.category?.name }}</span>
                                    </div>
                                    <div class="related-card-body">
                                        <Link :href="`/berita/${rel.slug}`" class="related-title-link">
                                            <h4 class="related-card-title">{{ rel.title }}</h4>
                                        </Link>
                                        <div class="related-meta">
                                            <span>{{ formatShortDate(rel.published_at) }}</span>
                                            <span class="meta-dot">•</span>
                                            <span>{{ rel.views || 0 }} views</span>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </section>
                    </main>

                    <!-- Right Column: Sidebar Widgets (Width ~32%) -->
                    <aside class="article-sidebar-col">
                        <!-- Widget 1: Berita Populer (Numbered List #1 to #5) -->
                        <div class="sidebar-widget">
                            <div class="widget-header">
                                <div class="widget-title-wrap">
                                    <v-icon size="18" color="#008744" class="mr-2">mdi-trending-up</v-icon>
                                    <h3 class="widget-title">Berita Terpopuler</h3>
                                </div>
                                <span class="widget-badge">Trending</span>
                            </div>

                            <div class="popular-list">
                                <article
                                    v-for="(pop, idx) in popularArticles"
                                    :key="pop.id"
                                    class="popular-item"
                                >
                                    <div class="popular-rank-badge" :class="`rank-${idx + 1}`">
                                        {{ idx + 1 }}
                                    </div>
                                    <div class="popular-content">
                                        <span class="popular-cat">{{ pop.category?.name }}</span>
                                        <Link :href="`/berita/${pop.slug}`" class="popular-title-link">
                                            <h4 class="popular-title">{{ pop.title }}</h4>
                                        </Link>
                                        <div class="popular-meta">
                                            <span>{{ formatShortDate(pop.published_at) }}</span>
                                            <span class="meta-dot">•</span>
                                            <span>{{ pop.views }} pembaca</span>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </div>

                        <!-- Widget 2: Jadwal Shalat Simo & Boyolali -->
                        <div class="sidebar-widget">
                            <div class="widget-header">
                                <div class="widget-title-wrap">
                                    <v-icon size="18" color="#008744" class="mr-2">mdi-mosque</v-icon>
                                    <h3 class="widget-title">Jadwal Shalat Hari Ini</h3>
                                </div>
                                <span class="prayer-loc-pill">Simo & Boyolali</span>
                            </div>

                            <div class="prayer-sidebar-grid">
                                <div
                                    v-for="p in prayerSchedule"
                                    :key="p.name"
                                    class="prayer-item-box"
                                >
                                    <div class="prayer-icon-row">
                                        <v-icon size="16" color="#008744">{{ p.icon }}</v-icon>
                                        <span class="prayer-name">{{ p.name }}</span>
                                    </div>
                                    <span class="prayer-time font-mono">{{ p.time }}</span>
                                </div>
                            </div>
                            <div class="prayer-footer-note">
                                Waktu Indonesia Barat (WIB) • Rujukan Kemenag / Majelis Tarjih
                            </div>
                        </div>

                        <!-- Widget 3: Kategori / Rubrik Warta -->
                        <div class="sidebar-widget">
                            <div class="widget-header">
                                <div class="widget-title-wrap">
                                    <v-icon size="18" color="#008744" class="mr-2">mdi-folder-outline</v-icon>
                                    <h3 class="widget-title">Kategori Berita</h3>
                                </div>
                            </div>

                            <div class="categories-list">
                                <Link
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :href="`/?kategori=${cat.slug}#berita`"
                                    class="cat-list-row"
                                >
                                    <span class="cat-row-name">{{ cat.name }}</span>
                                    <span class="cat-row-count">{{ cat.articles_count }} warta</span>
                                </Link>
                            </div>
                        </div>

                        <!-- Widget 4: Card Kontak & Sekretariat PCM Simo -->
                        <div class="sidebar-widget cta-card-widget">
                            <div class="cta-card-inner">
                                <div class="cta-logo-wrap">
                                    <img src="/images/logo-pcmsimo-square-white.png" alt="PCM Simo" class="cta-logo-img" />
                                </div>
                                <h4 class="cta-title font-messiri">Gedung Dakwah PCM Simo</h4>
                                <p class="cta-desc">
                                    Pusat pelayanan dakwah persyarikatan, konsultasi keumatan, dan administrasi cabang.
                                </p>
                                <div class="cta-contact-row">
                                    <v-icon size="14" class="mr-1.5 text-amber-300">mdi-map-marker</v-icon>
                                    <span>Jl. Raya Simo - Bangak Km. 1, Simo, Boyolali</span>
                                </div>
                                <div class="cta-contact-row">
                                    <v-icon size="14" class="mr-1.5 text-amber-300">mdi-phone</v-icon>
                                    <span>(0276) 3294404</span>
                                </div>

                                <a
                                    href="https://wa.me/6281234567890?text=Assalamu%27alaikum%20PCM%20Simo"
                                    target="_blank"
                                    class="cta-wa-btn"
                                >
                                    <v-icon size="16" class="mr-1.5">mdi-whatsapp</v-icon>
                                    <span>Hubungi Sekretariat</span>
                                </a>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
/* ── Page Wrapper & Unified Container ── */
.article-detail-page {
    background: #f8fafc;
    min-height: 100vh;
    padding: 28px 0 64px;
}

.article-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
}

/* ── 1. Breadcrumbs ── */
.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #64748b;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.breadcrumb-item {
    color: #475569;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: color 0.15s ease;
}

.breadcrumb-item:hover {
    color: #008744;
}

.breadcrumb-sep {
    color: #cbd5e1;
    font-size: 11px;
}

.breadcrumb-current {
    color: #008744;
    font-weight: 700;
}

/* ── 2. Grid Layout (Main & Sidebar) ── */
.article-layout-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
}

@media (min-width: 1024px) {
    .article-layout-grid {
        grid-template-columns: 1fr 360px;
        align-items: start;
    }
}

/* ── Main Article Card ── */
.article-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 36px 32px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}

@media (max-width: 640px) {
    .article-card {
        padding: 24px 18px;
    }
}

.article-header-meta {
    margin-bottom: 14px;
}

.article-cat-pill {
    background: #008744;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 6px;
    letter-spacing: 0.6px;
    display: inline-block;
}

.article-headline {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.35;
    margin: 0 0 20px 0;
}

@media (min-width: 768px) {
    .article-headline {
        font-size: 34px;
    }
}

/* Author & Metadata Bar */
.author-meta-bar {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 16px 20px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #f1f5f9;
    margin-bottom: 24px;
}

@media (min-width: 768px) {
    .author-meta-bar {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.author-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.author-avatar-circle {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #e6f4ea;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.author-details {
    display: flex;
    flex-direction: column;
}

.author-name-row {
    display: flex;
    align-items: center;
    gap: 4px;
}

.author-name {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
}

.author-subtitle {
    font-size: 11px;
    color: #64748b;
}

.article-stats-group {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11.5px;
    color: #64748b;
    flex-wrap: wrap;
}

.meta-stat-item {
    display: inline-flex;
    align-items: center;
}

.meta-stat-divider {
    color: #cbd5e1;
}

/* Reading Toolbar (Share & Font Resizer) */
.reading-toolbar {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 14px 0;
    border-top: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 28px;
}

@media (min-width: 640px) {
    .reading-toolbar {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.share-label-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.toolbar-label {
    font-size: 11.5px;
    font-weight: 700;
    color: #475569;
}

.share-buttons-row {
    display: flex;
    align-items: center;
    gap: 6px;
}

.share-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11.5px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.15s ease;
    color: #ffffff;
}

.share-pill--wa {
    background: #25d366;
}
.share-pill--wa:hover {
    background: #1ebc57;
}

.share-pill--fb {
    background: #1877f2;
}
.share-pill--fb:hover {
    background: #1466d2;
}

.share-pill--tw {
    background: #0f172a;
    padding: 6px 9px;
}
.share-pill--tw:hover {
    background: #334155;
}

.share-pill--tg {
    background: #0088cc;
    padding: 6px 9px;
}
.share-pill--tg:hover {
    background: #0077b5;
}

.share-pill--copy {
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
}
.share-pill--copy:hover {
    background: #e2e8f0;
}

.font-resizer-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.font-controls {
    display: flex;
    gap: 4px;
}

.font-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.font-btn:hover:not(:disabled) {
    background: #f1f5f9;
    border-color: #94a3b8;
}

.font-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* Featured Media */
.featured-media-wrapper {
    margin: 0 0 28px 0;
}

.featured-img-container {
    width: 100%;
    max-height: 440px;
    border-radius: 14px;
    overflow: hidden;
    background: #0f172a;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.featured-img {
    width: 100%;
    height: 100%;
    max-height: 440px;
    object-fit: cover;
    display: block;
}

.featured-caption {
    font-size: 11.5px;
    color: #64748b;
    margin-top: 8px;
    display: flex;
    align-items: center;
    font-style: italic;
}

/* Lead Excerpt */
.article-lead-box {
    background: #fffbeb;
    border-left: 4px solid #f59e0b;
    border-radius: 0 10px 10px 0;
    padding: 18px 22px;
    margin-bottom: 28px;
}

.lead-text {
    font-size: 15.5px;
    font-weight: 500;
    color: #92400e;
    line-height: 1.65;
    margin: 0;
    font-style: italic;
}

/* Body Content with Rich Typography & Media */
.article-body-content {
    color: #1e293b;
    line-height: 1.85;
    margin-bottom: 36px;
    word-break: break-word;
}

.article-body-content :deep(p) {
    margin-bottom: 18px;
}

.article-body-content :deep(h2) {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    margin-top: 32px;
    margin-bottom: 14px;
    line-height: 1.35;
    letter-spacing: -0.015em;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 8px;
}

.article-body-content :deep(h3) {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    margin-top: 26px;
    margin-bottom: 10px;
    line-height: 1.4;
    letter-spacing: -0.01em;
}

.article-body-content :deep(strong) {
    font-weight: 700;
    color: #0f172a;
}

.article-body-content :deep(em) {
    font-style: italic;
}

.article-body-content :deep(u) {
    text-decoration: underline;
    text-underline-offset: 3px;
}

.article-body-content :deep(blockquote) {
    border-left: 4px solid #006837;
    background: #f0fdf4;
    padding: 16px 24px;
    margin: 24px 0;
    border-radius: 0 12px 12px 0;
    font-style: italic;
    color: #166534;
    font-size: 1.05em;
}

.article-body-content :deep(blockquote p) {
    margin: 0;
}

.article-body-content :deep(ul) {
    list-style-type: disc;
    padding-left: 26px;
    margin-bottom: 18px;
}

.article-body-content :deep(ol) {
    list-style-type: decimal;
    padding-left: 26px;
    margin-bottom: 18px;
}

.article-body-content :deep(li) {
    margin-bottom: 6px;
}

.article-body-content :deep(a) {
    color: #006837;
    text-decoration: underline;
    text-underline-offset: 3px;
    font-weight: 600;
    transition: color 0.15s ease;
}

.article-body-content :deep(a:hover) {
    color: #004d28;
}

.article-body-content :deep(hr) {
    border: none;
    border-top: 1px solid #e2e8f0;
    margin: 32px 0;
}

.article-body-content :deep(.article-image-block) {
    margin: 28px 0;
    max-width: 100%;
    clear: both;
}

.article-body-content :deep(.article-image-block.align-center) {
    margin-left: auto;
    margin-right: auto;
    text-align: center;
}

.article-body-content :deep(.article-image-block.align-center img) {
    margin-left: auto;
    margin-right: auto;
}

.article-body-content :deep(.article-image-block.align-left) {
    float: left;
    margin-right: 28px;
    margin-bottom: 20px;
    clear: left;
}

.article-body-content :deep(.article-image-block.align-right) {
    float: right;
    margin-left: 28px;
    margin-bottom: 20px;
    clear: right;
}

.article-body-content :deep(.article-image-caption) {
    font-size: 13px;
    color: #64748b;
    margin-top: 8px;
    text-align: center;
    font-style: italic;
    line-height: 1.5;
}

.article-body-content :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 14px;
    margin: 24px auto;
    display: block;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
}

/* Gallery Grid */
.article-body-content :deep(.article-gallery-grid) {
    display: grid;
    gap: 14px;
    margin: 28px 0;
    border-radius: 14px;
    overflow: hidden;
}

.article-body-content :deep(.article-gallery-grid.cols-1) {
    grid-template-columns: 1fr;
}

.article-body-content :deep(.article-gallery-grid.cols-2) {
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
}

.article-body-content :deep(.article-gallery-grid.cols-3) {
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
}

.article-body-content :deep(.gallery-photo-item) {
    aspect-ratio: 4 / 3;
    overflow: hidden;
    border-radius: 12px;
    background: #f1f5f9;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
}

.article-body-content :deep(.gallery-photo-item img) {
    width: 100%;
    height: 100%;
    object-fit: cover;
    margin: 0 !important;
    border-radius: 12px;
    display: block;
    transition: transform 0.3s ease;
}

.article-body-content :deep(.gallery-photo-item img:hover) {
    transform: scale(1.04);
}

/* Tags Section */
.article-tags-section {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 0;
    border-top: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 32px;
    flex-wrap: wrap;
}

.tags-header {
    display: inline-flex;
    align-items: center;
}

.tags-title {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
}

.tags-cloud {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.topic-tag {
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 6px;
    transition: all 0.15s ease;
}

.topic-tag:hover {
    background: #e6f4ea;
    color: #008744;
}

/* Bottom Share Card */
.bottom-share-card {
    background: linear-gradient(135deg, #0b2452 0%, #008744 100%);
    border-radius: 14px;
    padding: 24px;
    color: #ffffff;
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 32px;
}

@media (min-width: 640px) {
    .bottom-share-card {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.bottom-share-title {
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 4px 0;
}

.bottom-share-desc {
    font-size: 12px;
    color: #e2e8f0;
    margin: 0;
}

.bottom-share-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.action-share-btn {
    display: inline-flex;
    align-items: center;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.15s ease;
}

.btn-wa {
    background: #25d366;
    color: #ffffff;
}
.btn-wa:hover {
    background: #1ebc57;
}

.btn-fb {
    background: #1877f2;
    color: #ffffff;
}
.btn-fb:hover {
    background: #1466d2;
}

.btn-copy {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    backdrop-filter: blur(4px);
}
.btn-copy:hover {
    background: rgba(255, 255, 255, 0.35);
}

/* Editorial Attribution Card */
.editorial-author-card {
    display: flex;
    gap: 18px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 22px;
    align-items: flex-start;
}

.editorial-emblem {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.emblem-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.editorial-bio {
    flex: 1;
}

.editorial-role-badge {
    display: inline-block;
    background: #e6f4ea;
    color: #008744;
    font-size: 9.5px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 4px;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.editorial-name {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 6px 0;
}

.editorial-desc {
    font-size: 12px;
    color: #64748b;
    line-height: 1.5;
    margin: 0;
}

/* ── 3. Rekomendasi Berita Lainnya Grid ── */
.related-stories-section {
    margin-top: 40px;
}

.section-title-strip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e2e8f0;
}

.title-accent-pill {
    width: 4px;
    height: 20px;
    background: #008744;
    border-radius: 2px;
}

.section-heading {
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}

.see-all-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 700;
    color: #008744;
    text-decoration: none;
    transition: all 0.15s ease;
}

.see-all-link:hover {
    color: #0b2452;
    transform: translateX(2px);
}

.related-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}

@media (min-width: 640px) {
    .related-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .related-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.related-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all 0.2s ease;
}

.related-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    border-color: #86efac;
}

.related-thumb-wrap {
    position: relative;
    width: 100%;
    height: 140px;
    overflow: hidden;
    background: #0f172a;
}

.related-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.related-card:hover .related-thumb {
    transform: scale(1.05);
}

.related-cat-tag {
    position: absolute;
    bottom: 8px;
    left: 8px;
    background: rgba(0, 135, 68, 0.9);
    color: #ffffff;
    font-size: 9.5px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 4px;
    backdrop-filter: blur(4px);
}

.related-card-body {
    padding: 14px 14px 16px;
    display: flex;
    flex-direction: column;
    flex: 1;
    justify-content: space-between;
}

.related-title-link {
    text-decoration: none;
}

.related-card-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.4;
    margin: 0 0 10px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.15s ease;
}

.related-card-title:hover {
    color: #008744;
}

.related-meta {
    font-size: 10.5px;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ── 4. Right Sidebar Widgets ── */
.article-sidebar-col {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.sidebar-widget {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}

.widget-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
    padding-bottom: 12px;
    border-bottom: 2px solid #008744;
}

.widget-title-wrap {
    display: flex;
    align-items: center;
}

.widget-title {
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.widget-badge {
    background: #fef3c7;
    color: #d97706;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
    text-transform: uppercase;
}

/* Popular Stories List */
.popular-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.popular-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding-bottom: 12px;
    border-bottom: 1px solid #f1f5f9;
}

.popular-item:last-child {
    padding-bottom: 0;
    border-bottom: none;
}

.popular-rank-badge {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 12px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.popular-rank-badge.rank-1 {
    background: #f59e0b;
    color: #ffffff;
}
.popular-rank-badge.rank-2 {
    background: #008744;
    color: #ffffff;
}
.popular-rank-badge.rank-3 {
    background: #0284c7;
    color: #ffffff;
}

.popular-content {
    flex: 1;
}

.popular-cat {
    font-size: 9.5px;
    font-weight: 700;
    color: #008744;
    text-transform: uppercase;
    display: block;
    margin-bottom: 2px;
}

.popular-title-link {
    text-decoration: none;
}

.popular-title {
    font-size: 12.5px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.35;
    margin: 0 0 4px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.15s ease;
}

.popular-title:hover {
    color: #008744;
}

.popular-meta {
    font-size: 10.5px;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Prayer Schedule Widget */
.prayer-loc-pill {
    background: #e6f4ea;
    color: #008744;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 4px;
}

.prayer-sidebar-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    margin-bottom: 12px;
}

.prayer-item-box {
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 8px;
    padding: 8px 10px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.prayer-icon-row {
    display: flex;
    align-items: center;
    gap: 6px;
}

.prayer-name {
    font-size: 11.5px;
    font-weight: 600;
    color: #334155;
}

.prayer-time {
    font-size: 13.5px;
    font-weight: 800;
    color: #008744;
}

.prayer-footer-note {
    font-size: 10px;
    color: #94a3b8;
    text-align: center;
    border-top: 1px dashed #e2e8f0;
    padding-top: 8px;
}

/* Categories List Widget */
.categories-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.cat-list-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    border-radius: 8px;
    background: #f8fafc;
    text-decoration: none;
    transition: all 0.15s ease;
}

.cat-list-row:hover {
    background: #e6f4ea;
    padding-left: 16px;
}

.cat-row-name {
    font-size: 12px;
    font-weight: 600;
    color: #334155;
}

.cat-list-row:hover .cat-row-name {
    color: #008744;
}

.cat-row-count {
    font-size: 10.5px;
    font-weight: 700;
    background: #ffffff;
    color: #64748b;
    padding: 2px 7px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}

/* Call To Action Sidebar Card */
.cta-card-widget {
    background: linear-gradient(135deg, #0b2452 0%, #008744 100%);
    color: #ffffff;
    padding: 24px;
    border: none;
}

.cta-card-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.cta-logo-wrap {
    width: 64px;
    height: 64px;
    margin-bottom: 12px;
}

.cta-logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.3));
}

.cta-title {
    font-size: 16px;
    font-weight: 800;
    margin: 0 0 6px 0;
}

.cta-desc {
    font-size: 11.5px;
    color: #e2e8f0;
    line-height: 1.5;
    margin: 0 0 14px 0;
}

.cta-contact-row {
    display: flex;
    align-items: center;
    font-size: 11.5px;
    color: #f1f5f9;
    margin-bottom: 6px;
}

.cta-wa-btn {
    margin-top: 14px;
    background: #25d366;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    padding: 9px 18px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
}

.cta-wa-btn:hover {
    background: #1ebc57;
    transform: translateY(-2px);
}
</style>
