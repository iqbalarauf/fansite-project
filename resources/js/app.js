import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';

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
                window.alert('Gagal mengunggah gambar. Pastikan berkas berupa gambar (maks. 5MB).');
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

window.openMediaLightbox = function (trigger) {
    const root = document.getElementById('media-lightbox');

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
