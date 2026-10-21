import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import { Node, mergeAttributes } from '@tiptap/core';

/**
 * Parse daftar item statistic (JSON) menjadi array yang aman.
 */
function parseStatItems(raw) {
    if (!raw) {
        return null;
    }

    try {
        const parsed = JSON.parse(raw);

        return Array.isArray(parsed) ? parsed : null;
    } catch (error) {
        return null;
    }
}

/**
 * Konten dalam kartu statistic: nilai tunggal atau daftar nama + jumlah.
 */
function renderStatisticCardContent(attrs) {
    const content = [['p', { class: 'rich-stat-card-label' }, attrs.label || 'Statistic']];

    if (Array.isArray(attrs.items) && attrs.items.length > 0) {
        content.push([
            'ul',
            { class: 'rich-stat-card-list' },
            ...attrs.items.map((item) => {
                const cells = [['span', {}, String(item.label ?? '')]];

                if (item.count !== null && item.count !== undefined) {
                    cells.push(['span', { class: 'rich-stat-card-list-count' }, Number(item.count).toLocaleString('id-ID')]);
                }

                return ['li', {}, ...cells];
            }),
        ]);
    } else {
        content.push(['p', { class: 'rich-stat-card-value' }, String(attrs.value ?? '0')]);
    }

    return content;
}

/**
 * Node atom untuk statistic card. Menyimpan konfigurasi pada data-stat-*
 * sehingga card dapat diedit/dihapus lagi tanpa menambah tabel baru.
 */
const StatisticCard = Node.create({
    name: 'statisticCard',
    group: 'block',
    atom: true,
    draggable: true,

    addAttributes() {
        return {
            metric: { default: 'show_teater_all_setlist' },
            label: { default: 'Statistic' },
            value: { default: '0' },
            items: { default: null },
            dateFrom: { default: null },
            dateTo: { default: null },
            setlist: { default: null },
            unitSong: { default: null },
            platform: { default: null },
        };
    },

    parseHTML() {
        return [{
            tag: 'div[data-stat-metric]',
            getAttrs: (element) => ({
                metric: element.getAttribute('data-stat-metric') || 'show_teater_all_setlist',
                label: element.getAttribute('data-stat-label') || 'Statistic',
                value: element.getAttribute('data-stat-value') || '0',
                items: parseStatItems(element.getAttribute('data-stat-items')),
                dateFrom: element.getAttribute('data-stat-date-from'),
                dateTo: element.getAttribute('data-stat-date-to'),
                setlist: element.getAttribute('data-stat-setlist'),
                unitSong: element.getAttribute('data-stat-unit-song'),
                platform: element.getAttribute('data-stat-platform'),
            }),
        }];
    },

    renderHTML({ node }) {
        const attributes = { class: 'rich-stat-card' };
        const hasItems = Array.isArray(node.attrs.items) && node.attrs.items.length > 0;

        if (node.attrs.metric) attributes['data-stat-metric'] = node.attrs.metric;
        if (node.attrs.label) attributes['data-stat-label'] = node.attrs.label;
        if (hasItems) {
            attributes['data-stat-items'] = JSON.stringify(node.attrs.items);
        } else if (node.attrs.value !== null && node.attrs.value !== undefined) {
            attributes['data-stat-value'] = String(node.attrs.value);
        }
        if (node.attrs.dateFrom) attributes['data-stat-date-from'] = node.attrs.dateFrom;
        if (node.attrs.dateTo) attributes['data-stat-date-to'] = node.attrs.dateTo;
        if (node.attrs.setlist) attributes['data-stat-setlist'] = node.attrs.setlist;
        if (node.attrs.unitSong) attributes['data-stat-unit-song'] = node.attrs.unitSong;
        if (node.attrs.platform) attributes['data-stat-platform'] = node.attrs.platform;

        return [
            'div',
            mergeAttributes(attributes),
            ...renderStatisticCardContent(node.attrs),
        ];
    },
});

function runCommand(editor, command) {
    const chain = editor.chain().focus();

    switch (command) {
        case 'bold':
            return chain.toggleBold().run();
        case 'italic':
            return chain.toggleItalic().run();
        case 'underline':
            return chain.toggleUnderline().run();
        case 'strike':
            return chain.toggleStrike().run();
        case 'h2':
            return chain.toggleHeading({ level: 2 }).run();
        case 'h3':
            return chain.toggleHeading({ level: 3 }).run();
        case 'bulletList':
            return chain.toggleBulletList().run();
        case 'orderedList':
            return chain.toggleOrderedList().run();
        case 'blockquote':
            return chain.toggleBlockquote().run();
        case 'codeBlock':
            return chain.toggleCodeBlock().run();
        case 'link': {
            const url = window.prompt('URL tautan:', 'https://');

            if (url) {
                return chain.extendMarkRange('link').setLink({ href: url }).run();
            }

            return chain.extendMarkRange('link').unsetLink().run();
        }
        case 'image': {
            // Ditangani melalui input unggah berkas (lihat initRichText).
            return false;
        }
        case 'statisticCard': {
            // Ditangani melalui dialog statistic card (lihat initRichText).
            return false;
        }
        case 'undo':
            return chain.undo().run();
        case 'redo':
            return chain.redo().run();
        case 'clear':
            return chain.clearContent().run();
        default:
            return false;
    }
}

function commandIsActive(editor, command) {
    switch (command) {
        case 'bold':
            return editor.isActive('bold');
        case 'italic':
            return editor.isActive('italic');
        case 'underline':
            return editor.isActive('underline');
        case 'strike':
            return editor.isActive('strike');
        case 'h2':
            return editor.isActive('heading', { level: 2 });
        case 'h3':
            return editor.isActive('heading', { level: 3 });
        case 'bulletList':
            return editor.isActive('bulletList');
        case 'orderedList':
            return editor.isActive('orderedList');
        case 'blockquote':
            return editor.isActive('blockquote');
        case 'codeBlock':
            return editor.isActive('codeBlock');
        case 'link':
            return editor.isActive('link');
        default:
            return null;
    }
}

function refreshToolbar(root, editor) {
    root.querySelectorAll('[data-rich-text-command]').forEach((button) => {
        const isActive = commandIsActive(editor, button.dataset.richTextCommand);

        if (isActive !== null) {
            button.classList.toggle('is-active', isActive);
        }
    });
}

function escapeAttribute(value) {
    return String(value ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function buildStatisticCard(attrs) {
    const isList = Array.isArray(attrs.items) && attrs.items.length > 0;
    const attributes = {
        class: 'rich-stat-card',
        'data-stat-metric': attrs.metric || 'show_teater_all_setlist',
        'data-stat-label': attrs.label || 'Statistic',
    };

    if (isList) {
        attributes['data-stat-items'] = JSON.stringify(attrs.items);
    } else {
        attributes['data-stat-value'] = String(attrs.value ?? '0');
    }

    if (attrs.dateFrom) attributes['data-stat-date-from'] = attrs.dateFrom;
    if (attrs.dateTo) attributes['data-stat-date-to'] = attrs.dateTo;
    if (attrs.setlist) attributes['data-stat-setlist'] = attrs.setlist;
    if (attrs.unitSong) attributes['data-stat-unit-song'] = attrs.unitSong;

    const attributePairs = Object.entries(attributes)
        .map(([name, value]) => `${name}="${escapeAttribute(value)}"`)
        .join(' ');

    const labelHtml = `<p class="rich-stat-card-label">${escapeAttribute(attributes['data-stat-label'])}</p>`;
    let bodyHtml;

    if (isList) {
        bodyHtml = '<ul class="rich-stat-card-list">'.concat(attrs.items.map((item) => {
            const count = (item.count === null || item.count === undefined)
                ? ''
                : `<span class="rich-stat-card-list-count">${escapeAttribute(Number(item.count).toLocaleString('id-ID'))}</span>`;

            return `<li><span>${escapeAttribute(item.label ?? '')}</span>${count}</li>`;
        }).join(''), '</ul>');
    } else {
        bodyHtml = `<p class="rich-stat-card-value">${escapeAttribute(attributes['data-stat-value'])}</p>`;
    }

    return `<div ${attributePairs}>${labelHtml}${bodyHtml}</div>`;
}

function openStatisticDialog(root, editor) {
    const dialog = root.querySelector('[data-stat-dialog]');

    if (!dialog) {
        return;
    }

    const endpoint = root.dataset.statisticUploadUrl || '';
    const csrfToken = root.dataset.csrfToken || '';
    const setlists = JSON.parse(root.dataset.statSetlists || '[]');
    const unitSongs = JSON.parse(root.dataset.statUnitSongs || '[]');
    const metrics = JSON.parse(root.dataset.statMetrics || '[]');
    let lastResult = null;

    const metricSelect = dialog.querySelector('[data-stat-field="metric"]');
    const labelInput = dialog.querySelector('[data-stat-field="label"]');
    const dateFields = dialog.querySelector('[data-stat-dates]');
    const dateFromInput = dialog.querySelector('[data-stat-field="date_from"]');
    const dateToInput = dialog.querySelector('[data-stat-field="date_to"]');
    const setlistField = dialog.querySelector('[data-stat-setlist]');
    const setlistSelect = dialog.querySelector('[data-stat-field="setlist"]');
    const unitSongField = dialog.querySelector('[data-stat-unit-song]');
    const unitSongSelect = dialog.querySelector('[data-stat-field="unit_song"]');
    const preview = dialog.querySelector('[data-stat-preview]');

    function currentMetric() {
        return metricSelect.value;
    }

    function metricConfig(metric) {
        return metrics.find((item) => item.value === metric) || metrics[0] || {};
    }

    function toggleFields() {
        const config = metricConfig(currentMetric());
        dateFields.classList.toggle('hidden', !config.date);
        setlistField.classList.toggle('hidden', !config.setlist);
        unitSongField.classList.toggle('hidden', !config.unit_song);
    }

    function prefill() {
        const config = metricConfig(metrics[0] && metrics[0].value);
        metricSelect.value = config.value || 'show_teater_all_setlist';
        labelInput.value = config.label || 'Statistic';
        dateFromInput.value = '';
        dateToInput.value = '';
        setlistSelect.value = '';
        unitSongSelect.value = '';
        toggleFields();
        updatePreview();
    }

    async function updatePreview() {
        if (!endpoint) {
            return;
        }

        const payload = {
            metric: currentMetric(),
            label: labelInput.value,
            date_from: dateFromInput.value || null,
            date_to: dateToInput.value || null,
            setlist: setlistSelect.value || null,
            unit_song: unitSongSelect.value || null,
        };

        preview.textContent = '…';

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                throw new Error('Request failed');
            }

            const data = await response.json();
            lastResult = data;

            if (data.type === 'list') {
                preview.textContent = `${(data.items || []).length} item`;
            } else {
                preview.textContent = Number(data.value || 0).toLocaleString('id-ID');
            }
        } catch (error) {
            lastResult = null;
            preview.textContent = '—';
        }
    }

    function close() {
        dialog.classList.add('hidden');
        dialog.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function open() {
        prefill();
        dialog.classList.remove('hidden');
        dialog.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    metricSelect.addEventListener('change', () => {
        const config = metricConfig(currentMetric());
        labelInput.value = config.label;
        toggleFields();
        updatePreview();
    });

    [labelInput, dateFromInput, dateToInput, setlistSelect, unitSongSelect].forEach((field) => {
        field.addEventListener('change', updatePreview);
    });

    dialog.querySelector('[data-stat-cancel]').addEventListener('click', close);

    dialog.querySelector('[data-stat-insert]').addEventListener('click', () => {
        const html = buildStatisticCard({
            metric: currentMetric(),
            label: labelInput.value,
            value: lastResult ? lastResult.value : 0,
            items: lastResult && lastResult.type === 'list' ? lastResult.items : null,
            dateFrom: dateFromInput.value || null,
            dateTo: dateToInput.value || null,
            setlist: setlistSelect.value || null,
            unitSong: unitSongSelect.value || null,
        });

        editor.chain().focus().insertContent(html).run();
        close();
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !dialog.classList.contains('hidden')) {
            close();
        }
    });

    open();
}

function initRichText(root) {
    if (root.dataset.richTextReady === 'true') {
        return;
    }
    const canvas = root.querySelector('[data-rich-text-canvas]');
    const input = root.querySelector('[data-rich-text-input]');

    if (!canvas || !input) {
        return;
    }

    root.dataset.richTextReady = 'true';

    const editor = new Editor({
        element: canvas,
        extensions: [
            StarterKit.configure({
                link: {
                    openOnClick: false,
                    autolink: true,
                },
            }),
            Image,
            StatisticCard,
            Placeholder.configure({
                placeholder: input.dataset.placeholder || 'Tulis konten di sini...',
            }),
        ],
        content: input.value || '',
        onUpdate: ({ editor }) => {
            input.value = editor.getHTML();
        },
        onSelectionUpdate: () => {
            refreshToolbar(root, editor);
        },
        onTransaction: () => {
            refreshToolbar(root, editor);
        },
    });

    const imageInput = root.querySelector('[data-rich-text-image-input]');
    const imageUploadUrl = root.dataset.imageUploadUrl;
    const csrfToken = root.dataset.csrfToken || '';

    if (imageInput && imageUploadUrl) {
        imageInput.addEventListener('change', async () => {
            const file = imageInput.files && imageInput.files[0];

            if (!file) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('image', file);

                const response = await fetch(imageUploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error('Upload gagal');
                }

                const data = await response.json();

                if (data.url) {
                    editor.chain().focus().setImage({ src: data.url }).run();
                }
            } catch (error) {
                window.appAlert('Gagal mengunggah gambar. Pastikan berkas berupa gambar (maks. 5MB).', { variant: 'error', title: 'Unggah Gagal' });
            } finally {
                imageInput.value = '';
            }
        });
    }

    root.querySelectorAll('[data-rich-text-command]').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.dataset.richTextCommand === 'image' && imageInput) {
                imageInput.click();

                return;
            }

            if (button.dataset.richTextCommand === 'statisticCard') {
                openStatisticDialog(root, editor);

                return;
            }

            runCommand(editor, button.dataset.richTextCommand);
        });
    });

    const form = root.closest('form');

    if (form) {
        form.addEventListener('submit', () => {
            input.value = editor.getHTML();
        });
    }
}

function initAllRichText() {
    document.querySelectorAll('[data-rich-text]').forEach(initRichText);
}

document.addEventListener('DOMContentLoaded', initAllRichText);
document.addEventListener('livewire:navigated', initAllRichText);

window.initRichText = initAllRichText;

window.openMediaLightbox = function (trigger) {    const root = document.getElementById('media-lightbox');

    if (!root) {
        return;
    }

    const image = document.getElementById('media-lightbox-image');
    const title = document.getElementById('media-lightbox-title');
    const description = document.getElementById('media-lightbox-description');
    const credit = document.getElementById('media-lightbox-credit');
    const closeButton = document.getElementById('media-lightbox-close');
    const backdrop = document.getElementById('media-lightbox-backdrop');

    const imageSrc = trigger.dataset.lightboxImage || '';
    if (imageSrc) {
        image.src = imageSrc;
        image.classList.remove('hidden');
    } else {
        image.removeAttribute('src');
        image.classList.add('hidden');
    }

    const titleText = trigger.dataset.lightboxTitle || '';
    title.textContent = titleText;
    title.classList.toggle('hidden', titleText === '');

    const descriptionText = trigger.dataset.lightboxDescription || '';
    description.textContent = descriptionText;
    description.classList.toggle('hidden', descriptionText === '');

    const creditText = trigger.dataset.lightboxCredit || '';
    credit.textContent = creditText;
    credit.classList.toggle('hidden', creditText === '');

    function close() {
        root.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        document.removeEventListener('keydown', onKey);
        closeButton.removeEventListener('click', close);
        backdrop.removeEventListener('click', close);
    }

    function onKey(event) {
        if (event.key === 'Escape') {
            close();
        }
    }

    root.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    closeButton.addEventListener('click', close);
    backdrop.addEventListener('click', close);
    document.addEventListener('keydown', onKey);
    closeButton.focus();
};

// ---------------------------------------------------------------------------
// Modal-based alerts (mengadopsi pola modal TailAdmin), menggantikan alert()
// dan confirm() bawaan browser.
// ---------------------------------------------------------------------------
window.appAlertDialog = function () {
    return {
        open: false,
        mode: 'alert',
        variant: 'info',
        title: '',
        message: '',
        confirmText: 'OK',
        cancelText: 'Batal',
        resolver: null,

        openAlert(detail = {}) {
            this.setup({ ...detail, mode: 'alert', confirmText: detail.confirmText || 'OK', variant: detail.variant || 'info' });
        },

        openConfirm(detail = {}) {
            this.setup({ ...detail, mode: 'confirm', confirmText: detail.confirmText || 'Ya', cancelText: detail.cancelText || 'Batal', variant: detail.variant || 'warning' });
        },

        setup(detail) {
            this.mode = detail.mode;
            this.variant = ['success', 'error', 'warning', 'info'].includes(detail.variant) ? detail.variant : 'info';
            this.title = detail.title || '';
            this.message = detail.message || '';
            this.confirmText = detail.confirmText;
            this.cancelText = detail.cancelText || 'Batal';
            this.resolver = typeof detail.resolver === 'function' ? detail.resolver : null;
            this.open = true;
            document.body.style.overflow = 'hidden';
        },

        accept() {
            this.close(true);
        },

        cancel() {
            this.close(false);
        },

        close(result) {
            this.open = false;
            document.body.style.overflow = '';

            const resolver = this.resolver;
            this.resolver = null;

            if (resolver) {
                resolver(result);
            }
        },

        iconWrapperClass() {
            return {
                success: 'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-400',
                error: 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400',
                warning: 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400',
                info: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400',
            }[this.variant];
        },

        confirmButtonClass() {
            return {
                success: 'bg-green-600 text-white hover:bg-green-500',
                error: 'bg-red-600 text-white hover:bg-red-500',
                warning: 'bg-amber-500 text-white hover:bg-amber-400',
                info: 'bg-indigo-600 text-white hover:bg-indigo-500',
            }[this.variant];
        },
    };
};

window.appAlert = function (message, options = {}) {
    return new Promise((resolve) => {
        window.dispatchEvent(new CustomEvent('app-alert', {
            detail: {
                message: message || '',
                title: options.title || '',
                variant: options.variant || 'info',
                confirmText: options.confirmText || 'OK',
                resolver: resolve,
            },
        }));
    });
};

window.appConfirm = function (message, options = {}) {
    return new Promise((resolve) => {
        window.dispatchEvent(new CustomEvent('app-confirm', {
            detail: {
                message: message || '',
                title: options.title || 'Konfirmasi',
                variant: options.variant || 'warning',
                confirmText: options.confirmText || 'Ya',
                cancelText: options.cancelText || 'Batal',
                resolver: resolve,
            },
        }));
    });
};
