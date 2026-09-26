import { Node } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import ImageBlockView from './ImageBlockView.vue';

export const ImageBlockExtension = Node.create({
    name: 'imageBlock',
    group: 'block',
    atom: true,
    draggable: true,
    selectable: true,

    addAttributes() {
        return {
            src: {
                default: null,
            },
            alt: {
                default: '',
            },
            width: {
                default: '100%',
            },
            align: {
                default: 'center',
            },
            aspectRatio: {
                default: 'auto',
            },
            objectPosition: {
                default: 'center',
            },
            caption: {
                default: '',
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'figure[data-type="image-block"]',
                getAttrs: (element) => {
                    const img = element.querySelector('img');
                    const figcaption = element.querySelector('figcaption');
                    return {
                        src: img?.getAttribute('src') || '',
                        alt: img?.getAttribute('alt') || '',
                        width: element.getAttribute('data-width') || '100%',
                        align: element.getAttribute('data-align') || 'center',
                        aspectRatio: element.getAttribute('data-ratio') || 'auto',
                        objectPosition: element.getAttribute('data-pos') || 'center',
                        caption: figcaption ? figcaption.innerText.trim() : '',
                    };
                },
            },
            {
                tag: 'figure.article-image-block',
                getAttrs: (element) => {
                    const img = element.querySelector('img');
                    const figcaption = element.querySelector('figcaption');
                    let align = 'center';
                    if (element.classList.contains('align-left')) align = 'left';
                    if (element.classList.contains('align-right')) align = 'right';
                    return {
                        src: img?.getAttribute('src') || '',
                        alt: img?.getAttribute('alt') || '',
                        width: element.getAttribute('data-width') || '100%',
                        align: element.getAttribute('data-align') || align,
                        aspectRatio: element.getAttribute('data-ratio') || 'auto',
                        objectPosition: element.getAttribute('data-pos') || 'center',
                        caption: figcaption ? figcaption.innerText.trim() : '',
                    };
                },
            },
            {
                tag: 'img[src]',
                getAttrs: (element) => {
                    if (
                        element.closest('.article-gallery-grid') ||
                        element.closest('figure[data-type="image-block"]')
                    ) {
                        return false;
                    }
                    return {
                        src: element.getAttribute('src'),
                        alt: element.getAttribute('alt') || '',
                        width: element.getAttribute('data-width') || element.style?.width || '100%',
                        align: element.getAttribute('data-align') || 'center',
                        aspectRatio: element.getAttribute('data-ratio') || 'auto',
                        objectPosition: 'center',
                        caption: '',
                    };
                },
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        const { src, alt, width, align, aspectRatio, objectPosition, caption } = HTMLAttributes;

        let imgStyle = `width: ${width || '100%'}; max-width: 100%;`;
        if (aspectRatio && aspectRatio !== 'auto') {
            imgStyle += ` aspect-ratio: ${aspectRatio}; object-fit: cover;`;
        }
        if (objectPosition && objectPosition !== 'center') {
            imgStyle += ` object-position: ${objectPosition};`;
        }

        const figureClasses = ['article-image-block'];
        if (align) figureClasses.push(`align-${align}`);

        const figureAttrs = {
            'data-type': 'image-block',
            'data-width': width || '100%',
            'data-align': align || 'center',
            'data-ratio': aspectRatio || 'auto',
            'data-pos': objectPosition || 'center',
            class: figureClasses.join(' '),
        };

        const imgAttrs = {
            src,
            alt: alt || '',
            style: imgStyle,
            class: 'editor-inline-image',
        };

        if (caption && caption.trim()) {
            return [
                'figure',
                figureAttrs,
                ['img', imgAttrs],
                ['figcaption', { class: 'article-image-caption' }, caption.trim()],
            ];
        }

        return [
            'figure',
            figureAttrs,
            ['img', imgAttrs],
        ];
    },

    addNodeView() {
        return VueNodeViewRenderer(ImageBlockView);
    },
});

export default ImageBlockExtension;
