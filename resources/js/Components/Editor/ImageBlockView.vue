<script setup>
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3';
import { computed, ref, watch } from 'vue';

const props = defineProps(nodeViewProps);

const containerRef = ref(null);
const showCropMenu = ref(false);
const captionInput = ref(props.node.attrs.caption || '');

// Drag to Resize state
const isResizing = ref(false);
const dragWidth = ref(null);
const liveWidthPercent = ref(100);
const liveWidthPx = ref(0);

watch(
    () => props.node.attrs.caption,
    (newVal) => {
        captionInput.value = newVal || '';
    }
);

const currentWidth = computed(() => props.node.attrs.width || '100%');
const currentAlign = computed(() => props.node.attrs.align || 'center');
const currentRatio = computed(() => props.node.attrs.aspectRatio || 'auto');
const currentPos = computed(() => props.node.attrs.objectPosition || 'center');

const activeDisplayWidth = computed(() => {
    if (isResizing.value && dragWidth.value) {
        return dragWidth.value;
    }
    return currentWidth.value;
});

const ratioLabel = computed(() => {
    switch (currentRatio.value) {
        case '16/9':
            return '16:9';
        case '4/3':
            return '4:3';
        case '1/1':
            return '1:1';
        case '3/2':
            return '3:2';
        default:
            return 'Asli';
    }
});

const imageStyle = computed(() => {
    const styles = {
        width: '100%',
        display: 'block',
    };

    if (currentRatio.value && currentRatio.value !== 'auto') {
        styles.aspectRatio = currentRatio.value;
        styles.objectFit = 'cover';
    } else {
        styles.height = 'auto';
    }

    if (currentPos.value && currentPos.value !== 'center') {
        styles.objectPosition = currentPos.value;
    }

    return styles;
});

// Select node in editor when clicked
const selectThisImage = () => {
    const pos = props.getPos();
    if (typeof pos === 'number') {
        props.editor?.chain().setNodeSelection(pos).run();
    }
};

// Direct interactive drag-to-resize
const startResize = (e, direction) => {
    e.preventDefault();
    e.stopPropagation();

    isResizing.value = true;
    const startX = e.clientX;

    const container = containerRef.value;
    if (!container) return;

    const startRect = container.getBoundingClientRect();
    const startWidthPx = startRect.width;

    const canvas = container.closest('.editor-canvas') || container.closest('.editor-canvas-wrapper');
    const parentWidth = canvas ? canvas.clientWidth : 750;

    liveWidthPx.value = Math.round(startWidthPx);
    liveWidthPercent.value = Math.min(100, Math.max(15, Math.round((startWidthPx / parentWidth) * 100)));

    const onMouseMove = (moveEvent) => {
        moveEvent.preventDefault();
        const deltaX = moveEvent.clientX - startX;

        let newWidth = startWidthPx;
        if (direction === 'se' || direction === 'ne' || direction === 'e') {
            newWidth = startWidthPx + deltaX;
        } else if (direction === 'sw' || direction === 'nw' || direction === 'w') {
            newWidth = startWidthPx - deltaX;
        }

        newWidth = Math.max(140, Math.min(newWidth, parentWidth));
        const percent = Math.min(100, Math.max(15, Math.round((newWidth / parentWidth) * 100)));

        liveWidthPx.value = Math.round(newWidth);
        liveWidthPercent.value = percent;
        dragWidth.value = `${percent}%`;
    };

    const onMouseUp = () => {
        isResizing.value = false;
        if (dragWidth.value) {
            props.updateAttributes({ width: dragWidth.value });
        }
        dragWidth.value = null;
        window.removeEventListener('mousemove', onMouseMove);
        window.removeEventListener('mouseup', onMouseUp);
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
    };

    document.body.style.cursor = (direction === 'e' || direction === 'w') ? 'ew-resize' : (direction === 'nw' || direction === 'se') ? 'nwse-resize' : 'nesw-resize';
    document.body.style.userSelect = 'none';

    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', onMouseUp);
};

// Double click quick toggle
const toggleQuickSize = () => {
    const currentNum = parseInt(currentWidth.value) || 100;
    if (currentNum >= 90) {
        props.updateAttributes({ width: '50%' });
    } else if (currentNum >= 45 && currentNum < 70) {
        props.updateAttributes({ width: '75%' });
    } else {
        props.updateAttributes({ width: '100%' });
    }
};

const setWidth = (w) => {
    props.updateAttributes({ width: w });
};

const setSliderWidth = (e) => {
    const val = e.target.value;
    props.updateAttributes({ width: `${val}%` });
};

const setAlign = (align) => {
    props.updateAttributes({ align });
};

const setRatio = (ratio) => {
    props.updateAttributes({ aspectRatio: ratio });
};

const setPosition = (pos) => {
    props.updateAttributes({ objectPosition: pos });
};

const updateCaption = () => {
    props.updateAttributes({ caption: captionInput.value });
};

const removeImage = () => {
    props.deleteNode();
};

const addParagraphBelow = () => {
    const pos = props.getPos();
    if (typeof pos === 'number') {
        const nodeSize = props.node.nodeSize;
        const targetPos = pos + nodeSize;
        props.editor
            ?.chain()
            .insertContentAt(targetPos, { type: 'paragraph', content: [] })
            .setTextSelection(targetPos + 1)
            .focus()
            .run();
    }
};

const toggleCropMenu = () => {
    showCropMenu.value = !showCropMenu.value;
};

const closeCropMenu = () => {
    showCropMenu.value = false;
};
</script>

<template>
    <NodeViewWrapper
        as="figure"
        :class="[
            'image-node-wrapper',
            `align-${currentAlign}`,
            { 'is-selected': selected, 'is-resizing': isResizing }
        ]"
        :style="{ width: activeDisplayWidth, maxWidth: '100%' }"
    >
        <!-- Main Image Container -->
        <div
            ref="containerRef"
            class="image-inner-container"
            :class="{ 'is-resizing': isResizing, 'is-selected': selected }"
            @click="selectThisImage"
        >
            <!-- Floating Context Toolbar (Positioned at Top of Image, Always Reachable) -->
            <div class="image-floating-toolbar" contenteditable="false" @click.stop>
                <!-- Alignment Controls (Kiri, Tengah, Kanan) -->
                <div class="btn-group">
                    <button
                        type="button"
                        class="action-pill"
                        :class="{ 'action-pill--active': currentAlign === 'left' }"
                        title="Atur Posisi Kiri (Teks Mengalir)"
                        @click.stop="setAlign('left')"
                    >
                        <v-icon size="13" class="mr-0.5">mdi-format-align-left</v-icon>
                        <span>Kiri</span>
                    </button>
                    <button
                        type="button"
                        class="action-pill"
                        :class="{ 'action-pill--active': currentAlign === 'center' }"
                        title="Atur Posisi Tengah"
                        @click.stop="setAlign('center')"
                    >
                        <v-icon size="13" class="mr-0.5">mdi-format-align-center</v-icon>
                        <span>Tengah</span>
                    </button>
                    <button
                        type="button"
                        class="action-pill"
                        :class="{ 'action-pill--active': currentAlign === 'right' }"
                        title="Atur Posisi Kanan (Teks Mengalir)"
                        @click.stop="setAlign('right')"
                    >
                        <v-icon size="13" class="mr-0.5">mdi-format-align-right</v-icon>
                        <span>Kanan</span>
                    </button>
                </div>

                <div class="bar-separator"></div>

                <!-- Size Presets -->
                <div class="btn-group">
                    <button
                        type="button"
                        class="action-pill"
                        :class="{ 'action-pill--active': currentWidth === '25%' }"
                        title="Ukuran 25% (Kecil)"
                        @click.stop="setWidth('25%')"
                    >
                        25%
                    </button>
                    <button
                        type="button"
                        class="action-pill"
                        :class="{ 'action-pill--active': currentWidth === '50%' }"
                        title="Ukuran 50% (Sedang)"
                        @click.stop="setWidth('50%')"
                    >
                        50%
                    </button>
                    <button
                        type="button"
                        class="action-pill"
                        :class="{ 'action-pill--active': currentWidth === '75%' }"
                        title="Ukuran 75% (Besar)"
                        @click.stop="setWidth('75%')"
                    >
                        75%
                    </button>
                    <button
                        type="button"
                        class="action-pill"
                        :class="{ 'action-pill--active': currentWidth === '100%' }"
                        title="Ukuran 100% (Penuh)"
                        @click.stop="setWidth('100%')"
                    >
                        100%
                    </button>
                </div>

                <div class="bar-separator"></div>

                <!-- Crop & Aspect Ratio Button -->
                <div class="relative-container">
                    <button
                        type="button"
                        class="action-pill action-pill--crop"
                        :class="{ 'action-pill--active': showCropMenu || currentRatio !== 'auto' }"
                        title="Pangkas Rasio Foto"
                        @click.stop="toggleCropMenu"
                    >
                        <v-icon size="13" class="mr-0.5">mdi-crop</v-icon>
                        <span>{{ ratioLabel }}</span>
                        <v-icon size="11" class="ml-0.5">mdi-chevron-down</v-icon>
                    </button>

                    <!-- Crop Settings Popover -->
                    <div v-if="showCropMenu" class="crop-popover" @click.stop>
                        <div class="crop-header">
                            <span class="crop-title">Pangkas & Rasio Foto</span>
                            <button type="button" class="btn-close-popover" @click="closeCropMenu">
                                <v-icon size="14">mdi-close</v-icon>
                            </button>
                        </div>

                        <!-- Ratio Options -->
                        <div class="crop-section">
                            <label class="crop-label">Pilihan Rasio</label>
                            <div class="crop-grid">
                                <button
                                    type="button"
                                    class="ratio-btn"
                                    :class="{ 'ratio-btn--active': currentRatio === 'auto' }"
                                    @click="setRatio('auto')"
                                >
                                    <span class="ratio-name">Asli</span>
                                    <span class="ratio-sub">Proporsi Awal</span>
                                </button>
                                <button
                                    type="button"
                                    class="ratio-btn"
                                    :class="{ 'ratio-btn--active': currentRatio === '16/9' }"
                                    @click="setRatio('16/9')"
                                >
                                    <span class="ratio-name">16:9</span>
                                    <span class="ratio-sub">Landscape</span>
                                </button>
                                <button
                                    type="button"
                                    class="ratio-btn"
                                    :class="{ 'ratio-btn--active': currentRatio === '4/3' }"
                                    @click="setRatio('4/3')"
                                >
                                    <span class="ratio-name">4:3</span>
                                    <span class="ratio-sub">Standar Berita</span>
                                </button>
                                <button
                                    type="button"
                                    class="ratio-btn"
                                    :class="{ 'ratio-btn--active': currentRatio === '1/1' }"
                                    @click="setRatio('1/1')"
                                >
                                    <span class="ratio-name">1:1</span>
                                    <span class="ratio-sub">Persegi</span>
                                </button>
                                <button
                                    type="button"
                                    class="ratio-btn"
                                    :class="{ 'ratio-btn--active': currentRatio === '3/2' }"
                                    @click="setRatio('3/2')"
                                >
                                    <span class="ratio-name">3:2</span>
                                    <span class="ratio-sub">Foto Klasik</span>
                                </button>
                            </div>
                        </div>

                        <!-- Focus Point -->
                        <div v-if="currentRatio !== 'auto'" class="crop-section">
                            <label class="crop-label">Fokus Gambar (Object Position)</label>
                            <div class="d-flex ga-1.5">
                                <button
                                    type="button"
                                    class="pos-btn"
                                    :class="{ 'pos-btn--active': currentPos === 'top' }"
                                    @click="setPosition('top')"
                                >
                                    Atas (Wajah)
                                </button>
                                <button
                                    type="button"
                                    class="pos-btn"
                                    :class="{ 'pos-btn--active': currentPos === 'center' }"
                                    @click="setPosition('center')"
                                >
                                    Tengah
                                </button>
                                <button
                                    type="button"
                                    class="pos-btn"
                                    :class="{ 'pos-btn--active': currentPos === 'bottom' }"
                                    @click="setPosition('bottom')"
                                >
                                    Bawah
                                </button>
                            </div>
                        </div>

                        <!-- Custom Width Slider -->
                        <div class="crop-section border-t border-slate-700/50 pt-2 mt-2">
                            <div class="d-flex justify-between items-center mb-1">
                                <label class="crop-label mb-0">Lebar Kustom</label>
                                <span class="text-xs font-mono text-emerald-400">{{ currentWidth }}</span>
                            </div>
                            <input
                                type="range"
                                min="20"
                                max="100"
                                step="5"
                                :value="parseInt(currentWidth) || 100"
                                class="width-slider"
                                @input="setSliderWidth"
                            />
                        </div>
                    </div>
                </div>

                <div class="bar-separator"></div>

                <!-- Add Text Below Button -->
                <button
                    type="button"
                    class="action-pill action-pill--add-text"
                    title="Lanjut Menulis Teks di Bawah Foto"
                    @click.stop="addParagraphBelow"
                >
                    <v-icon size="13" class="mr-0.5">mdi-keyboard-return</v-icon>
                    <span>Tulis Teks</span>
                </button>

                <!-- Delete Button -->
                <button
                    type="button"
                    class="action-icon action-icon--delete"
                    title="Hapus Foto dari Naskah"
                    @click.stop="removeImage"
                >
                    <v-icon size="14">mdi-trash-can-outline</v-icon>
                </button>
            </div>

            <!-- Image Element -->
            <img
                :src="node.attrs.src"
                :alt="node.attrs.alt"
                :style="imageStyle"
                class="canvas-rendered-image"
                loading="lazy"
                title="Klik untuk memilih foto. Geser sudut/sisi untuk mengubah ukuran, atau klik dua kali untuk ganti ukuran cepat"
                @dblclick.stop="toggleQuickSize"
            />

            <!-- Direct Visual Resize Handles -->
            <div class="resize-handle-layer" contenteditable="false" @click.stop>
                <div
                    class="resize-handle handle-nw"
                    title="Tarik untuk mengubah ukuran"
                    @mousedown.stop="startResize($event, 'nw')"
                ></div>
                <div
                    class="resize-handle handle-ne"
                    title="Tarik untuk mengubah ukuran"
                    @mousedown.stop="startResize($event, 'ne')"
                ></div>
                <div
                    class="resize-handle handle-sw"
                    title="Tarik untuk mengubah ukuran"
                    @mousedown.stop="startResize($event, 'sw')"
                ></div>
                <div
                    class="resize-handle handle-se"
                    title="Tarik untuk mengubah ukuran"
                    @mousedown.stop="startResize($event, 'se')"
                ></div>

                <div
                    class="resize-edge-handle edge-e"
                    title="Tarik sisi kanan untuk mengubah ukuran"
                    @mousedown.stop="startResize($event, 'e')"
                ></div>
                <div
                    class="resize-edge-handle edge-w"
                    title="Tarik sisi kiri untuk mengubah ukuran"
                    @mousedown.stop="startResize($event, 'w')"
                ></div>
            </div>

            <!-- Live Drag Tooltip Overlay -->
            <div v-if="isResizing" class="live-resize-badge">
                <v-icon size="13" class="mr-1 text-emerald-400">mdi-arrow-expand-horizontal</v-icon>
                <span>{{ liveWidthPercent }}% ({{ liveWidthPx }}px)</span>
            </div>
        </div>

        <!-- Editable Caption Box -->
        <figcaption class="image-caption-box" contenteditable="false" @click.stop>
            <v-icon size="12" color="#94a3b8" class="mr-1">mdi-card-text-outline</v-icon>
            <input
                type="text"
                v-model="captionInput"
                class="caption-input-field"
                placeholder="Tulis keterangan / takarir foto (opsional)..."
                @input="updateCaption"
                @keydown.stop
            />
        </figcaption>
    </NodeViewWrapper>
</template>

<style scoped>
.image-node-wrapper {
    position: relative;
    user-select: none;
    transition: width 0.15s ease-out;
}

/* Rata Tengah (Center) */
.image-node-wrapper.align-center {
    margin: 12px auto;
    text-align: center;
    display: block;
    float: none;
    clear: both;
}

/* Rata Kiri (Left) */
.image-node-wrapper.align-left {
    float: left;
    margin: 4px 20px 12px 0;
    clear: left;
    text-align: left;
}

/* Rata Kanan (Right) */
.image-node-wrapper.align-right {
    float: right;
    margin: 4px 0 12px 20px;
    clear: right;
    text-align: right;
}

/* Inner Container */
.image-inner-container {
    position: relative;
    border-radius: 10px;
    background: #f8fafc;
    border: 2px solid transparent;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 3px 10px rgba(15, 23, 42, 0.08);
}

.image-node-wrapper:hover .image-inner-container,
.image-node-wrapper.is-selected .image-inner-container,
.image-inner-container.is-selected {
    border-color: #006837;
    box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.15), 0 4px 16px rgba(15, 23, 42, 0.12);
}

.image-inner-container.is-resizing {
    border-color: #006837 !important;
    border-style: dashed !important;
    box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.2) !important;
}

.canvas-rendered-image {
    width: 100%;
    display: block;
    border-radius: 8px;
    cursor: pointer;
    user-select: none;
}

/* ── Pinned Context Toolbar at Top of Image ── */
.image-floating-toolbar {
    position: absolute;
    top: 8px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(15, 23, 42, 0.94);
    backdrop-filter: blur(8px);
    color: #f8fafc;
    padding: 3px 6px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 3px;
    z-index: 30;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.18s ease, transform 0.18s ease;
    white-space: nowrap;
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.image-node-wrapper:hover .image-floating-toolbar,
.image-node-wrapper.is-selected .image-floating-toolbar,
.image-inner-container.is-selected .image-floating-toolbar {
    opacity: 1;
    pointer-events: auto;
    transform: translateX(-50%) translateY(0);
}

.bar-separator {
    width: 1px;
    height: 16px;
    background: rgba(255, 255, 255, 0.18);
    margin: 0 2px;
}

.btn-group {
    display: flex;
    align-items: center;
    gap: 2px;
}

.action-pill {
    padding: 3px 7px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 5px;
    border: none;
    background: transparent;
    color: #cbd5e1;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
}

.action-pill:hover {
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
}

.action-pill--active {
    background: #006837 !important;
    color: #ffffff !important;
}

.action-pill--crop {
    display: inline-flex;
    align-items: center;
}

.action-pill--add-text {
    background: rgba(0, 104, 55, 0.5);
    color: #a7f3d0;
    display: inline-flex;
    align-items: center;
}

.action-pill--add-text:hover {
    background: #006837;
    color: #ffffff;
}

.action-icon {
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
    border: none;
    background: transparent;
    color: #cbd5e1;
    cursor: pointer;
    transition: all 0.15s ease;
}

.action-icon:hover {
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
}

.action-icon--active {
    background: #006837 !important;
    color: #ffffff !important;
}

.action-icon--delete:hover {
    background: #e11d48 !important;
    color: #ffffff !important;
}

/* ── Crop Settings Popover ── */
.relative-container {
    position: relative;
}

.crop-popover {
    position: absolute;
    top: calc(100% + 6px);
    left: 50%;
    transform: translateX(-50%);
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    padding: 10px 12px;
    width: 270px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.45);
    z-index: 50;
    text-align: left;
    white-space: normal;
}

.crop-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
    padding-bottom: 5px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.crop-title {
    font-size: 11.5px;
    font-weight: 700;
    color: #f1f5f9;
}

.btn-close-popover {
    background: transparent;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 2px;
    border-radius: 4px;
}

.btn-close-popover:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.1);
}

.crop-section {
    margin-bottom: 8px;
}

.crop-label {
    display: block;
    font-size: 10.5px;
    font-weight: 600;
    color: #94a3b8;
    margin-bottom: 5px;
}

.crop-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 5px;
}

.ratio-btn {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 5px 7px;
    background: #0f172a;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 6px;
    color: #cbd5e1;
    cursor: pointer;
    transition: all 0.15s ease;
    text-align: left;
}

.ratio-btn:hover {
    border-color: #006837;
    background: #162438;
}

.ratio-btn--active {
    background: #006837 !important;
    border-color: #10b981 !important;
    color: #ffffff !important;
}

.ratio-name {
    font-size: 11px;
    font-weight: 700;
}

.ratio-sub {
    font-size: 9px;
    opacity: 0.75;
}

.pos-btn {
    flex: 1;
    padding: 3px 5px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 5px;
    background: #0f172a;
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #cbd5e1;
    cursor: pointer;
    transition: all 0.15s ease;
}

.pos-btn:hover {
    border-color: #006837;
}

.pos-btn--active {
    background: #006837 !important;
    color: #ffffff !important;
    border-color: #10b981 !important;
}

.width-slider {
    width: 100%;
    accent-color: #006837;
    cursor: pointer;
}

/* ── Direct Visual Resize Handles ── */
.resize-handle-layer {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.image-node-wrapper:hover .resize-handle-layer,
.image-node-wrapper.is-selected .resize-handle-layer,
.image-inner-container.is-selected .resize-handle-layer,
.image-inner-container.is-resizing .resize-handle-layer {
    pointer-events: auto;
}

.resize-handle {
    position: absolute;
    width: 11px;
    height: 11px;
    background: #ffffff;
    border: 2px solid #006837;
    border-radius: 2px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
    z-index: 25;
    opacity: 0;
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.image-node-wrapper:hover .resize-handle,
.image-node-wrapper.is-selected .resize-handle,
.image-inner-container.is-selected .resize-handle,
.image-inner-container.is-resizing .resize-handle {
    opacity: 1;
}

.resize-handle:hover {
    transform: scale(1.3);
    background: #006837;
    border-color: #ffffff;
}

.handle-nw {
    top: -5px;
    left: -5px;
    cursor: nwse-resize;
}

.handle-ne {
    top: -5px;
    right: -5px;
    cursor: nesw-resize;
}

.handle-sw {
    bottom: -5px;
    left: -5px;
    cursor: nesw-resize;
}

.handle-se {
    bottom: -5px;
    right: -5px;
    cursor: nwse-resize;
}

.resize-edge-handle {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 6px;
    height: 28px;
    background: #ffffff;
    border: 2px solid #006837;
    border-radius: 3px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
    z-index: 25;
    cursor: ew-resize;
    opacity: 0;
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.image-node-wrapper:hover .resize-edge-handle,
.image-node-wrapper.is-selected .resize-edge-handle,
.image-inner-container.is-selected .resize-edge-handle,
.image-inner-container.is-resizing .resize-edge-handle {
    opacity: 1;
}

.resize-edge-handle:hover {
    transform: translateY(-50%) scale(1.2);
    background: #006837;
    border-color: #ffffff;
}

.edge-w {
    left: -5px;
}

.edge-e {
    right: -5px;
}

/* ── Live Drag Tooltip Badge ── */
.live-resize-badge {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(15, 23, 42, 0.92);
    backdrop-filter: blur(6px);
    color: #ffffff;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
    display: inline-flex;
    align-items: center;
    pointer-events: none;
    z-index: 35;
    border: 1px solid rgba(255, 255, 255, 0.15);
}

/* ── Caption Box ── */
.image-caption-box {
    display: flex;
    align-items: center;
    margin-top: 5px;
    padding: 2px 6px;
    border-radius: 6px;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    transition: all 0.2s ease;
}

.image-caption-box:focus-within {
    background: #ffffff;
    border-color: #006837;
    border-style: solid;
    box-shadow: 0 0 0 2px rgba(0, 104, 55, 0.08);
}

.caption-input-field {
    width: 100%;
    font-size: 11.5px;
    color: #334155;
    background: transparent;
    border: none;
    outline: none;
    font-style: italic;
}

.caption-input-field::placeholder {
    color: #94a3b8;
    font-style: italic;
}
</style>
