import { domToPng } from 'modern-screenshot';

const thumbnailFormat = Object.freeze({
    label: 'YouTube thumbnail',
    width: 1280,
    height: 720,
    exportScale: 2,
});

const backgroundVariants = Object.freeze([
    Object.freeze({
        id: 'dark',
        label: 'Dark',
        asset: '/img/youtube-templates/youtube-bg-dark.webp',
        logo: '/img/youtube-templates/laravel-cloud-on-dark.svg',
        baseFill: '#00001e',
        figmaNode: '4070:370747',
    }),
    Object.freeze({
        id: 'light',
        label: 'Light',
        asset: '/img/youtube-templates/youtube-bg-light.webp',
        logo: '/img/youtube-templates/laravel-cloud-on-light.svg',
        baseFill: '#e9f3ff',
        figmaNode: '4070:370786',
    }),
    Object.freeze({
        id: 'blue',
        label: 'Blue',
        asset: '/img/youtube-templates/youtube-bg-blue.webp',
        logo: '/img/youtube-templates/laravel-cloud-on-dark.svg',
        baseFill: '#0057ff',
        figmaNode: '4070:370825',
    }),
]);

const subjectZoneStart = 62.578125;
const subjectScaleLimits = Object.freeze({ min: 28, max: 72 });

document.addEventListener('alpine:init', () => {
    window.Alpine.data('imageStudio', () => ({
        headline: 'Ship with confidence',
        headlineError: '',
        headlineLineCount: 2,
        isHeadlineMeasuring: true,
        background: 'blue',
        subject: {
            type: 'person',
            src: null,
            name: '',
            x: 98,
            y: 47,
            scale: 68,
        },
        isSubjectSelected: false,
        dragState: null,
        resizeState: null,
        isExporting: false,
        statusMessage: 'Add a transparent subject or export the locked layout as-is.',

        init() {
            document.fonts.ready.then(async () => {
                await document.fonts.load('700 128px "Instrument Sans Condensed"');
                this.queueHeadlineValidation();
            });
        },

        get activeBackground() {
            return backgroundVariants.find((option) => option.id === this.background) ?? backgroundVariants[2];
        },

        get displayHeadline() {
            return this.headline.toUpperCase();
        },

        get artboardClassNames() {
            return [`artboard--${this.background}`, this.isExporting ? 'is-exporting' : ''].filter(Boolean).join(' ');
        },

        get artboardStyle() {
            return `--artboard-ratio: ${thumbnailFormat.width / thumbnailFormat.height}; aspect-ratio: ${thumbnailFormat.width} / ${thumbnailFormat.height};`;
        },

        get subjectStyle() {
            return `--subject-x: ${this.subject.x}%; --subject-y: ${this.subject.y}%; --subject-size: ${this.subject.scale}%;`;
        },

        get canExport() {
            return !this.isExporting && !this.isHeadlineMeasuring && this.displayHeadline.trim() !== '' && this.headlineError === '';
        },

        get exportButtonLabel() {
            return this.isExporting ? 'Rendering…' : 'Export PNG';
        },

        selectBackground(event) {
            const background = event.currentTarget.dataset.background;

            if (!backgroundVariants.some((option) => option.id === background)) {
                return;
            }

            this.background = background;
            this.statusMessage = `${this.activeBackground.label} background applied.`;
        },

        normalizeHeadline() {
            this.headline = this.headline.trim().replace(/[.!?,;:]+$/u, '');
            this.queueHeadlineValidation();
        },

        queueHeadlineValidation() {
            this.isHeadlineMeasuring = true;

            this.$nextTick(() => {
                requestAnimationFrame(() => this.validateHeadline());
            });
        },

        validateHeadline() {
            const headline = this.displayHeadline.trim();
            const headlineElement = this.$refs.headline;
            let error = '';

            if (headline === '') {
                this.headlineLineCount = 0;
                this.headlineError = 'Add a headline before exporting.';
                this.isHeadlineMeasuring = false;
                return;
            }

            if (headline.length > 56) {
                error = 'Keep the headline to 56 characters or fewer.';
            } else if (/LARAVEL\s+CLOUD/iu.test(headline)) {
                error = 'Remove “Laravel Cloud” from the headline; the logo already says it.';
            } else if (/[.!?,;:]$/u.test(headline)) {
                error = 'Remove trailing punctuation.';
            }

            if (headlineElement) {
                const range = document.createRange();
                range.selectNodeContents(headlineElement);
                const lineTops = new Set(
                    [...range.getClientRects()].filter((rectangle) => rectangle.width > 0).map((rectangle) => Math.round(rectangle.top * 2) / 2)
                );

                this.headlineLineCount = lineTops.size;

                if (!error && (this.headlineLineCount > 4 || headlineElement.scrollHeight > headlineElement.parentElement.clientHeight + 1)) {
                    error = 'Shorten the headline to four lines or fewer.';
                } else if (!error && headlineElement.scrollWidth > headlineElement.clientWidth + 1) {
                    error = 'Shorten the longest word so it fits the copy box.';
                }
            }

            this.headlineError = error;
            this.isHeadlineMeasuring = false;
        },

        async handleSubjectUpload(event) {
            const upload = event.currentTarget;
            const [file] = [...(upload?.files ?? [])];

            if (!file || file.type !== 'image/png' || file.size > 15 * 1024 * 1024) {
                this.statusMessage = 'Choose one transparent PNG under 15 MB.';
                this.resetFileInput(upload);
                return;
            }

            let hasTransparency = false;
            let subjectSource = null;

            try {
                subjectSource = await this.readFile(file);
                hasTransparency = await this.imageHasTransparency(subjectSource);
            } catch {
                this.statusMessage = 'This PNG could not be read. Choose a different subject image.';
                this.resetFileInput(upload);
                return;
            }

            if (!hasTransparency) {
                this.statusMessage = 'This PNG has no transparent background. Upload an isolated cutout.';
                this.resetFileInput(upload);
                return;
            }

            this.subject = {
                type: 'person',
                src: subjectSource,
                name: file.name.replace(/\.png$/iu, ''),
                x: 98,
                y: 47,
                scale: 68,
            };
            this.isSubjectSelected = true;
            this.statusMessage = `${this.subject.name} added with the approved subject treatment.`;
            this.resetFileInput(upload);
        },

        async imageHasTransparency(source) {
            const image = new Image();
            image.src = source;
            await image.decode();

            const scale = Math.min(1, 256 / Math.max(image.naturalWidth, image.naturalHeight));
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
            canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));

            const context = canvas.getContext('2d', { willReadFrequently: true });
            context.drawImage(image, 0, 0, canvas.width, canvas.height);
            const pixels = context.getImageData(0, 0, canvas.width, canvas.height).data;

            for (let index = 3; index < pixels.length; index += 4) {
                if (pixels[index] < 250) {
                    return true;
                }
            }

            return false;
        },

        readFile(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.addEventListener('load', () => resolve(reader.result));
                reader.addEventListener('error', () => reject(reader.error));
                reader.readAsDataURL(file);
            });
        },

        resetFileInput(upload) {
            if (typeof upload?.clear === 'function') {
                upload.clear();
                return;
            }

            const input = upload?.querySelector?.('input[type="file"]');

            if (input) {
                input.value = '';
            }
        },

        removeSubject() {
            this.subject = { type: 'person', src: null, name: '', x: 98, y: 47, scale: 68 };
            this.isSubjectSelected = false;
            this.statusMessage = 'Subject removed.';
        },

        selectSubject() {
            this.isSubjectSelected = true;
        },

        deselectSubject() {
            this.isSubjectSelected = false;
        },

        startSubjectDrag(event) {
            const canvas = this.$root.querySelector('[data-image-studio-canvas]');

            if (!canvas || !this.subject.src) {
                return;
            }

            event.currentTarget.setPointerCapture?.(event.pointerId);
            this.isSubjectSelected = true;
            this.resizeState = null;
            this.dragState = {
                pointerId: event.pointerId,
                startClientX: event.clientX,
                startClientY: event.clientY,
                startX: this.subject.x,
                startY: this.subject.y,
                canvasRect: canvas.getBoundingClientRect(),
            };
        },

        startSubjectResize(event) {
            const subjectElement = event.currentTarget.closest('.artboard-subject');

            if (!subjectElement || !this.subject.src) {
                return;
            }

            const subjectRect = subjectElement.getBoundingClientRect();
            const centerClientX = subjectRect.left + subjectRect.width / 2;
            const centerClientY = subjectRect.top + subjectRect.height / 2;
            const startDistance = Math.hypot(event.clientX - centerClientX, event.clientY - centerClientY);

            if (startDistance < 1) {
                return;
            }

            event.currentTarget.setPointerCapture?.(event.pointerId);
            this.isSubjectSelected = true;
            this.dragState = null;
            this.resizeState = {
                pointerId: event.pointerId,
                startScale: this.subject.scale,
                centerClientX,
                centerClientY,
                startDistance,
            };
        },

        movePointer(event) {
            if (this.resizeState && event.pointerId === this.resizeState.pointerId) {
                const distance = Math.hypot(event.clientX - this.resizeState.centerClientX, event.clientY - this.resizeState.centerClientY);
                this.subject.scale = this.clamp(
                    this.resizeState.startScale * (distance / this.resizeState.startDistance),
                    subjectScaleLimits.min,
                    subjectScaleLimits.max
                );
                this.constrainSubject();
                return;
            }

            if (!this.dragState || event.pointerId !== this.dragState.pointerId) {
                return;
            }

            const deltaX = ((event.clientX - this.dragState.startClientX) / this.dragState.canvasRect.width) * 100;
            const deltaY = ((event.clientY - this.dragState.startClientY) / this.dragState.canvasRect.height) * 100;

            this.subject.x = this.dragState.startX + deltaX;
            this.subject.y = this.dragState.startY + deltaY;
            this.constrainSubject();
        },

        stopPointer(event) {
            if (this.resizeState && event.pointerId === this.resizeState.pointerId) {
                this.resizeState = null;
                this.statusMessage = 'Subject resized inside its safe zone.';
            }

            if (this.dragState && event.pointerId === this.dragState.pointerId) {
                this.dragState = null;
                this.statusMessage = 'Subject repositioned inside its safe zone.';
            }
        },

        constrainSubject() {
            const minimumX = subjectZoneStart + this.subject.scale / 2;

            this.subject.x = this.clamp(this.subject.x, minimumX, 112);
            this.subject.y = this.clamp(this.subject.y, -10, 108);
        },

        clamp(value, minimum, maximum) {
            return Math.min(maximum, Math.max(minimum, value));
        },

        handleKeyboard(event) {
            const isFormControl = ['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName);

            if (isFormControl || !this.subject.src || !this.isSubjectSelected) {
                return;
            }

            if (['Backspace', 'Delete'].includes(event.key)) {
                event.preventDefault();
                this.removeSubject();
                return;
            }

            const movement = event.shiftKey ? 2 : 0.5;
            const movements = {
                ArrowLeft: [-movement, 0],
                ArrowRight: [movement, 0],
                ArrowUp: [0, -movement],
                ArrowDown: [0, movement],
            };

            if (!movements[event.key]) {
                return;
            }

            event.preventDefault();
            this.subject.x += movements[event.key][0];
            this.subject.y += movements[event.key][1];
            this.constrainSubject();
        },

        sanitizeExportClone(clone) {
            if (!(clone instanceof Element)) {
                return;
            }

            clone.getAttributeNames().forEach((attributeName) => {
                if (attributeName.startsWith('x-') || attributeName.startsWith('@') || attributeName.startsWith(':')) {
                    clone.removeAttribute(attributeName);
                }
            });
        },

        createExportCanvas(canvas) {
            const exportCanvas = canvas.cloneNode(true);

            exportCanvas.setAttribute('data-image-studio-export-canvas', '');
            exportCanvas.removeAttribute('data-image-studio-canvas');
            exportCanvas.classList.add('is-exporting');
            exportCanvas.querySelectorAll('[data-export-ignore]').forEach((element) => element.remove());
            [exportCanvas, ...exportCanvas.querySelectorAll('*')].forEach((element) => this.sanitizeExportClone(element));

            Object.assign(exportCanvas.style, {
                position: 'fixed',
                top: '0',
                left: '-100000px',
                width: `${thumbnailFormat.width}px`,
                minWidth: `${thumbnailFormat.width}px`,
                maxWidth: 'none',
                height: `${thumbnailFormat.height}px`,
                minHeight: `${thumbnailFormat.height}px`,
                maxHeight: 'none',
                aspectRatio: `${thumbnailFormat.width} / ${thumbnailFormat.height}`,
                boxShadow: 'none',
                pointerEvents: 'none',
            });

            exportCanvas.inert = true;
            exportCanvas.setAttribute('aria-hidden', 'true');
            document.body.append(exportCanvas);

            return exportCanvas;
        },

        async waitForImages(canvas) {
            await Promise.all(
                [...canvas.querySelectorAll('img')].map(async (image) => {
                    if (!image.complete) {
                        await new Promise((resolve) => image.addEventListener('load', resolve, { once: true }));
                    }

                    if (typeof image.decode === 'function') {
                        await image.decode().catch(() => undefined);
                    }
                })
            );
        },

        async exportPng() {
            const canvas = this.$root.querySelector('[data-image-studio-canvas]');
            let exportCanvas = null;

            this.validateHeadline();

            if (!canvas || canvas.offsetWidth === 0 || !this.canExport) {
                this.statusMessage = this.headlineError || 'The canvas is not ready to export yet.';
                return;
            }

            this.isExporting = true;
            this.statusMessage = 'Rendering full-resolution PNG…';

            try {
                await document.fonts.ready;
                await document.fonts.load('700 128px "Instrument Sans Condensed"');
                exportCanvas = this.createExportCanvas(canvas);
                await this.waitForImages(exportCanvas);
                await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));

                const dataUrl = await domToPng(exportCanvas, {
                    width: thumbnailFormat.width,
                    height: thumbnailFormat.height,
                    scale: thumbnailFormat.exportScale,
                    style: {
                        width: `${thumbnailFormat.width}px`,
                        height: `${thumbnailFormat.height}px`,
                        maxWidth: 'none',
                        maxHeight: 'none',
                    },
                    backgroundColor: this.activeBackground.baseFill,
                    onCloneEachNode: (clone) => this.sanitizeExportClone(clone),
                    filter: (node) => !(node instanceof Element && node.hasAttribute('data-export-ignore')),
                });

                const link = document.createElement('a');
                link.download = `${this.slugify(this.headline)}-thumbnail.png`;
                link.href = dataUrl;
                link.click();
                this.statusMessage = '2560 × 1440 PNG exported.';
            } catch (error) {
                console.error(error);
                this.statusMessage = 'Export failed. Check the browser console for details.';
            } finally {
                exportCanvas?.remove();
                this.isExporting = false;
            }
        },

        slugify(value) {
            return (
                value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/gu, '-')
                    .replace(/^-|-$/gu, '')
                    .slice(0, 48) || 'laravel-cloud'
            );
        },
    }));
});
