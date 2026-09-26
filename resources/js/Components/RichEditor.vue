<script setup>
import showAlert from '@/Utils/sweetalert';
import ImageBlockExtension from '@/Components/Editor/ImageBlockExtension';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { TextSelection } from 'prosemirror-state';
import axios from 'axios';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Mulai menulis naskah berita lengkap di sini...' },
});

const emit = defineEmits(['update:modelValue']);

const singleFileInput = ref(null);
const galleryFileInput = ref(null);
const isUploading = ref(false);
const uploadMessage = ref('');

// Link Dialog
const linkDialog = ref(false);
const linkUrl = ref('');

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [2, 3],
            },
        }),
        Underline,
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Link.configure({
            openOnClick: false,
            autolink: true,
            HTMLAttributes: {
                class: 'editor-link',
                target: '_blank',
                rel: 'noopener noreferrer',
            },
        }),
        ImageBlockExtension,
        Placeholder.configure({
            placeholder: props.placeholder,
        }),
    ],
    editorProps: {
        attributes: {
            class: 'editor-canvas focus:outline-none',
        },
        handleTextInput(view, from, to, text) {
            const { selection, doc, schema } = view.state;
            if (selection.node && (selection.node.type.name === 'imageBlock' || selection.node.type.name === 'image')) {
                const afterPos = selection.$from.after();
                const $after = doc.resolve(afterPos);
                const nextNode = $after.nodeAfter;

                let tr = view.state.tr;
                if (nextNode && nextNode.type.name === 'paragraph') {
                    // Paragraph already exists right after image! Type inside it without creating extra gaps:
                    const insidePos = afterPos + 1;
                    tr = tr.insertText(text, insidePos);
                    tr.setSelection(TextSelection.near(tr.doc.resolve(insidePos + text.length)));
                } else {
                    // Create one single paragraph
                    const newPara = schema.nodes.paragraph.createAndFill({}, schema.text(text));
                    tr = tr.insert(afterPos, newPara);
                    tr.setSelection(TextSelection.near(tr.doc.resolve(afterPos + 1 + text.length)));
                }
                view.dispatch(tr);
                return true;
            }
            return false;
        },
        handleKeyDown(view, event) {
            const { selection, doc, schema } = view.state;
            if (selection.node && (selection.node.type.name === 'imageBlock' || selection.node.type.name === 'image')) {
                if (event.key === 'Enter') {
                    const afterPos = selection.$from.after();
                    const $after = doc.resolve(afterPos);
                    const nextNode = $after.nodeAfter;

                    let tr = view.state.tr;
                    if (nextNode && nextNode.type.name === 'paragraph' && nextNode.textContent.length === 0) {
                        // Already has an empty paragraph right after! Just focus it:
                        tr.setSelection(TextSelection.near(tr.doc.resolve(afterPos + 1)));
                    } else {
                        // Create a paragraph
                        tr = tr.insert(afterPos, schema.nodes.paragraph.createAndFill());
                        tr.setSelection(TextSelection.near(tr.doc.resolve(afterPos + 1)));
                    }
                    view.dispatch(tr);
                    return true;
                }
            }
            return false;
        },
    },
    onUpdate: () => {
        emit('update:modelValue', editor.value?.getHTML() || '');
    },
});

watch(
    () => props.modelValue,
    (newValue) => {
        if (!editor.value) return;
        const currentHTML = editor.value.getHTML();
        if (currentHTML !== newValue) {
            // Only sync externally if the user is not actively typing in the editor
            if (!editor.value.isFocused) {
                editor.value.commands.setContent(newValue || '', false);
            }
        }
    }
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

// Link Operations
const openLinkModal = () => {
    const previousUrl = editor.value?.getAttributes('link').href || '';
    linkUrl.value = previousUrl;
    linkDialog.value = true;
};

const applyLink = () => {
    if (!linkUrl.value) {
        editor.value?.chain().focus().unsetLink().run();
    } else {
        let finalUrl = linkUrl.value.trim();
        if (!/^https?:\/\//i.test(finalUrl)) {
            finalUrl = 'https://' + finalUrl;
        }
        editor.value
            ?.chain()
            .focus()
            .extendMarkRange('link')
            .setLink({ href: finalUrl })
            .run();
    }
    linkDialog.value = false;
    linkUrl.value = '';
};

const removeLink = () => {
    editor.value?.chain().focus().unsetLink().run();
    linkDialog.value = false;
    linkUrl.value = '';
};

// Single Image Upload
const triggerSingleImage = () => {
    singleFileInput.value?.click();
};

const handleSingleImageUpload = async (e) => {
    const file = e.target.files?.[0];
    if (!file) return;

    isUploading.value = true;
    uploadMessage.value = 'Mengunggah foto ke naskah...';

    const formData = new FormData();
    formData.append('image', file);

    try {
        const response = await axios.post('/dashboard/news/upload-image', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (response.data?.url) {
            editor.value
                ?.chain()
                .focus()
                .insertContent([
                    {
                        type: 'imageBlock',
                        attrs: {
                            src: response.data.url,
                            alt: file.name,
                            width: '100%',
                            align: 'center',
                            aspectRatio: 'auto',
                            objectPosition: 'center',
                            caption: '',
                        },
                    },
                    {
                        type: 'paragraph',
                        content: [],
                    },
                ])
                .focus('end')
                .run();
            showAlert.toast('Foto berhasil disisipkan ke naskah!');
        }
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Pastikan format JPG, PNG, atau WebP dengan ukuran maksimal 5MB.';
        showAlert.error('Gagal Mengunggah Foto', errorMsg);
    } finally {
        isUploading.value = false;
        uploadMessage.value = '';
        if (singleFileInput.value) singleFileInput.value.value = '';
    }
};

// Global Alignment Handler (Works for both Text & Selected Photos)
const setAlign = (alignment) => {
    if (!editor.value) return;
    const { selection } = editor.value.state;
    if (selection.node && (selection.node.type.name === 'imageBlock' || selection.node.type.name === 'image')) {
        editor.value.chain().updateAttributes('imageBlock', { align: alignment }).run();
        const label = alignment === 'left' ? 'Kiri' : alignment === 'right' ? 'Kanan' : 'Tengah';
        showAlert.toast(`Posisi foto diatur ke ${label}`);
        return;
    }
    editor.value.chain().focus().setTextAlign(alignment).run();
};

const isAlignActive = (alignment) => {
    if (!editor.value) return false;
    const { selection } = editor.value.state;
    if (selection.node && (selection.node.type.name === 'imageBlock' || selection.node.type.name === 'image')) {
        return (selection.node.attrs.align || 'center') === alignment;
    }
    return editor.value.isActive({ textAlign: alignment });
};

const handleCanvasWrapperClick = (e) => {
    if (e.target.classList.contains('editor-canvas-wrapper')) {
        editor.value?.chain().focus('end').run();
    }
};

// Gallery Grid Upload (Multiple Photos)
const triggerGalleryUpload = () => {
    galleryFileInput.value?.click();
};

const handleGalleryUpload = async (e) => {
    const files = Array.from(e.target.files || []);
    if (files.length === 0) return;

    if (files.length > 8) {
        showAlert.warning('Batas Maksimal Foto', 'Maksimal memilih 8 foto sekaligus untuk satu blok galeri berita.');
        return;
    }

    isUploading.value = true;
    uploadMessage.value = `Mengunggah ${files.length} foto untuk galeri berita...`;

    try {
        const uploadedUrls = [];
        for (let i = 0; i < files.length; i++) {
            const formData = new FormData();
            formData.append('image', files[i]);
            const res = await axios.post('/dashboard/news/upload-image', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            if (res.data?.url) {
                uploadedUrls.push(res.data.url);
            }
        }

        if (uploadedUrls.length > 0) {
            // Build gallery grid HTML block
            const cols = uploadedUrls.length === 1 ? 'cols-1' : uploadedUrls.length === 2 ? 'cols-2' : 'cols-3';
            let galleryHtml = `<div class="article-gallery-grid ${cols}">`;
            uploadedUrls.forEach((url, idx) => {
                galleryHtml += `<div class="gallery-photo-item"><img src="${url}" alt="Dokumentasi Kegiatan ${idx + 1}" class="gallery-img" /></div>`;
            });
            galleryHtml += `</div><p></p>`;

            editor.value?.chain().focus().insertContent(galleryHtml).run();
            showAlert.toast(`${uploadedUrls.length} foto berhasil dibuat menjadi galeri!`);
        }
    } catch (error) {
        showAlert.error('Gagal Mengunggah Galeri', 'Terjadi kendala saat mengunggah foto galeri. Silakan coba kembali.');
    } finally {
        isUploading.value = false;
        uploadMessage.value = '';
        if (galleryFileInput.value) galleryFileInput.value.value = '';
    }
};

const wordCount = computed(() => {
    if (!props.modelValue) return 0;
    // Strip HTML tags and count words
    const text = props.modelValue.replace(/<[^>]*>/g, ' ').trim();
    if (!text) return 0;
    return text.split(/\s+/).filter(Boolean).length;
});
</script>

<template>
    <div class="rich-editor-container">
        <!-- Hidden file inputs for inline images & gallery -->
        <input
            ref="singleFileInput"
            type="file"
            accept="image/png, image/jpeg, image/jpg, image/webp"
            class="hidden-input"
            @change="handleSingleImageUpload"
        />
        <input
            ref="galleryFileInput"
            type="file"
            multiple
            accept="image/png, image/jpeg, image/jpg, image/webp"
            class="hidden-input"
            @change="handleGalleryUpload"
        />

        <!-- Loading Overlay during image upload -->
        <div v-if="isUploading" class="upload-progress-overlay">
            <v-progress-circular indeterminate color="#006837" size="32" width="3" />
            <span class="upload-progress-text">{{ uploadMessage }}</span>
        </div>

        <!-- Sticky Floating Toolbar -->
        <div class="editor-toolbar" v-if="editor">
            <!-- Text Styles Group -->
            <div class="toolbar-group">
                <button
                    type="button"
                    class="tool-btn"
                    :class="{ 'tool-btn--active': editor.isActive('bold') }"
                    title="Tebal (Ctrl+B)"
                    @click="editor.chain().focus().toggleBold().run()"
                >
                    <v-icon size="17">mdi-format-bold</v-icon>
                </button>
                <button
                    type="button"
                    class="tool-btn"
                    :class="{ 'tool-btn--active': editor.isActive('italic') }"
                    title="Miring (Ctrl+I)"
                    @click="editor.chain().focus().toggleItalic().run()"
                >
                    <v-icon size="17">mdi-format-italic</v-icon>
                </button>
                <button
                    type="button"
                    class="tool-btn"
                    :class="{ 'tool-btn--active': editor.isActive('underline') }"
                    title="Garis Bawah (Ctrl+U)"
                    @click="editor.chain().focus().toggleUnderline().run()"
                >
                    <v-icon size="17">mdi-format-underline</v-icon>
                </button>
                <button
                    type="button"
                    class="tool-btn"
                    :class="{ 'tool-btn--active': editor.isActive('strike') }"
                    title="Coret Teks"
                    @click="editor.chain().focus().toggleStrike().run()"
                >
                    <v-icon size="17">mdi-format-strikethrough-variant</v-icon>
                </button>
            </div>

            <div class="toolbar-divider"></div>

            <!-- Headings Group -->
            <div class="toolbar-group">
                <button
                    type="button"
                    class="tool-btn"
                    :class="{ 'tool-btn--active': editor.isActive('heading', { level: 2 }) }"
                    title="Sub-Judul Utama (H2)"
                    @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                >
                    <span class="heading-label">H2</span>
                </button>
                <button
                    type="button"
                    class="tool-btn"
                    :class="{ 'tool-btn--active': editor.isActive('heading', { level: 3 }) }"
                    title="Sub-Bagian Kecil (H3)"
                    @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                >
                    <span class="heading-label">H3</span>
                </button>
                <button
                    type="button"
                    class="tool-btn"
                    :class="{ 'tool-btn--active': editor.isActive('paragraph') }"
                    title="Paragraf Teks Biasa"
                    @click="editor.chain().focus().setParagraph().run()"
                >
                    <v-icon size="17">mdi-format-paragraph</v-icon>
                </button>
            </div>

            <div class="toolbar-divider"></div>

            <!-- Lists & Quote Group -->
            <div class="toolbar-group">
                <button
                    type="button"
                    class="tool-btn"
                    :class="{ 'tool-btn--active': editor.isActive('bulletList') }"
                    title="Daftar Titik (Bullet List)"
                    @click="editor.chain().focus().toggleBulletList().run()"
                >
                    <v-icon size="17">mdi-format-list-bulleted</v-icon>
                </button>
                <button
                    type="button"
                    class="tool-btn"
                    :class="{ 'tool-btn--active': editor.isActive('orderedList') }"
                    title="Daftar Nomor (Numbered List)"
                    @click="editor.chain().focus().toggleOrderedList().run()"
                >
                    <v-icon size="17">mdi-format-list-numbered</v-icon>
                </button>
                <button
                    type="button"
                    class="tool-btn"
                    :class="{ 'tool-btn--active': editor.isActive('blockquote') }"
                    title="Kutipan Pernyataan (Blockquote)"
                    @click="editor.chain().focus().toggleBlockquote().run()"
                >
                    <v-icon size="17">mdi-format-quote-close</v-icon>
                </button>
                <button
                    type="button"
                    class="tool-btn"
                    title="Garis Pemisah (Horizontal Line)"
                    @click="editor.chain().focus().setHorizontalRule().run()"
                >
                    <v-icon size="17">mdi-minus</v-icon>
                </button>
            </div>

            <div class="toolbar-divider"></div>

            <!-- Alignment Group: Left, Center, Right, Justify (For Text & Photo) -->
            <div class="toolbar-group">
                <button
                    type="button"
                    class="tool-btn"
                    :class="{ 'tool-btn--active': isAlignActive('left') }"
                    title="Rata Kiri (Teks / Foto)"
                    @click="setAlign('left')"
                >
                    <v-icon size="17">mdi-format-align-left</v-icon>
                </button>
                <button
                    type="button"
                    class="tool-btn"
                    :class="{ 'tool-btn--active': isAlignActive('center') }"
                    title="Rata Tengah (Teks / Foto)"
                    @click="setAlign('center')"
                >
                    <v-icon size="17">mdi-format-align-center</v-icon>
                </button>
                <button
                    type="button"
                    class="tool-btn"
                    :class="{ 'tool-btn--active': isAlignActive('right') }"
                    title="Rata Kanan (Teks / Foto)"
                    @click="setAlign('right')"
                >
                    <v-icon size="17">mdi-format-align-right</v-icon>
                </button>
                <button
                    type="button"
                    class="tool-btn"
                    :class="{ 'tool-btn--active': isAlignActive('justify') }"
                    title="Rata Kanan-Kiri (Justify Teks)"
                    @click="setAlign('justify')"
                >
                    <v-icon size="17">mdi-format-align-justify</v-icon>
                </button>
            </div>

            <div class="toolbar-divider"></div>

            <!-- Media: Link, Image, Gallery -->
            <div class="toolbar-group">
                <!-- Link Button -->
                <button
                    type="button"
                    class="tool-btn tool-btn--link"
                    :class="{ 'tool-btn--active': editor.isActive('link') }"
                    title="Sisipkan Tautan / Link"
                    @click="openLinkModal"
                >
                    <v-icon size="17">mdi-link-variant</v-icon>
                </button>

                <!-- Single Photo Upload Button -->
                <button
                    type="button"
                    class="tool-btn tool-btn--media"
                    title="Sisipkan Foto ke Naskah"
                    @click="triggerSingleImage"
                >
                    <v-icon size="17" color="#006837">mdi-image-plus</v-icon>
                    <span class="tool-btn-text">Foto</span>
                </button>

                <!-- Gallery Grid Button -->
                <button
                    type="button"
                    class="tool-btn tool-btn--gallery"
                    title="Buat Galeri Foto (Pilih 2-8 Foto)"
                    @click="triggerGalleryUpload"
                >
                    <v-icon size="17" color="#b45309">mdi-view-grid-plus-outline</v-icon>
                    <span class="tool-btn-text">Galeri</span>
                </button>
            </div>

            <div class="toolbar-divider"></div>

            <!-- Undo / Redo Group -->
            <div class="toolbar-group ml-auto">
                <button
                    type="button"
                    class="tool-btn"
                    :disabled="!editor.can().undo()"
                    title="Urungkan (Undo)"
                    @click="editor.chain().focus().undo().run()"
                >
                    <v-icon size="17">mdi-undo</v-icon>
                </button>
                <button
                    type="button"
                    class="tool-btn"
                    :disabled="!editor.can().redo()"
                    title="Ulangi (Redo)"
                    @click="editor.chain().focus().redo().run()"
                >
                    <v-icon size="17">mdi-redo</v-icon>
                </button>
            </div>
        </div>

        <!-- Canvas Writing Area -->
        <div class="editor-canvas-wrapper" @click="handleCanvasWrapperClick">
            <EditorContent :editor="editor" />
        </div>

        <!-- Footer Stats Bar -->
        <div class="editor-footer-bar">
            <div class="footer-hint">
                <v-icon size="14" color="#006837" class="mr-1">mdi-check-circle-outline</v-icon>
                <span>Canvas WYSIWYG aktif • Format tersimpan otomatis</span>
            </div>
            <div class="footer-stats">
                <span class="stat-tag">{{ wordCount }} kata</span>
            </div>
        </div>

        <!-- Link Modal Dialog -->
        <v-dialog v-model="linkDialog" max-width="440" persistent>
            <v-card rounded="xl" class="pa-5 border border-slate-100">
                <div class="d-flex align-center ga-2 mb-3 pb-2 border-b border-slate-100">
                    <v-icon size="20" color="#006837">mdi-link-variant</v-icon>
                    <span class="font-bold text-sm text-slate-800">Sisipkan Tautan (Link)</span>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Alamat URL Tujuan
                    </label>
                    <v-text-field
                        v-model="linkUrl"
                        density="compact"
                        variant="outlined"
                        placeholder="https://contoh-link.com"
                        hide-details
                        autofocus
                        @keydown.enter="applyLink"
                    />
                </div>
                <div class="d-flex align-center justify-end ga-2">
                    <button
                        v-if="editor?.isActive('link')"
                        type="button"
                        class="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 rounded-lg hover:bg-rose-100"
                        @click="removeLink"
                    >
                        Hapus Link
                    </button>
                    <button
                        type="button"
                        class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200"
                        @click="linkDialog = false"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-[#006837] rounded-lg hover:bg-[#00502b]"
                        @click="applyLink"
                    >
                        Pasang Link
                    </button>
                </div>
            </v-card>
        </v-dialog>
    </div>
</template>

<style scoped>
.rich-editor-container {
    background: #ffffff;
    border: 1px solid #dcdfe4;
    border-radius: 14px;
    overflow: hidden;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02) inset;
    position: relative;
}

.rich-editor-container:focus-within {
    border-color: #006837;
    box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.12);
}

.hidden-input {
    display: none;
}

/* Upload Progress Overlay */
.upload-progress-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(4px);
    z-index: 40;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.upload-progress-text {
    font-size: 13px;
    font-weight: 600;
    color: #006837;
}

/* ── Sticky Toolbar ── */
.editor-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
    padding: 8px 12px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    position: sticky;
    top: 0;
    z-index: 10;
}

.toolbar-group {
    display: flex;
    align-items: center;
    gap: 2px;
}

.toolbar-divider {
    width: 1px;
    height: 20px;
    background: #e2e8f0;
    margin: 0 4px;
}

.tool-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    width: 32px;
    height: 32px;
    border-radius: 7px;
    border: none;
    background: transparent;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s ease;
    font-size: 12px;
}

.tool-btn:hover:not(:disabled) {
    background: #e2e8f0;
    color: #0f172a;
}

.tool-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.tool-btn--active {
    background: #ecfdf5 !important;
    color: #006837 !important;
    font-weight: 700;
}

.heading-label {
    font-weight: 800;
    font-size: 12px;
    letter-spacing: -0.5px;
}

.tool-btn--media,
.tool-btn--gallery {
    width: auto;
    padding: 0 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
}

.tool-btn-text {
    font-size: 11.5px;
    font-weight: 600;
}

.tool-btn--media:hover {
    background: #ecfdf5 !important;
    border-color: #a7f3d0 !important;
}

.tool-btn--gallery:hover {
    background: #fef3c7 !important;
    border-color: #fde68a !important;
}

/* ── Canvas Writing Area ── */
.editor-canvas-wrapper {
    min-height: 420px;
    padding: 24px 28px;
    background: #ffffff;
    cursor: text;
}

/* ── Deep Canvas Prose Styling ── */
:deep(.editor-canvas) {
    font-size: 15px;
    line-height: 1.8;
    color: #1e293b;
    min-height: 380px;
}

:deep(.editor-canvas:focus) {
    outline: none;
}

/* Placeholder */
:deep(.editor-canvas p.is-editor-empty:first-child::before) {
    content: attr(data-placeholder);
    float: left;
    color: #94a3b8;
    pointer-events: none;
    height: 0;
}

:deep(.editor-canvas h2) {
    font-size: 21px;
    font-weight: 700;
    color: #0f172a;
    margin-top: 24px;
    margin-bottom: 10px;
    line-height: 1.35;
    letter-spacing: -0.015em;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 6px;
}

:deep(.editor-canvas h3) {
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    margin-top: 20px;
    margin-bottom: 8px;
    line-height: 1.4;
    letter-spacing: -0.01em;
}

:deep(.editor-canvas p) {
    margin-bottom: 14px;
}

:deep(.editor-canvas strong) {
    font-weight: 700;
    color: #0f172a;
}

:deep(.editor-canvas em) {
    font-style: italic;
}

:deep(.editor-canvas u) {
    text-decoration: underline;
    text-underline-offset: 3px;
}

:deep(.editor-canvas blockquote) {
    border-left: 4px solid #006837;
    background: #f0fdf4;
    padding: 14px 20px;
    margin: 20px 0;
    border-radius: 0 10px 10px 0;
    font-style: italic;
    color: #166534;
}

:deep(.editor-canvas blockquote p) {
    margin: 0;
}

:deep(.editor-canvas ul) {
    list-style-type: disc;
    padding-left: 24px;
    margin-bottom: 14px;
}

:deep(.editor-canvas ol) {
    list-style-type: decimal;
    padding-left: 24px;
    margin-bottom: 14px;
}

:deep(.editor-canvas li) {
    margin-bottom: 4px;
}

:deep(.editor-canvas hr) {
    border: none;
    border-top: 1px solid #e2e8f0;
    margin: 28px 0;
}

:deep(.editor-canvas a.editor-link) {
    color: #006837;
    text-decoration: underline;
    text-underline-offset: 3px;
    font-weight: 600;
}

:deep(.editor-canvas .article-image-block),
:deep(.editor-canvas .image-node-wrapper) {
    max-width: 100%;
}

:deep(.editor-canvas .article-image-block.align-center),
:deep(.editor-canvas .image-node-wrapper.align-center) {
    margin: 14px auto !important;
    text-align: center !important;
    display: block !important;
    float: none !important;
    clear: both !important;
}

:deep(.editor-canvas .article-image-block.align-left),
:deep(.editor-canvas .image-node-wrapper.align-left) {
    float: left !important;
    margin: 4px 20px 12px 0 !important;
    clear: left !important;
    text-align: left !important;
}

:deep(.editor-canvas .article-image-block.align-right),
:deep(.editor-canvas .image-node-wrapper.align-right) {
    float: right !important;
    margin: 4px 0 12px 20px !important;
    clear: right !important;
    text-align: right !important;
}

:deep(.editor-canvas img.editor-inline-image) {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin: 14px auto;
    display: block;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
}

:deep(.editor-canvas .article-image-caption) {
    font-size: 13px;
    color: #64748b;
    margin-top: 6px;
    text-align: center;
    font-style: italic;
    line-height: 1.5;
}

/* Gallery Grid in Editor */
:deep(.editor-canvas .article-gallery-grid) {
    display: grid;
    gap: 12px;
    margin: 22px 0;
    border-radius: 12px;
    overflow: hidden;
}

:deep(.editor-canvas .article-gallery-grid.cols-1) {
    grid-template-columns: 1fr;
}

:deep(.editor-canvas .article-gallery-grid.cols-2) {
    grid-template-columns: repeat(2, 1fr);
}

:deep(.editor-canvas .article-gallery-grid.cols-3) {
    grid-template-columns: repeat(3, 1fr);
}

:deep(.editor-canvas .gallery-photo-item) {
    aspect-ratio: 4 / 3;
    overflow: hidden;
    border-radius: 10px;
    background: #f1f5f9;
}

:deep(.editor-canvas .gallery-photo-item img) {
    width: 100%;
    height: 100%;
    object-fit: cover;
    margin: 0 !important;
    border-radius: 10px;
    display: block;
}

/* ── Footer Bar ── */
.editor-footer-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 16px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    font-size: 11.5px;
    color: #64748b;
}

.footer-hint {
    display: flex;
    align-items: center;
}

.footer-stats {
    display: flex;
    align-items: center;
}

.stat-tag {
    font-weight: 600;
    color: #334155;
    background: #e2e8f0;
    padding: 2px 8px;
    border-radius: 10px;
}
</style>
