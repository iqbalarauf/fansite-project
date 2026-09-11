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
            const url = window.prompt('URL gambar:', 'https://');

            if (url) {
                return chain.setImage({ src: url }).run();
            }

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

    root.querySelectorAll('[data-rich-text-command]').forEach((button) => {
        button.addEventListener('click', () => {
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
