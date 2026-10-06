<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    metrics: {
        type: Object,
        default: () => ({
            onlineVisitors: 0,
            todayPageviews: 0,
            todayUnique: 0,
            yesterdayPageviews: 0,
            yesterdayUnique: 0,
            growthToday: 0,
            weekPageviews: 0,
            weekUnique: 0,
            monthPageviews: 0,
            totalPageviews: 0,
            totalUnique: 0,
            timestamp: '',
        }),
    },
    period: {
        type: Object,
        default: () => ({
            key: 'last_7_days',
            label: '7 Hari Terakhir',
            rangeLabel: '',
            compareLabel: '',
            granularity: 'day',
            start: '',
            end: '',
            options: [],
        }),
    },
    periodMetrics: {
        type: Object,
        default: () => ({
            pageviews: 0,
            unique: 0,
            prevPageviews: 0,
            prevUnique: 0,
            pageviewsGrowth: 0,
            uniqueGrowth: 0,
            avgPerDay: 0,
            pagesPerVisitor: 0,
        }),
    },
    dailyTrend: { type: Array, default: () => [] },
    hourlyToday: { type: Array, default: () => [] },
    topPages: { type: Array, default: () => [] },
    deviceStats: { type: Object, default: () => ({}) },
    browserStats: { type: Array, default: () => [] },
    referrerStats: { type: Array, default: () => [] },
    provinceStats: { type: Array, default: () => [] },
    countryStats: { type: Array, default: () => [] },
    cityStats: { type: Array, default: () => [] },
    recentVisits: { type: Array, default: () => [] },
});

// Reactive state for real-time live updates
const liveMetrics = ref({ ...props.metrics });
const liveRecentVisits = ref([...props.recentVisits]);
const isAutoRefresh = ref(true);
const isRefreshing = ref(false);
const lastUpdatedTime = ref(props.metrics?.timestamp ? `${props.metrics.timestamp} WIB` : 'Baru saja');
const activeChartMetric = ref('pageviews'); // 'pageviews' or 'unique'
const hoveredPoint = ref(null);

// Reactive state for period filtering
const selectedPeriod = ref(props.period?.key || 'last_7_days');
const customStart = ref(props.period?.start || '');
const customEnd = ref(props.period?.end || '');
const showCustomPicker = ref(props.period?.key === 'custom');
const isChangingPeriod = ref(false);

watch(() => props.period, (newP) => {
    if (newP) {
        selectedPeriod.value = newP.key || 'last_7_days';
        customStart.value = newP.start || '';
        customEnd.value = newP.end || '';
        showCustomPicker.value = newP.key === 'custom';
    }
}, { deep: true });

const changePeriod = (key) => {
    selectedPeriod.value = key;
    if (key === 'custom') {
        showCustomPicker.value = true;
        return;
    }
    showCustomPicker.value = false;
    applyFilter({ period: key });
};

const applyCustomRange = () => {
    if (!customStart.value || !customEnd.value) return;
    applyFilter({
        period: 'custom',
        start: customStart.value,
        end: customEnd.value,
    });
};

const applyFilter = (params) => {
    isChangingPeriod.value = true;
    router.get('/dashboard/monitoring', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => {
            isChangingPeriod.value = false;
        },
    });
};

const shouldShowXLabel = (idx, total) => {
    if (total <= 14) return true;
    if (total <= 25) return idx % 3 === 0 || idx === total - 1;
    if (total <= 40) return idx % 4 === 0 || idx === total - 1;
    return idx % 6 === 0 || idx === total - 1;
};

let pollTimer = null;

const formatNumber = (num) => {
    if (!num) return '0';
    return Number(num).toLocaleString('id-ID');
};

const formatCompact = (num) => {
    if (!num) return '0';
    if (num >= 1000000) return (num / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
    return Number(num).toLocaleString('id-ID');
};

// ── SVG Curved Area Chart Calculations ──
const chartWidth = 720;
const chartHeight = 220;
const chartPadding = { top: 20, right: 24, bottom: 32, left: 36 };

const maxTrendValue = computed(() => {
    const key = activeChartMetric.value;
    const values = props.dailyTrend.map((d) => d[key] || 0);
    const max = Math.max(...values, 10);
    // Round up to nearest nice integer
    return Math.ceil(max * 1.15);
});

const chartPoints = computed(() => {
    const data = props.dailyTrend;
    if (!data.length) return [];

    const innerW = chartWidth - chartPadding.left - chartPadding.right;
    const innerH = chartHeight - chartPadding.top - chartPadding.bottom;
    const maxVal = maxTrendValue.value;

    if (data.length === 1) {
        const val = data[0][activeChartMetric.value] || 0;
        const x = chartPadding.left + innerW / 2;
        const y = chartPadding.top + innerH - (val / maxVal) * innerH;
        return [{
            x,
            y,
            val,
            label: data[0].label,
            day: data[0].day,
            date: data[0].date,
            isToday: data[0].isToday,
            pageviews: data[0].pageviews,
            unique: data[0].unique,
        }];
    }

    const stepX = innerW / Math.max(data.length - 1, 1);

    return data.map((item, idx) => {
        const val = item[activeChartMetric.value] || 0;
        const x = chartPadding.left + idx * stepX;
        const y = chartPadding.top + innerH - (val / maxVal) * innerH;
        return {
            x,
            y,
            val,
            label: item.label,
            day: item.day,
            date: item.date,
            isToday: item.isToday,
            pageviews: item.pageviews,
            unique: item.unique,
        };
    });
});

// Generate smooth cubic bezier SVG path
const linePath = computed(() => {
    const pts = chartPoints.value;
    if (!pts.length) return '';
    if (pts.length === 1) return `M ${pts[0].x} ${pts[0].y}`;

    let d = `M ${pts[0].x} ${pts[0].y}`;
    for (let i = 0; i < pts.length - 1; i++) {
        const p0 = pts[i];
        const p1 = pts[i + 1];
        const cpX = (p0.x + p1.x) / 2;
        d += ` C ${cpX} ${p0.y}, ${cpX} ${p1.y}, ${p1.x} ${p1.y}`;
    }
    return d;
});

const areaPath = computed(() => {
    const pts = chartPoints.value;
    if (pts.length <= 1) return '';
    const bottomY = chartHeight - chartPadding.bottom;
    const firstX = pts[0].x;
    const lastX = pts[pts.length - 1].x;
    return `${linePath.value} L ${lastX} ${bottomY} L ${firstX} ${bottomY} Z`;
});

// ── Hourly Peak Chart Calculations ──
const maxHourlyVisits = computed(() => {
    const vals = props.hourlyToday.map((h) => h.visits || 0);
    return Math.max(...vals, 5);
});

// ── Live Polling Function ──
const fetchLiveStats = async () => {
    if (isRefreshing.value) return;
    isRefreshing.value = true;
    try {
        const res = await axios.get('/dashboard/monitoring/live');
        if (res.data) {
            liveMetrics.value.onlineVisitors = res.data.onlineVisitors;
            liveMetrics.value.todayPageviews = res.data.todayPageviews;
            liveMetrics.value.todayUnique = res.data.todayUnique;
            liveMetrics.value.totalPageviews = res.data.totalPageviews;
            if (Array.isArray(res.data.recentVisits)) {
                liveRecentVisits.value = res.data.recentVisits;
            }
            lastUpdatedTime.value = res.data.timestamp + ' WIB';
        }
    } catch {
        // Silently tolerate temporary network glitch
    } finally {
        setTimeout(() => {
            isRefreshing.value = false;
        }, 400);
    }
};

const toggleAutoRefresh = () => {
    isAutoRefresh.value = !isAutoRefresh.value;
    if (isAutoRefresh.value) {
        startPolling();
        fetchLiveStats();
    } else {
        stopPolling();
    }
};

const startPolling = () => {
    stopPolling();
    pollTimer = setInterval(fetchLiveStats, 10000); // 10s poll
};

const stopPolling = () => {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
};

onMounted(() => {
    startPolling();
});

onUnmounted(() => {
    stopPolling();
});

const getDeviceIcon = (device) => {
    switch (device) {
        case 'mobile':
            return 'mdi-cellphone';
        case 'tablet':
            return 'mdi-tablet';
        default:
            return 'mdi-laptop';
    }
};

const getReferrerBadgeClass = (type) => {
    switch (type) {
        case 'whatsapp':
            return 'ref-badge--whatsapp';
        case 'google':
            return 'ref-badge--google';
        case 'facebook':
            return 'ref-badge--facebook';
        case 'instagram':
            return 'ref-badge--instagram';
        case 'direct':
            return 'ref-badge--direct';
        default:
            return 'ref-badge--other';
    }
};
</script>

<template>
    <Head title="Monitoring Pengunjung - Admin PCM Simo" />

    <div class="m3-page-container">
        <!-- ── Material 3 Top Page Header ── -->
        <header class="m3-header">
            <div class="m3-header-main">
                <div class="m3-header-titles">
                    <div class="d-flex align-center ga-2 mb-1 flex-wrap">
                        <span class="m3-header-badge m3-header-badge--live">
                            <span class="live-pulse-radar"></span>
                            Live Traffic Monitoring
                        </span>
                        <span class="m3-header-badge m3-header-badge--count">
                            Diperbarui: {{ lastUpdatedTime }}
                        </span>
                    </div>
                    <h1 class="m3-headline">Monitoring &amp; Statistik Kunjungan</h1>
                    <p class="m3-subhead">
                        Pantau aktivitas pengunjung website, pembaca dakwah online, dan sebaran trafik secara langsung.
                    </p>
                </div>

                <div class="m3-header-actions">
                    <!-- Toggle Auto Refresh -->
                    <button
                        type="button"
                        class="m3-btn-tonal"
                        :class="{ 'm3-btn-tonal--active': isAutoRefresh }"
                        @click="toggleAutoRefresh"
                        :title="isAutoRefresh ? 'Jeda pembaruan otomatis' : 'Aktifkan pembaruan otomatis'"
                    >
                        <v-icon size="16" class="mr-1.5" :class="{ 'spin-slow': isAutoRefresh }">
                            mdi-autorenew
                        </v-icon>
                        <span>Auto-Refresh: {{ isAutoRefresh ? '10 dtk' : 'Mati' }}</span>
                    </button>

                    <!-- Manual Refresh -->
                    <button
                        type="button"
                        class="m3-btn-tonal"
                        :disabled="isRefreshing"
                        @click="fetchLiveStats"
                        title="Perbarui data sekarang"
                    >
                        <v-progress-circular
                            v-if="isRefreshing"
                            indeterminate
                            size="14"
                            width="2"
                            color="#006837"
                            class="mr-1.5"
                        />
                        <v-icon v-else size="16" class="mr-1.5">mdi-refresh</v-icon>
                        <span>Segarkan</span>
                    </button>

                    <!-- Buka Website Publik -->
                    <a href="/" target="_blank" class="text-decoration-none">
                        <button class="m3-fab-extended">
                            <v-icon size="18" class="mr-1.5">mdi-open-in-new</v-icon>
                            <span>Buka Portal Publik</span>
                        </button>
                    </a>
                </div>
            </div>
        </header>

        <!-- ── Key KPI Metrics Grid ── -->
        <section class="m3-metrics-grid" aria-label="Statistik Real-time">
            <!-- Hero Card: Pengunjung Online Saat Ini -->
            <div class="m3-metric-card hero-online-card">
                <div class="m3-metric-top">
                    <div class="m3-metric-icon hero-online-icon">
                        <v-icon size="24" color="#006837">mdi-access-point-network</v-icon>
                    </div>
                    <span class="m3-metric-active-pill">
                        <span class="m3-pulse-dot"></span>
                        Aktif Sekarang
                    </span>
                </div>
                <div class="m3-metric-body">
                    <div class="m3-metric-value text-emerald-800">
                        {{ formatNumber(liveMetrics.onlineVisitors) }}
                    </div>
                    <div class="m3-metric-label">PENGUNJUNG ONLINE LIVE</div>
                </div>
                <div class="m3-metric-footer">
                    <span>Sedang aktif dalam 5 menit terakhir</span>
                    <v-icon size="14" class="text-emerald-700">mdi-radiobox-marked</v-icon>
                </div>
            </div>

            <!-- Card 2: Kunjungan Hari Ini -->
            <div class="m3-metric-card" style="--m3-accent: #0284c7; --m3-container: #f0f9ff;">
                <div class="m3-metric-top">
                    <div class="m3-metric-icon">
                        <v-icon size="22" color="#0284c7">mdi-calendar-today</v-icon>
                    </div>
                    <span v-if="liveMetrics.growthToday !== 0" :class="['growth-pill', liveMetrics.growthToday > 0 ? 'growth--up' : 'growth--down']">
                        <v-icon size="12">{{ liveMetrics.growthToday > 0 ? 'mdi-arrow-up-bold' : 'mdi-arrow-down-bold' }}</v-icon>
                        {{ Math.abs(liveMetrics.growthToday) }}%
                    </span>
                </div>
                <div class="m3-metric-body">
                    <div class="m3-metric-value">{{ formatCompact(liveMetrics.todayPageviews) }}</div>
                    <div class="m3-metric-label">TAYANGAN HARI INI</div>
                </div>
                <div class="m3-metric-footer">
                    <span><strong>{{ formatNumber(liveMetrics.todayUnique) }}</strong> pengunjung unik</span>
                    <v-icon size="14" class="m3-metric-footer-icon">mdi-account-outline</v-icon>
                </div>
            </div>

            <!-- Card 3: Kunjungan Minggu Ini -->
            <div class="m3-metric-card" style="--m3-accent: #7c3aed; --m3-container: #f5f3ff;">
                <div class="m3-metric-top">
                    <div class="m3-metric-icon">
                        <v-icon size="22" color="#7c3aed">mdi-calendar-week-outline</v-icon>
                    </div>
                    <v-icon size="16" class="m3-metric-arrow">mdi-chart-line</v-icon>
                </div>
                <div class="m3-metric-body">
                    <div class="m3-metric-value">{{ formatCompact(liveMetrics.weekPageviews) }}</div>
                    <div class="m3-metric-label">MINGGU INI</div>
                </div>
                <div class="m3-metric-footer">
                    <span><strong>{{ formatNumber(liveMetrics.weekUnique) }}</strong> pengunjung unik</span>
                    <v-icon size="14" class="m3-metric-footer-icon">mdi-chevron-right</v-icon>
                </div>
            </div>

            <!-- Card 4: Total Akumulasi -->
            <div class="m3-metric-card" style="--m3-accent: #ea580c; --m3-container: #fff7ed;">
                <div class="m3-metric-top">
                    <div class="m3-metric-icon">
                        <v-icon size="22" color="#ea580c">mdi-database-eye-outline</v-icon>
                    </div>
                    <v-icon size="16" class="m3-metric-arrow">mdi-arrow-top-right</v-icon>
                </div>
                <div class="m3-metric-body">
                    <div class="m3-metric-value">{{ formatCompact(liveMetrics.totalPageviews) }}</div>
                    <div class="m3-metric-label">TOTAL AKUMULASI</div>
                </div>
                <div class="m3-metric-footer">
                    <span><strong>{{ formatCompact(liveMetrics.totalUnique) }}</strong> total pembaca unik</span>
                    <v-icon size="14" class="m3-metric-footer-icon">mdi-all-inclusive</v-icon>
                </div>
            </div>
        </section>

        <!-- ── Filter Periode & Analisis Rentang Waktu ── -->
        <section class="period-filter-section mb-6" aria-label="Filter Periode Analisis">
            <div class="m3-table-card period-card">
                <div class="period-card-header">
                    <div class="d-flex align-center ga-3 flex-wrap">
                        <div class="header-icon-box" style="background: #ecfdf5; color: #006837;">
                            <v-icon size="20">mdi-calendar-clock</v-icon>
                        </div>
                        <div>
                            <div class="d-flex align-center ga-2">
                                <h2 class="period-section-title">Filter Periode Analisis</h2>
                                <span v-if="isChangingPeriod" class="period-loading-tag">
                                    <v-progress-circular indeterminate size="12" width="2" color="#006837" class="mr-1" />
                                    Memuat data...
                                </span>
                            </div>
                            <p class="period-section-subtitle">
                                Pilih rentang waktu untuk memfilter grafik tren, performa konten, dan zonasi pengunjung
                            </p>
                        </div>
                    </div>

                    <!-- Range Badge & Compare Tag -->
                    <div class="period-info-badge">
                        <v-icon size="14" class="text-emerald-700 mr-1.5">mdi-calendar-range</v-icon>
                        <span class="font-bold text-slate-800">{{ period.rangeLabel || period.label }}</span>
                        <span class="period-compare-pill">Bandingkan: {{ period.compareLabel }}</span>
                    </div>
                </div>

                <!-- Quick Period Filter Pills -->
                <div class="period-pills-bar">
                    <button
                        v-for="opt in (period.options || [])"
                        :key="opt.key"
                        type="button"
                        class="period-pill-btn"
                        :class="{ 'period-pill-btn--active': selectedPeriod === opt.key }"
                        :disabled="isChangingPeriod"
                        @click="changePeriod(opt.key)"
                    >
                        <span>{{ opt.label }}</span>
                    </button>
                </div>

                <!-- Custom Date Range Expandable Bar -->
                <transition name="expand">
                    <div v-if="showCustomPicker" class="custom-range-bar">
                        <div class="d-flex align-center ga-2">
                            <v-icon size="16" color="#006837">mdi-tune-variant</v-icon>
                            <span class="text-xs font-semibold text-slate-700">Tentukan Rentang Tanggal Kustom:</span>
                        </div>

                        <div class="custom-range-inputs">
                            <div class="date-input-group">
                                <label for="start-date" class="date-label">Dari</label>
                                <input
                                    id="start-date"
                                    v-model="customStart"
                                    type="date"
                                    class="m3-date-input"
                                    :max="customEnd || undefined"
                                />
                            </div>

                            <span class="date-sep">—</span>

                            <div class="date-input-group">
                                <label for="end-date" class="date-label">Sampai</label>
                                <input
                                    id="end-date"
                                    v-model="customEnd"
                                    type="date"
                                    class="m3-date-input"
                                    :min="customStart || undefined"
                                />
                            </div>

                            <button
                                type="button"
                                class="m3-btn-apply"
                                :disabled="!customStart || !customEnd || isChangingPeriod"
                                @click="applyCustomRange"
                            >
                                <v-icon size="14" class="mr-1">mdi-filter-check</v-icon>
                                <span>Terapkan Rentang</span>
                            </button>
                        </div>
                    </div>
                </transition>

                <!-- Comparative Metrics Banner (Performa Periode Terpilih) -->
                <div class="period-metrics-banner">
                    <!-- Metric 1: Tayangan Periode Ini -->
                    <div class="period-metric-col">
                        <div class="metric-col-top">
                            <span class="metric-col-label">TAYANGAN HALAMAN</span>
                            <span
                                :class="[
                                    'growth-pill',
                                    periodMetrics.pageviewsGrowth > 0 ? 'growth--up' : (periodMetrics.pageviewsGrowth < 0 ? 'growth--down' : 'growth--neutral')
                                ]"
                            >
                                <v-icon size="11">
                                    {{ periodMetrics.pageviewsGrowth > 0 ? 'mdi-arrow-up-bold' : (periodMetrics.pageviewsGrowth < 0 ? 'mdi-arrow-down-bold' : 'mdi-minus') }}
                                </v-icon>
                                {{ periodMetrics.pageviewsGrowth > 0 ? '+' : '' }}{{ periodMetrics.pageviewsGrowth }}%
                            </span>
                        </div>
                        <div class="metric-col-val text-slate-900">
                            {{ formatNumber(periodMetrics.pageviews) }}
                        </div>
                        <div class="metric-col-sub">
                            vs <strong>{{ formatNumber(periodMetrics.prevPageviews) }}</strong> ({{ period.compareLabel }})
                        </div>
                    </div>

                    <!-- Metric 2: Pengunjung Unik Periode Ini -->
                    <div class="period-metric-col">
                        <div class="metric-col-top">
                            <span class="metric-col-label">PENGUNJUNG UNIK</span>
                            <span
                                :class="[
                                    'growth-pill',
                                    periodMetrics.uniqueGrowth > 0 ? 'growth--up' : (periodMetrics.uniqueGrowth < 0 ? 'growth--down' : 'growth--neutral')
                                ]"
                            >
                                <v-icon size="11">
                                    {{ periodMetrics.uniqueGrowth > 0 ? 'mdi-arrow-up-bold' : (periodMetrics.uniqueGrowth < 0 ? 'mdi-arrow-down-bold' : 'mdi-minus') }}
                                </v-icon>
                                {{ periodMetrics.uniqueGrowth > 0 ? '+' : '' }}{{ periodMetrics.uniqueGrowth }}%
                            </span>
                        </div>
                        <div class="metric-col-val text-emerald-800">
                            {{ formatNumber(periodMetrics.unique) }}
                        </div>
                        <div class="metric-col-sub">
                            vs <strong>{{ formatNumber(periodMetrics.prevUnique) }}</strong> ({{ period.compareLabel }})
                        </div>
                    </div>

                    <!-- Metric 3: Rata-Rata Tayangan / Hari -->
                    <div class="period-metric-col">
                        <div class="metric-col-top">
                            <span class="metric-col-label">RATA-RATA / HARI</span>
                            <v-icon size="15" color="#0284c7">mdi-chart-line</v-icon>
                        </div>
                        <div class="metric-col-val text-sky-800">
                            {{ formatNumber(periodMetrics.avgPerDay) }}
                        </div>
                        <div class="metric-col-sub">
                            Tayangan rata-rata per hari aktif
                        </div>
                    </div>

                    <!-- Metric 4: Kedalaman Baca (Pages / Visitor) -->
                    <div class="period-metric-col">
                        <div class="metric-col-top">
                            <span class="metric-col-label">HALAMAN / PEMBACA</span>
                            <v-icon size="15" color="#7c3aed">mdi-book-open-page-variant-outline</v-icon>
                        </div>
                        <div class="metric-col-val text-purple-800">
                            {{ periodMetrics.pagesPerVisitor }}
                        </div>
                        <div class="metric-col-sub">
                            Rasio tayangan per pengunjung unik
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Row: Trend Chart & Hourly Distribution ── -->
        <div class="analytics-row mb-6">
            <!-- 14 Days Interactive Trend Chart Card -->
            <div class="m3-table-card chart-card flex-2">
                <div class="m3-table-header">
                    <div>
                        <div class="d-flex align-center ga-2 mb-1">
                            <span class="m3-header-badge">
                                <v-icon size="13" class="mr-1 text-emerald-700">mdi-chart-areaspline</v-icon>
                                Tren Kunjungan · {{ period.label }}
                            </span>
                        </div>
                        <h2 class="m3-table-title">Dinamika Kunjungan {{ period.label }}</h2>
                        <p class="m3-table-subtitle">Rentang {{ period.rangeLabel }} (Tampilan {{ period.granularity === 'hour' ? 'Per Jam' : (period.granularity === 'day' ? 'Per Hari' : 'Per Bulan') }})</p>
                    </div>

                    <!-- Metric Toggle Segmented Pill -->
                    <div class="segmented-control">
                        <button
                            type="button"
                            class="segmented-btn"
                            :class="{ 'segmented-btn--active': activeChartMetric === 'pageviews' }"
                            @click="activeChartMetric = 'pageviews'"
                        >
                            <span>Total Tayangan</span>
                        </button>
                        <button
                            type="button"
                            class="segmented-btn"
                            :class="{ 'segmented-btn--active': activeChartMetric === 'unique' }"
                            @click="activeChartMetric = 'unique'"
                        >
                            <span>Pengunjung Unik</span>
                        </button>
                    </div>
                </div>

                <!-- SVG Curved Chart Container -->
                <div class="svg-chart-container">
                    <svg
                        class="svg-chart"
                        :viewBox="`0 0 ${chartWidth} ${chartHeight}`"
                        preserveAspectRatio="none"
                    >
                        <defs>
                            <linearGradient id="m3EmeraldGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#006837" stop-opacity="0.25" />
                                <stop offset="100%" stop-color="#006837" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>

                        <!-- Horizontal Grid Lines -->
                        <line
                            v-for="line in [0.25, 0.5, 0.75, 1]"
                            :key="line"
                            :x1="chartPadding.left"
                            :y1="chartPadding.top + (chartHeight - chartPadding.top - chartPadding.bottom) * (1 - line)"
                            :x2="chartWidth - chartPadding.right"
                            :y2="chartPadding.top + (chartHeight - chartPadding.top - chartPadding.bottom) * (1 - line)"
                            stroke="#f1f5f9"
                            stroke-dasharray="3 3"
                            stroke-width="1"
                        />

                        <!-- Gradient Area -->
                        <path :d="areaPath" fill="url(#m3EmeraldGradient)" />

                        <!-- Smooth Line -->
                        <path
                            :d="linePath"
                            fill="none"
                            stroke="#006837"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                        <!-- Data Points -->
                        <g v-for="(pt, idx) in chartPoints" :key="idx">
                            <!-- Outer hover halo -->
                            <circle
                                :cx="pt.x"
                                :cy="pt.y"
                                :r="hoveredPoint === pt ? 7 : (pt.isToday ? 5 : 3.5)"
                                :fill="pt.isToday ? '#006837' : '#ffffff'"
                                :stroke="pt.isToday ? '#ffffff' : '#006837'"
                                stroke-width="2"
                                class="chart-point"
                                @mouseenter="hoveredPoint = pt"
                                @mouseleave="hoveredPoint = null"
                            />
                        </g>
                    </svg>

                    <!-- Bottom X-Axis Labels -->
                    <div class="chart-x-labels">
                        <template v-for="(pt, idx) in chartPoints" :key="idx">
                            <span
                                v-if="shouldShowXLabel(idx, chartPoints.length)"
                                :class="['x-label', { 'x-label--today': pt.isToday }]"
                                :style="{ left: `${(pt.x / chartWidth) * 100}%` }"
                            >
                                {{ pt.day }}
                            </span>
                        </template>
                    </div>

                    <!-- Interactive Tooltip -->
                    <div
                        v-if="hoveredPoint"
                        class="chart-tooltip"
                        :style="{
                            left: `${(hoveredPoint.x / chartWidth) * 100}%`,
                            top: `${(hoveredPoint.y / chartHeight) * 100}%`
                        }"
                    >
                        <div class="tooltip-date">{{ hoveredPoint.label }} ({{ hoveredPoint.day }})</div>
                        <div class="tooltip-val">
                            <span class="tooltip-dot"></span>
                            <span>{{ hoveredPoint.pageviews }} Tayangan</span>
                        </div>
                        <div class="tooltip-sub">
                            {{ hoveredPoint.unique }} Pengunjung Unik
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hourly Peak Chart Today (Right) -->
            <div class="m3-table-card hourly-card flex-1">
                <div class="m3-table-header">
                    <div>
                        <div class="d-flex align-center ga-2 mb-1">
                            <span class="m3-header-badge">
                                <v-icon size="13" class="mr-1 text-emerald-700">mdi-clock-time-four-outline</v-icon>
                                Distribusi Jam
                            </span>
                        </div>
                        <h2 class="m3-table-title">Pola Jam Kunjungan</h2>
                        <p class="m3-table-subtitle">Akumulasi jam ramai (00:00 - 23:00) pada {{ period.label }}</p>
                    </div>
                </div>

                <div class="hourly-chart-body">
                    <div class="hourly-bars-track">
                        <div
                            v-for="h in hourlyToday"
                            :key="h.hour"
                            class="hourly-bar-col"
                            :title="`${h.label}: ${h.visits} kunjungan`"
                        >
                            <div class="hourly-bar-fill-wrap">
                                <div
                                    class="hourly-bar-fill"
                                    :class="{ 'hourly-bar-fill--current': h.isCurrent }"
                                    :style="{ height: `${Math.max((h.visits / maxHourlyVisits) * 100, h.visits > 0 ? 8 : 2)}%` }"
                                ></div>
                            </div>
                            <span v-if="h.hour % 4 === 0 || h.hour === 23" class="hourly-bar-label">
                                {{ h.hour }}
                            </span>
                        </div>
                    </div>
                    <div class="hourly-footer-legend">
                        <span>Pukul 00:00</span>
                        <span class="d-flex align-center ga-1 text-emerald-700 font-semibold">
                            <span class="dot-green"></span> Jam Ini
                        </span>
                        <span>Pukul 23:00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Row: Top Pages & Audience Segmentation ── -->
        <div class="analytics-row mb-6">
            <!-- Left: Top Visited Pages & Articles -->
            <div class="m3-table-card flex-2">
                <div class="m3-table-header">
                    <div>
                        <div class="d-flex align-center ga-2 mb-1">
                            <span class="m3-header-badge">
                                <v-icon size="13" class="mr-1 text-emerald-700">mdi-star-outline</v-icon>
                                Paling Banyak Dibaca
                            </span>
                        </div>
                        <h2 class="m3-table-title">Halaman &amp; Berita Terpopuler</h2>
                        <p class="m3-table-subtitle">Periode {{ period.label }} ({{ period.rangeLabel }}) berdasarkan akumulasi pembaca</p>
                    </div>
                </div>

                <div class="table-scroll">
                    <table class="recent-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Halaman / Artikel Warta</th>
                                <th style="width: 140px;" class="text-right">Tayangan</th>
                                <th style="width: 120px;" class="text-right">Unik</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(page, idx) in topPages" :key="page.path" class="table-row">
                                <td>
                                    <span
                                        :class="[
                                            'rank-badge',
                                            idx === 0 ? 'rank-1' : (idx === 1 ? 'rank-2' : (idx === 2 ? 'rank-3' : 'rank-other'))
                                        ]"
                                    >
                                        {{ idx + 1 }}
                                    </span>
                                </td>
                                <td>
                                    <div class="title-cell">
                                        <a :href="page.path" target="_blank" class="article-title-link">
                                            {{ page.page_title || page.path }}
                                        </a>
                                        <span class="path-subtext text-truncate">{{ page.path }}</span>
                                    </div>
                                </td>
                                <td class="text-right font-semibold text-slate-800">
                                    {{ formatNumber(page.views_count) }}
                                </td>
                                <td class="text-right text-slate-500">
                                    {{ formatNumber(page.unique_count) }}
                                </td>
                            </tr>

                            <tr v-if="topPages.length === 0">
                                <td colspan="4" class="text-center py-8 text-slate-400 text-xs">
                                    Belum ada data kunjungan halaman yang tercatat.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: Audience Breakdown (Device, Browser, Referrer) -->
            <div class="audience-column flex-1">
                <!-- Card A: Perangkat -->
                <div class="m3-table-card mb-4">
                    <div class="sidebar-card-header">
                        <div class="d-flex align-center ga-2">
                            <div class="header-icon-box" style="background: #eff6ff; color: #2563eb;">
                                <v-icon size="18">mdi-devices</v-icon>
                            </div>
                            <div>
                                <h3 class="breakdown-title">Perangkat Pengunjung</h3>
                                <p class="breakdown-subtitle">Proporsi selama {{ period.label }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="breakdown-body">
                        <!-- Mobile -->
                        <div class="breakdown-item">
                            <div class="breakdown-item-top">
                                <span class="d-flex align-center ga-1.5 font-medium text-xs text-slate-700">
                                    <v-icon size="16" color="#006837">mdi-cellphone</v-icon>
                                    Smartphone (HP)
                                </span>
                                <span class="font-bold text-xs text-slate-900">
                                    {{ deviceStats.mobile?.percentage ?? 0 }}%
                                </span>
                            </div>
                            <div class="progress-bar-bg">
                                <div
                                    class="progress-bar-fill"
                                    style="background: #006837;"
                                    :style="{ width: `${deviceStats.mobile?.percentage ?? 0}%` }"
                                ></div>
                            </div>
                        </div>

                        <!-- Desktop -->
                        <div class="breakdown-item">
                            <div class="breakdown-item-top">
                                <span class="d-flex align-center ga-1.5 font-medium text-xs text-slate-700">
                                    <v-icon size="16" color="#2563eb">mdi-laptop</v-icon>
                                    Komputer / Laptop
                                </span>
                                <span class="font-bold text-xs text-slate-900">
                                    {{ deviceStats.desktop?.percentage ?? 0 }}%
                                </span>
                            </div>
                            <div class="progress-bar-bg">
                                <div
                                    class="progress-bar-fill"
                                    style="background: #2563eb;"
                                    :style="{ width: `${deviceStats.desktop?.percentage ?? 0}%` }"
                                ></div>
                            </div>
                        </div>

                        <!-- Tablet -->
                        <div class="breakdown-item mb-0">
                            <div class="breakdown-item-top">
                                <span class="d-flex align-center ga-1.5 font-medium text-xs text-slate-700">
                                    <v-icon size="16" color="#7c3aed">mdi-tablet</v-icon>
                                    Tablet (iPad)
                                </span>
                                <span class="font-bold text-xs text-slate-900">
                                    {{ deviceStats.tablet?.percentage ?? 0 }}%
                                </span>
                            </div>
                            <div class="progress-bar-bg">
                                <div
                                    class="progress-bar-fill"
                                    style="background: #7c3aed;"
                                    :style="{ width: `${deviceStats.tablet?.percentage ?? 0}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card B: Sumber Kunjungan (Referrer) -->
                <div class="m3-table-card">
                    <div class="sidebar-card-header">
                        <div class="d-flex align-center ga-2">
                            <div class="header-icon-box" style="background: #ecfdf5; color: #006837;">
                                <v-icon size="18">mdi-source-branch</v-icon>
                            </div>
                            <div>
                                <h3 class="breakdown-title">Sumber Trafik</h3>
                                <p class="breakdown-subtitle">Asal rujukan selama {{ period.label }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="breakdown-body">
                        <div
                            v-for="ref in referrerStats"
                            :key="ref.type"
                            class="referrer-row"
                        >
                            <div class="d-flex align-center ga-2">
                                <v-icon size="16" :color="ref.color">{{ ref.icon }}</v-icon>
                                <span class="text-xs font-medium text-slate-800">{{ ref.name }}</span>
                            </div>
                            <div class="d-flex align-center ga-2">
                                <span class="text-xs font-bold text-slate-700">{{ ref.percentage }}%</span>
                                <span class="text-[11px] text-slate-400">({{ formatNumber(ref.count) }})</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Row: Sebaran Geografis & Zonasi Pengunjung (Provinsi, Kota & Negara) ── -->
        <div class="m3-table-card mb-6">
            <div class="m3-table-header">
                <div>
                    <div class="d-flex align-center ga-2 mb-1">
                        <span class="m3-header-badge">
                            <v-icon size="13" class="mr-1 text-emerald-700">mdi-map-marker-radius-outline</v-icon>
                            Sebaran Geografis &amp; Zonasi
                        </span>
                    </div>
                    <h2 class="m3-table-title">Zonasi Asal Pengunjung &amp; Wilayah</h2>
                    <p class="m3-table-subtitle">Sebaran geografis selama {{ period.label }} ({{ period.rangeLabel }})</p>
                </div>
                <div class="d-flex align-center ga-2 flex-wrap">
                    <span class="m3-stat-tag">
                        <v-icon size="14" class="mr-1 text-emerald-700">mdi-flag-outline</v-icon>
                        {{ countryStats.length }} Negara Terdeteksi
                    </span>
                    <span class="m3-stat-tag">
                        <v-icon size="14" class="mr-1 text-blue-600">mdi-city-variant-outline</v-icon>
                        {{ provinceStats.length }} Provinsi
                    </span>
                </div>
            </div>

            <div class="geo-grid-layout">
                <!-- Col 1: Sebaran Provinsi di Indonesia -->
                <div class="geo-panel">
                    <div class="geo-panel-header">
                        <div class="d-flex align-center ga-2">
                            <div class="header-icon-box" style="background: #ecfdf5; color: #006837;">
                                <v-icon size="17">mdi-map-outline</v-icon>
                            </div>
                            <div>
                                <h3 class="breakdown-title">Provinsi di Indonesia</h3>
                                <p class="breakdown-subtitle">Sebaran pembaca nusantara</p>
                            </div>
                        </div>
                    </div>
                    <div class="geo-panel-body">
                        <div v-for="prov in provinceStats" :key="prov.province" class="geo-item">
                            <div class="geo-item-top">
                                <span class="d-flex align-center ga-1.5 font-medium text-xs text-slate-800">
                                    <span class="geo-dot" :style="{ background: prov.color }"></span>
                                    {{ prov.province }}
                                </span>
                                <div class="d-flex align-center ga-2">
                                    <span class="font-bold text-xs text-slate-900">{{ prov.percentage }}%</span>
                                    <span class="text-[11px] text-slate-400 font-mono">({{ formatNumber(prov.count) }})</span>
                                </div>
                            </div>
                            <div class="progress-bar-bg">
                                <div
                                    class="progress-bar-fill"
                                    :style="{ width: `${prov.percentage}%`, background: prov.color }"
                                ></div>
                            </div>
                        </div>

                        <div v-if="provinceStats.length === 0" class="text-center py-6 text-xs text-slate-400">
                            Belum ada data zonasi provinsi tercatat.
                        </div>
                    </div>
                </div>

                <!-- Col 2: Sebaran Negara Global -->
                <div class="geo-panel">
                    <div class="geo-panel-header">
                        <div class="d-flex align-center ga-2">
                            <div class="header-icon-box" style="background: #eff6ff; color: #2563eb;">
                                <v-icon size="17">mdi-earth</v-icon>
                            </div>
                            <div>
                                <h3 class="breakdown-title">Zonasi Negara</h3>
                                <p class="breakdown-subtitle">Akses domestik &amp; mancanegara</p>
                            </div>
                        </div>
                    </div>
                    <div class="geo-panel-body">
                        <div v-for="c in countryStats" :key="c.code" class="country-row">
                            <div class="d-flex align-center ga-3">
                                <span class="country-flag-icon">{{ c.flag }}</span>
                                <div class="d-flex flex-column">
                                    <span class="text-xs font-semibold text-slate-800">{{ c.country }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono tracking-wider">{{ c.code }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-center ga-3">
                                <div class="country-bar-inline">
                                    <div class="country-bar-fill" :style="{ width: `${Math.min(c.percentage * 1.05, 100)}%` }"></div>
                                </div>
                                <span class="text-xs font-bold text-slate-800 min-w-[42px] text-right">{{ c.percentage }}%</span>
                                <span class="text-[11px] text-slate-400 font-mono">({{ formatNumber(c.count) }})</span>
                            </div>
                        </div>

                        <div v-if="countryStats.length === 0" class="text-center py-6 text-xs text-slate-400">
                            Belum ada data zonasi negara tercatat.
                        </div>
                    </div>
                </div>

                <!-- Col 3: Kota & Daerah Teratas -->
                <div class="geo-panel">
                    <div class="geo-panel-header">
                        <div class="d-flex align-center ga-2">
                            <div class="header-icon-box" style="background: #fef3c7; color: #d97706;">
                                <v-icon size="17">mdi-city</v-icon>
                            </div>
                            <div>
                                <h3 class="breakdown-title">Kota &amp; Daerah Teratas</h3>
                                <p class="breakdown-subtitle">Konsentrasi pembaca paling aktif</p>
                            </div>
                        </div>
                    </div>
                    <div class="geo-panel-body">
                        <div class="city-chips-grid">
                            <div v-for="ct in cityStats" :key="ct.city" class="city-badge-item">
                                <div class="d-flex align-center ga-1.5 mb-1">
                                    <v-icon size="14" color="#006837">mdi-map-marker</v-icon>
                                    <span class="font-bold text-xs text-slate-800">{{ ct.city }}</span>
                                </div>
                                <div class="d-flex align-center justify-space-between text-[11px] text-slate-500">
                                    <span class="text-truncate max-w-[90px]">{{ ct.region }}</span>
                                    <span class="font-semibold text-emerald-800 bg-emerald-50 px-1.5 py-0.5 rounded">{{ formatNumber(ct.count) }} hits</span>
                                </div>
                            </div>
                        </div>

                        <div v-if="cityStats.length === 0" class="text-center py-6 text-xs text-slate-400">
                            Belum ada data kota tercatat.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Live Activity Stream Feed ── -->
        <div class="m3-table-card">
            <div class="m3-table-header">
                <div>
                    <div class="d-flex align-center ga-2 mb-1">
                        <span class="m3-header-badge m3-header-badge--live">
                            <span class="m3-pulse-dot"></span>
                            Live Feed
                        </span>
                    </div>
                    <h2 class="m3-table-title">Aktivitas Kunjungan Terkini</h2>
                    <p class="m3-table-subtitle">Aliran akses halaman oleh pengunjung secara real-time</p>
                </div>
                <div class="text-xs text-slate-500 font-medium">
                    Menampilkan {{ liveRecentVisits.length }} kunjungan terakhir
                </div>
            </div>

            <div class="table-scroll">
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th style="width: 105px;">Waktu</th>
                            <th style="width: 185px;">Lokasi &amp; Zonasi</th>
                            <th style="width: 130px;">Perangkat</th>
                            <th>Halaman Diakses</th>
                            <th style="width: 170px;">Rujukan / Sumber</th>
                            <th style="width: 120px;">Browser</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="visit in liveRecentVisits" :key="visit.id" class="table-row">
                            <!-- Time -->
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="font-semibold text-xs text-emerald-800">{{ visit.created_at_human }}</span>
                                    <span class="text-[11px] text-slate-400 font-mono">{{ visit.time }}</span>
                                </div>
                            </td>

                            <!-- Location & Zonasi -->
                            <td>
                                <div class="d-flex align-center ga-2">
                                    <span class="country-flag-sm">{{ visit.flag || '🇮🇩' }}</span>
                                    <div class="d-flex flex-column min-w-0">
                                        <span class="font-semibold text-xs text-slate-800 text-truncate">
                                            {{ visit.region || visit.country || 'Jawa Tengah' }}
                                        </span>
                                        <span class="text-[10.5px] text-slate-400 text-truncate">
                                            {{ visit.city ? visit.city + ', ' : '' }}{{ visit.country_code || 'ID' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Device & Platform -->
                            <td>
                                <span class="device-badge">
                                    <v-icon size="13" class="mr-1 text-slate-500">{{ getDeviceIcon(visit.device_type) }}</v-icon>
                                    {{ visit.platform || 'Desktop' }}
                                </span>
                            </td>

                            <!-- Page Title & URL -->
                            <td>
                                <div class="title-cell">
                                    <span class="font-semibold text-xs text-slate-800">{{ visit.page_title }}</span>
                                    <span class="path-subtext">{{ visit.path }}</span>
                                </div>
                            </td>

                            <!-- Referrer -->
                            <td>
                                <span :class="['ref-pill', getReferrerBadgeClass(visit.referrer_type)]">
                                    {{ visit.referrer_host || visit.referrer_type }}
                                </span>
                            </td>

                            <!-- Browser -->
                            <td>
                                <span class="text-xs text-slate-600 font-medium">{{ visit.browser || 'Web Browser' }}</span>
                            </td>
                        </tr>

                        <tr v-if="liveRecentVisits.length === 0">
                            <td colspan="6" class="text-center py-8 text-slate-400 text-xs">
                                Belum ada aktivitas live stream terbaru.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
.m3-page-container {
    padding: 32px 36px 60px;
    max-width: 1320px;
    margin: 0 auto;
    font-family: 'Poppins', system-ui, -apple-system, sans-serif;
}

/* ── M3 Top Header ── */
.m3-header {
    margin-bottom: 24px;
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
    background: #ecfdf5;
    color: #065f46;
    font-size: 11.5px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 9999px;
    border: 1px solid #a7f3d0;
}

.m3-header-badge--count {
    background: #f1f5f9;
    color: #475569;
    border-color: #e2e8f0;
}

.m3-header-badge--live {
    background: #ecfdf5;
    color: #047857;
    border-color: #6ee7b7;
}

.live-pulse-radar {
    width: 8px;
    height: 8px;
    border-radius: 9999px;
    background: #10b981;
    margin-right: 6px;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: radarPulse 1.8s infinite;
}

@keyframes radarPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 7px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.m3-headline {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
    margin: 0 0 4px 0;
    line-height: 1.25;
}

.m3-subhead {
    font-size: 13.5px;
    color: #64748b;
    margin: 0;
    max-width: 680px;
    line-height: 1.5;
}

.m3-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.m3-fab-extended {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(135deg, #006837 0%, #008744 100%);
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 20px;
    border-radius: 9999px;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(0, 104, 55, 0.3);
    transition: all 0.2s cubic-bezier(0.2, 0, 0, 1);
    white-space: nowrap;
}

.m3-fab-extended:hover {
    background: linear-gradient(135deg, #00502a 0%, #006837 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(0, 104, 55, 0.4);
}

.m3-btn-tonal {
    display: inline-flex;
    align-items: center;
    padding: 9px 16px;
    border-radius: 9999px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #0f172a;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    transition: all 0.2s ease;
    white-space: nowrap;
}

.m3-btn-tonal:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    transform: translateY(-1px);
}

.m3-btn-tonal--active {
    background: #ecfdf5 !important;
    border-color: #a7f3d0 !important;
    color: #065f46 !important;
}

.spin-slow {
    animation: spin 3s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* ── M3 Metrics Grid ── */
.m3-metrics-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

@media (min-width: 640px) {
    .m3-metrics-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .m3-metrics-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

.m3-metric-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 20px;
    text-align: left;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
    position: relative;
    overflow: hidden;
}

.m3-metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--m3-accent, #006837);
    opacity: 0;
    transition: opacity 0.2s ease;
}

.m3-metric-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    border-color: #cbd5e1;
}

.m3-metric-card:hover::before {
    opacity: 1;
}

/* Hero Online Card */
.hero-online-card {
    background: linear-gradient(145deg, #ffffff 0%, #f0fdf4 100%);
    border-color: #a7f3d0;
}

.hero-online-card::before {
    opacity: 1;
    background: linear-gradient(90deg, #10b981, #006837);
}

.hero-online-icon {
    background: #dcfce7 !important;
}

.m3-metric-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.m3-metric-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: var(--m3-container, #ecfdf5);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.m3-metric-arrow {
    color: #94a3b8;
    transition: transform 0.2s ease, color 0.2s ease;
}

.m3-metric-active-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 2px 8px;
    border-radius: 9999px;
    background: #ecfdf5;
    color: #047857;
    font-size: 11px;
    font-weight: 700;
    border: 1px solid #a7f3d0;
}

.m3-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: #10b981;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.85); }
}

.growth-pill {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    padding: 2px 7px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
}

.growth--up {
    background: #ecfdf5;
    color: #047857;
}

.growth--down {
    background: #fff1f2;
    color: #e11d48;
}

.m3-metric-body {
    margin-bottom: 12px;
}

.m3-metric-value {
    font-size: 30px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
    letter-spacing: -0.02em;
    font-variant-numeric: tabular-nums;
}

.m3-metric-label {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.m3-metric-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    color: #94a3b8;
    font-weight: 500;
    padding-top: 10px;
    border-top: 1px solid #f8fafc;
}

/* ── Rows Layout ── */
.analytics-row {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

@media (min-width: 1024px) {
    .analytics-row {
        flex-direction: row;
        align-items: stretch;
    }
}

.flex-1 { flex: 1; }
.flex-2 { flex: 2; }

/* ── M3 Table Card ── */
.m3-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.m3-table-header {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 22px 24px 18px;
    border-bottom: 1px solid #f1f5f9;
}

@media (min-width: 640px) {
    .m3-table-header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.m3-table-title {
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.m3-table-subtitle {
    font-size: 12.5px;
    color: #64748b;
    margin: 2px 0 0 0;
}

/* ── Segmented Control Button ── */
.segmented-control {
    display: inline-flex;
    background: #f1f5f9;
    padding: 3px;
    border-radius: 9999px;
    border: 1px solid #e2e8f0;
}

.segmented-btn {
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 9999px;
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s ease;
}

.segmented-btn--active {
    background: #ffffff;
    color: #006837;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

/* ── SVG Chart ── */
.svg-chart-container {
    position: relative;
    padding: 16px 20px 24px;
}

.svg-chart {
    width: 100%;
    height: 220px;
    overflow: visible;
}

.chart-point {
    cursor: pointer;
    transition: r 0.15s ease;
}

.chart-x-labels {
    position: relative;
    height: 20px;
    margin-top: 6px;
}

.x-label {
    position: absolute;
    transform: translateX(-50%);
    font-size: 11px;
    color: #94a3b8;
    font-weight: 500;
}

.x-label--today {
    color: #006837;
    font-weight: 700;
}

.chart-tooltip {
    position: absolute;
    transform: translate(-50%, -120%);
    background: #0f172a;
    color: #ffffff;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 11.5px;
    pointer-events: none;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    z-index: 20;
    white-space: nowrap;
}

.tooltip-date {
    font-size: 10.5px;
    color: #94a3b8;
    margin-bottom: 2px;
}

.tooltip-val {
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: 700;
    color: #34d399;
}

.tooltip-dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: #34d399;
}

.tooltip-sub {
    font-size: 10.5px;
    color: #cbd5e1;
    margin-top: 1px;
}

/* ── Hourly Chart ── */
.hourly-chart-body {
    padding: 20px 24px;
}

.hourly-bars-track {
    display: flex;
    align-items: flex-end;
    gap: 3px;
    height: 170px;
    padding-bottom: 6px;
}

.hourly-bar-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
    position: relative;
}

.hourly-bar-fill-wrap {
    flex: 1;
    width: 100%;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}

.hourly-bar-fill {
    width: 80%;
    max-width: 12px;
    background: #cbd5e1;
    border-radius: 4px 4px 1px 1px;
    transition: all 0.3s ease;
}

.hourly-bar-col:hover .hourly-bar-fill {
    background: #006837;
    transform: scaleY(1.05);
}

.hourly-bar-fill--current {
    background: linear-gradient(180deg, #10b981 0%, #006837 100%) !important;
    box-shadow: 0 0 8px rgba(16, 185, 129, 0.4);
}

.hourly-bar-label {
    font-size: 10px;
    color: #94a3b8;
    font-weight: 500;
    margin-top: 4px;
}

.hourly-footer-legend {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11.5px;
    color: #94a3b8;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
    margin-top: 8px;
}

.dot-green {
    width: 7px;
    height: 7px;
    border-radius: 9999px;
    background: #10b981;
}

/* ── Table Styling ── */
.table-scroll {
    overflow-x: auto;
}

.recent-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    text-align: left;
}

.recent-table th {
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 12px 18px;
    border-bottom: 1px solid #e2e8f0;
}

.recent-table td {
    padding: 13px 18px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
    color: #1e293b;
    vertical-align: middle;
}

.table-row {
    transition: background 0.15s ease;
}

.table-row:hover {
    background: #f8fafc;
}

.table-row:last-child td {
    border-bottom: none;
}

/* Rank Badge */
.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 800;
}

.rank-1 { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
.rank-2 { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.rank-3 { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
.rank-other { background: #f8fafc; color: #94a3b8; }

.title-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.article-title-link {
    color: #0f172a;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    transition: color 0.15s ease;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.article-title-link:hover {
    color: #006837;
}

.path-subtext {
    font-size: 11px;
    color: #94a3b8;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

/* Device badge */
.device-badge {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    color: #334155;
    font-size: 11.5px;
    font-weight: 500;
    padding: 3px 8px;
    border-radius: 6px;
}

/* Referrer Pills */
.ref-pill {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.ref-badge--whatsapp { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.ref-badge--google { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.ref-badge--facebook { background: #eff6ff; color: #1e40af; border: 1px solid #dbeafe; }
.ref-badge--instagram { background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8; }
.ref-badge--direct { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }
.ref-badge--other { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

/* ── Breakdown Cards (Sidebar) ── */
.sidebar-card-header {
    padding: 18px 20px 14px;
    border-bottom: 1px solid #f1f5f9;
}

.header-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.breakdown-title {
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.breakdown-subtitle {
    font-size: 11.5px;
    color: #64748b;
    margin: 1px 0 0 0;
}

.breakdown-body {
    padding: 16px 20px;
}

.breakdown-item {
    margin-bottom: 14px;
}

.breakdown-item-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 5px;
}

.progress-bar-bg {
    width: 100%;
    height: 7px;
    background: #f1f5f9;
    border-radius: 9999px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    border-radius: 9999px;
    transition: width 0.4s ease;
}

.referrer-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 7px 0;
    border-bottom: 1px dashed #f1f5f9;
}

.referrer-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

/* ── Geographic & Zonasi Section Styles ── */
.m3-stat-tag {
    display: inline-flex;
    align-items: center;
    background: #f8fafc;
    color: #334155;
    font-size: 11.5px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 9999px;
    border: 1px solid #e2e8f0;
}

.geo-grid-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    padding: 16px 20px 20px;
}

@media (min-width: 900px) {
    .geo-grid-layout {
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
}

.geo-panel {
    background: #f8fafc;
    border: 1px solid #eef2f6;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.geo-panel-header {
    padding: 14px 16px 10px;
    border-bottom: 1px solid #edf2f7;
    background: #ffffff;
}

.geo-panel-body {
    padding: 14px 16px;
    flex: 1;
}

.geo-item {
    margin-bottom: 11px;
}

.geo-item:last-child {
    margin-bottom: 0;
}

.geo-item-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 4px;
}

.geo-dot {
    width: 8px;
    height: 8px;
    border-radius: 9999px;
    display: inline-block;
    flex-shrink: 0;
}

/* Country Rows */
.country-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed #e2e8f0;
}

.country-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.country-flag-icon {
    font-size: 20px;
    line-height: 1;
    display: inline-block;
}

.country-flag-sm {
    font-size: 16px;
    line-height: 1;
    display: inline-block;
    flex-shrink: 0;
}

.country-bar-inline {
    width: 60px;
    height: 6px;
    background: #e2e8f0;
    border-radius: 9999px;
    overflow: hidden;
}

.country-bar-fill {
    height: 100%;
    background: #2563eb;
    border-radius: 9999px;
}

/* City Grid */
.city-chips-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

@media (max-width: 640px) {
    .city-chips-grid {
        grid-template-columns: 1fr;
    }
}

.city-badge-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 10px;
    transition: all 0.15s ease;
}

.city-badge-item:hover {
    border-color: #cbd5e1;
    background: #fcfcfd;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

/* ── Period Filter Section & Banner ── */
.period-filter-section {
    position: relative;
}

.period-card {
    border-radius: 20px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.period-card-header {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 18px 24px 16px;
    background: #ffffff;
}

@media (min-width: 768px) {
    .period-card-header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.period-section-title {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.01em;
}

.period-section-subtitle {
    font-size: 12px;
    color: #64748b;
    margin: 2px 0 0 0;
}

.period-loading-tag {
    display: inline-flex;
    align-items: center;
    font-size: 11px;
    font-weight: 600;
    color: #006837;
    background: #ecfdf5;
    padding: 2px 8px;
    border-radius: 9999px;
    border: 1px solid #a7f3d0;
}

.period-info-badge {
    display: inline-flex;
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 12px;
    color: #334155;
    white-space: nowrap;
}

.period-compare-pill {
    background: #f1f5f9;
    color: #475569;
    padding: 2px 8px;
    border-radius: 9999px;
    font-size: 11px;
    margin-left: 8px;
    font-weight: 500;
}

/* Quick Period Filter Pills */
.period-pills-bar {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    padding: 12px 24px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
}

.period-pill-btn {
    display: inline-flex;
    align-items: center;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 9999px;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
}

.period-pill-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a;
}

.period-pill-btn--active {
    background: #006837 !important;
    color: #ffffff !important;
    border-color: #006837 !important;
    box-shadow: 0 2px 8px rgba(0, 104, 55, 0.28);
}

.period-pill-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Custom Range Expandable Bar */
.custom-range-bar {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 14px 24px;
    background: #ecfdf5;
    border-bottom: 1px solid #a7f3d0;
}

@media (min-width: 640px) {
    .custom-range-bar {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.custom-range-inputs {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.date-input-group {
    display: flex;
    align-items: center;
    gap: 6px;
}

.date-label {
    font-size: 12px;
    font-weight: 600;
    color: #065f46;
}

.m3-date-input {
    background: #ffffff;
    border: 1px solid #a7f3d0;
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 12px;
    color: #0f172a;
    font-family: inherit;
    outline: none;
    transition: all 0.15s ease;
}

.m3-date-input:focus {
    border-color: #006837;
    box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.15);
}

.date-sep {
    color: #059669;
    font-weight: 700;
}

.m3-btn-apply {
    display: inline-flex;
    align-items: center;
    background: #006837;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 7px 16px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}

.m3-btn-apply:hover {
    background: #00502a;
}

.m3-btn-apply:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Comparative Metrics Banner */
.period-metrics-banner {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1px;
    background: #f1f5f9;
}

@media (min-width: 640px) {
    .period-metrics-banner {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .period-metrics-banner {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

.period-metric-col {
    background: #ffffff;
    padding: 16px 22px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.metric-col-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.metric-col-label {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.05em;
    color: #64748b;
}

.metric-col-val {
    font-size: 24px;
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.02em;
    font-variant-numeric: tabular-nums;
}

.metric-col-sub {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
}

.growth--neutral {
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

/* Expand Transition */
.expand-enter-active,
.expand-leave-active {
    transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
    max-height: 120px;
    opacity: 1;
    overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
    max-height: 0;
    opacity: 0;
    padding-top: 0;
    padding-bottom: 0;
}
</style>
