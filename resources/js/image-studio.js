import { domToPng } from 'modern-screenshot';

const thumbnailFormat = {
    label: 'YouTube thumbnail',
    width: 1280,
    height: 720,
    exportScale: 2,
};

const thumbnailTemplates = {
    'starter-kits': {
        label: 'Announcement',
        brand: 'laravel',
        figmaNode: '27:217728',
        fontLabel: 'Instrument Sans Medium',
        logo: null,
        alignment: 'left',
        headlineSize: 100,
        eyebrow: 'Introducing',
        title: 'A better way to ship Laravel',
        supportingText: '',
        slotLabel: 'Add a focused image',
        imageSlots: [{ x: 84, y: 55, size: 55, rotation: 0, layer: 'above' }],
    },
    'cloud-bill': {
        label: 'Product',
        brand: 'cloud',
        figmaNode: '5:272000',
        fontLabel: 'Instrument Sans Medium',
        logo: '/img/youtube-templates/cloud-bill-logo.svg',
        alignment: 'left',
        headlineSize: 100,
        eyebrow: 'Product deep dive',
        title: 'See your Laravel app clearly',
        supportingText: '',
        slotLabel: 'Add focused product image',
        imageSlots: [{ x: 76, y: 38, size: 42, rotation: -8, layer: 'behind' }],
    },
    'cloud-ama': {
        label: 'Interview',
        brand: 'cloud',
        figmaNode: '27:218797',
        fontLabel: 'Instrument Sans Medium',
        logo: '/img/youtube-templates/cloud-ama-logo.svg',
        alignment: 'left',
        headlineSize: 100,
        eyebrow: 'In conversation',
        title: 'Guest name',
        supportingText: 'with',
        slotLabel: 'Add guest cutout',
        imageSlots: [{ x: 94, y: 66, size: 56, rotation: 0, layer: 'above' }],
    },
    'laravel-cloud-youtube': {
        label: 'Cloud',
        brand: 'cloud',
        figmaNode: '4070:370825',
        fontLabel: 'Instrument Sans Condensed Bold',
        logo: '/img/youtube-templates/laravel-cloud-youtube-logo.svg',
        alignment: 'left',
        headlineSize: 100,
        eyebrow: '',
        title: 'Laravel Cloud on Stripe Projects',
        supportingText: '',
        slotLabel: 'Add presenter cutout',
        imageSlots: [{ x: 94, y: 66, size: 56, rotation: 0, layer: 'above' }],
    },
};

const templates = Object.keys(thumbnailTemplates);

document.addEventListener('alpine:init', () => {
    window.Alpine.data('imageStudio', () => ({
        brand: 'laravel',
        template: 'starter-kits',
        alignment: 'left',
        eyebrow: 'Introducing',
        title: 'A better way to ship Laravel',
        supportingText: '',
        headlineSize: 100,
        backgroundImage: null,
        imageLibrary: [],
        placedImages: [],
        selectedImageId: null,
        selectedImageSize: 46,
        selectedImageRotation: 0,
        copyX: null,
        copyY: null,
        copyDragState: null,
        logoX: null,
        logoY: null,
        logoScale: 100,
        logoDragState: null,
        dragState: null,
        resizeState: null,
        isExporting: false,
        variationIndex: 0,
        showSafeAreas: false,
        statusMessage: 'Ready to design.',

        init() {
            this.loadSharedImages();
        },

        get selectedFormat() {
            return thumbnailFormat;
        },

        get formatDescription() {
            return `${this.selectedFormat.label} · ${this.selectedFormat.width} × ${this.selectedFormat.height}`;
        },

        get exportWidth() {
            return this.selectedFormat.width * this.selectedFormat.exportScale;
        },

        get exportHeight() {
            return this.selectedFormat.height * this.selectedFormat.exportScale;
        },

        get exportButtonLabel() {
            return this.isExporting ? 'Rendering…' : 'Export PNG';
        },

        get brandName() {
            return this.brand === 'cloud' ? 'Laravel Cloud' : 'Laravel';
        },

        get brandLogo() {
            return this.activeTemplate.logo;
        },

        get activeTemplate() {
            return thumbnailTemplates[this.template];
        },

        get templateSlotLabel() {
            return this.activeTemplate?.slotLabel ?? '';
        },

        get copyFontLabel() {
            return this.activeTemplate.fontLabel;
        },

        get hasEyebrow() {
            return this.activeTemplate.eyebrow !== '';
        },

        get hasSupportingText() {
            return this.activeTemplate.supportingText !== '';
        },

        get artboardClassNames() {
            return [
                `artboard--${this.brand}`,
                `artboard--${this.template}`,
                'artboard--thumbnail',
                this.backgroundImage ? 'artboard--custom-background' : '',
                this.isExporting ? 'is-exporting' : '',
            ];
        },

        get artboardStyle() {
            const ratio = this.selectedFormat.width / this.selectedFormat.height;

            return `--artboard-ratio: ${ratio}; aspect-ratio: ${this.selectedFormat.width} / ${this.selectedFormat.height};`;
        },

        get copyStyle() {
            const position =
                this.copyX === null || this.copyY === null ? '' : `left: ${this.copyX}%; top: ${this.copyY}%; right: auto; bottom: auto;`;

            return `${position} text-align: ${this.alignment};`;
        },

        get headlineStyle() {
            return `--headline-scale: ${this.headlineSize / 100};`;
        },

        get logoStyle() {
            const position =
                this.logoX === null || this.logoY === null ? '' : `left: ${this.logoX}%; top: ${this.logoY}%; right: auto; bottom: auto;`;

            return `${position} --logo-scale: ${this.logoScale / 100};`;
        },

        get selectedImage() {
            return this.placedImages.find((imageLayer) => imageLayer.id === this.selectedImageId) ?? null;
        },

        get reversedPlacedImages() {
            return [...this.placedImages].reverse();
        },

        get savedImageCount() {
            return this.imageLibrary.filter((imageLayer) => imageLayer.isSaved).length;
        },

        selectTemplate(event) {
            this.applyTemplate(event.currentTarget.dataset.template);
        },

        selectAlignment(event) {
            this.alignment = event.currentTarget.dataset.alignment;
        },

        templateLabel(template) {
            return thumbnailTemplates[template]?.label ?? template;
        },

        applyTemplate(template, announce = true) {
            const definition = thumbnailTemplates[template];

            if (!definition) {
                return;
            }

            this.template = template;

            if (definition.brand) {
                this.brand = definition.brand;
            }

            this.alignment = definition.alignment;
            this.headlineSize = definition.headlineSize;
            this.eyebrow = definition.eyebrow;
            this.title = definition.title;
            this.supportingText = definition.supportingText;
            this.resetCopyPosition();
            this.resetLogoLayer();

            this.placedImages.forEach((imageLayer, index) => {
                const positions = this.imagePositionsForTemplate(index);
                imageLayer.x = positions.x;
                imageLayer.y = positions.y;
                imageLayer.size = positions.size;
                imageLayer.rotation = positions.rotation;
                imageLayer.layer = positions.layer;
                this.refreshImageStyle(imageLayer);
            });

            if (this.selectedImage) {
                this.loadSelectedImageControls();
            }

            if (announce) {
                this.statusMessage = `${definition.label} thumbnail template applied.`;
            }
        },

        resetTemplate() {
            this.applyTemplate(this.template, false);
            this.statusMessage = `${this.templateLabel(this.template)} template reset to its defaults.`;
        },

        generateVariation() {
            this.variationIndex += 1;
            this.applyTemplate(templates[this.variationIndex % templates.length], false);

            this.statusMessage = `${this.templateLabel(this.template)} variation generated.`;
        },

        imagePositionsForTemplate(index) {
            const slot = this.activeTemplate.imageSlots[index];

            if (slot) {
                return { ...slot };
            }

            const offset = Math.min(index * 8, 24);

            return { x: 78 - offset, y: 62, size: 34, rotation: index % 2 === 0 ? -2 : 2, layer: 'behind' };
        },

        async handleImageUpload(event) {
            const upload = event.currentTarget;
            const files = this.filesFromUpload(upload);
            const validFiles = files.filter((file) => ['image/png', 'image/jpeg', 'image/webp'].includes(file.type) && file.size <= 15 * 1024 * 1024);

            if (validFiles.length === 0) {
                this.statusMessage = 'Choose PNG, JPG, or WebP image layers under 15 MB.';
                this.resetFileInput(upload);
                return;
            }

            const assets = await Promise.all(
                validFiles.map(async (file) => ({
                    id: window.crypto.randomUUID(),
                    name: file.name.replace(/\.(png|jpe?g|webp)$/i, ''),
                    src: await this.readFile(file),
                    file,
                    isSaved: false,
                    isSaving: false,
                }))
            );

            this.imageLibrary.push(...assets);
            this.statusMessage = `${assets.length} ${assets.length === 1 ? 'image is' : 'images are'} ready to use or save for everyone.`;
            this.resetFileInput(upload);
        },

        async loadSharedImages() {
            try {
                const response = await fetch(this.$root.dataset.sharedImagesIndexUrl, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('The shared image library could not be loaded.');
                }

                const payload = await response.json();
                const localIds = new Set(this.imageLibrary.map((imageLayer) => imageLayer.id));
                this.imageLibrary.unshift(...payload.data.filter((imageLayer) => !localIds.has(imageLayer.id)));
            } catch {
                this.statusMessage = 'The shared image library is temporarily unavailable.';
            }
        },

        async saveImage(event) {
            const asset = this.imageLibrary.find((imageLayer) => imageLayer.id === event.currentTarget.dataset.imageId);

            if (!asset || asset.isSaved || asset.isSaving || !asset.file) {
                return;
            }

            asset.isSaving = true;
            const formData = new FormData();
            formData.append('images[]', asset.file);

            try {
                const response = await fetch(this.$root.dataset.sharedImagesStoreUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error('The image could not be saved.');
                }

                const payload = await response.json();
                const [savedAsset] = payload.data;

                this.placedImages
                    .filter((imageLayer) => imageLayer.assetId === asset.id)
                    .forEach((imageLayer) => {
                        imageLayer.src = savedAsset.src;
                        imageLayer.name = savedAsset.name;
                    });

                asset.name = savedAsset.name;
                asset.src = savedAsset.src;
                asset.file = null;
                asset.isSaved = true;
                this.statusMessage = `${asset.name} is now shared with everyone.`;
            } catch {
                this.statusMessage = `${asset.name} could not be saved. Try again.`;
            } finally {
                asset.isSaving = false;
            }
        },

        async handleBackgroundUpload(event) {
            const upload = event.currentTarget;
            const [file] = this.filesFromUpload(upload);

            if (!file || !['image/png', 'image/jpeg', 'image/webp'].includes(file.type) || file.size > 15 * 1024 * 1024) {
                this.statusMessage = 'Choose a PNG, JPG, or WebP background under 15 MB.';
                this.resetFileInput(upload);
                return;
            }

            this.backgroundImage = await this.readFile(file);
            this.statusMessage = 'Approved artwork added beneath the brand grid.';
            this.resetFileInput(upload);
        },

        filesFromUpload(upload) {
            return [...(upload?.files ?? [])];
        },

        resetFileInput(upload) {
            if (typeof upload?.clear === 'function') {
                upload.clear();
            }
        },

        removeBackground() {
            this.backgroundImage = null;
            this.statusMessage = `${this.brandName} grid restored.`;
        },

        readFile(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.addEventListener('load', () => resolve(reader.result));
                reader.addEventListener('error', () => reject(reader.error));
                reader.readAsDataURL(file);
            });
        },

        addImage(event) {
            const asset = this.imageLibrary.find((imageLayer) => imageLayer.id === event.currentTarget.dataset.imageId);

            if (!asset) {
                return;
            }

            const positions = this.imagePositionsForTemplate(this.placedImages.length);
            const imageLayer = {
                id: window.crypto.randomUUID(),
                assetId: asset.id,
                name: asset.name,
                src: asset.src,
                x: positions.x,
                y: positions.y,
                size: positions.size,
                rotation: positions.rotation,
                flipped: false,
                layer: positions.layer,
                style: '',
                classNames: '',
            };

            this.placedImages.push(imageLayer);
            this.selectedImageId = imageLayer.id;
            this.loadSelectedImageControls();
            this.refreshImageStyles();
            this.statusMessage = `${imageLayer.name} added to the canvas.`;
        },

        selectImage(event) {
            this.selectedImageId = event.currentTarget.dataset.layerId;
            this.loadSelectedImageControls();
            this.copyDragState = null;
            this.logoDragState = null;
            this.refreshImageStyles();
        },

        deselectImage() {
            this.selectedImageId = null;
            this.refreshImageStyles();
        },

        startImageDrag(event) {
            event.preventDefault();
            this.selectedImageId = event.currentTarget.dataset.layerId;
            this.loadSelectedImageControls();
            this.copyDragState = null;
            this.logoDragState = null;

            const canvas = this.$root.querySelector('[data-image-studio-canvas]');
            const imageLayer = this.selectedImage;

            if (!canvas || !imageLayer) {
                return;
            }

            this.dragState = {
                pointerId: event.pointerId,
                startClientX: event.clientX,
                startClientY: event.clientY,
                startX: imageLayer.x,
                startY: imageLayer.y,
                canvasRect: canvas.getBoundingClientRect(),
            };

            this.refreshImageStyles();
        },

        startImageResize(event) {
            const imageElement = event.currentTarget.closest('[data-layer-id]');
            const canvas = this.$root.querySelector('[data-image-studio-canvas]');

            if (!imageElement || !canvas) {
                return;
            }

            this.selectedImageId = imageElement.dataset.layerId;
            this.loadSelectedImageControls();

            const imageLayer = this.selectedImage;

            if (!imageLayer) {
                return;
            }

            const canvasRect = canvas.getBoundingClientRect();
            const centerClientX = canvasRect.left + (imageLayer.x / 100) * canvasRect.width;
            const centerClientY = canvasRect.top + (imageLayer.y / 100) * canvasRect.height;
            const startDistance = Math.hypot(event.clientX - centerClientX, event.clientY - centerClientY);

            if (startDistance < 1) {
                return;
            }

            event.currentTarget.setPointerCapture?.(event.pointerId);
            this.dragState = null;
            this.copyDragState = null;
            this.logoDragState = null;
            this.resizeState = {
                pointerId: event.pointerId,
                startSize: imageLayer.size,
                centerClientX,
                centerClientY,
                startDistance,
            };
            this.refreshImageStyles();
        },

        startCopyDrag(event) {
            const canvas = this.$root.querySelector('[data-image-studio-canvas]');
            const copy = event.currentTarget;

            if (!canvas) {
                return;
            }

            const canvasRect = canvas.getBoundingClientRect();
            const copyRect = copy.getBoundingClientRect();
            const startX = ((copyRect.left - canvasRect.left) / canvasRect.width) * 100;
            const startY = ((copyRect.top - canvasRect.top) / canvasRect.height) * 100;
            const width = (copyRect.width / canvasRect.width) * 100;
            const height = (copyRect.height / canvasRect.height) * 100;

            copy.setPointerCapture?.(event.pointerId);
            this.selectedImageId = null;
            this.dragState = null;
            this.resizeState = null;
            this.logoDragState = null;
            this.copyX = startX;
            this.copyY = startY;
            this.copyDragState = {
                pointerId: event.pointerId,
                startClientX: event.clientX,
                startClientY: event.clientY,
                startX,
                startY,
                minX: -10,
                maxX: Math.max(-10, 110 - width),
                minY: -10,
                maxY: Math.max(-10, 110 - height),
                canvasRect,
            };
            this.refreshImageStyles();
        },

        startLogoDrag(event) {
            const canvas = this.$root.querySelector('[data-image-studio-canvas]');
            const logo = event.currentTarget;

            if (!canvas) {
                return;
            }

            const canvasRect = canvas.getBoundingClientRect();
            const logoRect = logo.getBoundingClientRect();
            const startX = ((logoRect.left - canvasRect.left) / canvasRect.width) * 100;
            const startY = ((logoRect.top - canvasRect.top) / canvasRect.height) * 100;
            const width = (logoRect.width / canvasRect.width) * 100;
            const height = (logoRect.height / canvasRect.height) * 100;

            logo.setPointerCapture?.(event.pointerId);
            this.selectedImageId = null;
            this.dragState = null;
            this.resizeState = null;
            this.copyDragState = null;
            this.logoX = startX;
            this.logoY = startY;
            this.logoDragState = {
                pointerId: event.pointerId,
                startClientX: event.clientX,
                startClientY: event.clientY,
                startX,
                startY,
                minX: -10,
                maxX: Math.max(-10, 110 - width),
                minY: -10,
                maxY: Math.max(-10, 110 - height),
                canvasRect,
            };
            this.refreshImageStyles();
        },

        movePointer(event) {
            if (this.logoDragState && event.pointerId === this.logoDragState.pointerId) {
                const deltaX = ((event.clientX - this.logoDragState.startClientX) / this.logoDragState.canvasRect.width) * 100;
                const deltaY = ((event.clientY - this.logoDragState.startClientY) / this.logoDragState.canvasRect.height) * 100;

                this.logoX = Math.min(this.logoDragState.maxX, Math.max(this.logoDragState.minX, this.logoDragState.startX + deltaX));
                this.logoY = Math.min(this.logoDragState.maxY, Math.max(this.logoDragState.minY, this.logoDragState.startY + deltaY));

                return;
            }

            if (this.copyDragState && event.pointerId === this.copyDragState.pointerId) {
                const deltaX = ((event.clientX - this.copyDragState.startClientX) / this.copyDragState.canvasRect.width) * 100;
                const deltaY = ((event.clientY - this.copyDragState.startClientY) / this.copyDragState.canvasRect.height) * 100;

                this.copyX = Math.min(this.copyDragState.maxX, Math.max(this.copyDragState.minX, this.copyDragState.startX + deltaX));
                this.copyY = Math.min(this.copyDragState.maxY, Math.max(this.copyDragState.minY, this.copyDragState.startY + deltaY));

                return;
            }

            if (this.resizeState && event.pointerId === this.resizeState.pointerId && this.selectedImage) {
                const distance = Math.hypot(event.clientX - this.resizeState.centerClientX, event.clientY - this.resizeState.centerClientY);
                const size = this.resizeState.startSize * (distance / this.resizeState.startDistance);

                this.selectedImage.size = Math.min(200, Math.max(16, size));
                this.selectedImageSize = this.selectedImage.size;
                this.refreshImageStyle(this.selectedImage);

                return;
            }

            if (!this.dragState || event.pointerId !== this.dragState.pointerId || !this.selectedImage) {
                return;
            }

            const deltaX = ((event.clientX - this.dragState.startClientX) / this.dragState.canvasRect.width) * 100;
            const deltaY = ((event.clientY - this.dragState.startClientY) / this.dragState.canvasRect.height) * 100;

            this.selectedImage.x = Math.min(110, Math.max(-10, this.dragState.startX + deltaX));
            this.selectedImage.y = Math.min(110, Math.max(-10, this.dragState.startY + deltaY));
            this.refreshImageStyle(this.selectedImage);
        },

        stopPointer(event) {
            if (this.logoDragState && event.pointerId === this.logoDragState.pointerId) {
                this.statusMessage = 'Logo moved.';
                this.logoDragState = null;
            }

            if (this.copyDragState && event.pointerId === this.copyDragState.pointerId) {
                this.statusMessage = 'Headline moved.';
                this.copyDragState = null;
            }

            if (this.resizeState && event.pointerId === this.resizeState.pointerId) {
                this.statusMessage = `${this.selectedImage?.name ?? 'Image'} resized to ${Math.round(this.selectedImageSize)}%.`;
                this.resizeState = null;
            }

            if (this.dragState && event.pointerId === this.dragState.pointerId) {
                this.dragState = null;
            }
        },

        resetCopyPosition() {
            this.copyX = null;
            this.copyY = null;
            this.copyDragState = null;
        },

        resetLogoLayer() {
            this.logoX = null;
            this.logoY = null;
            this.logoScale = 100;
            this.logoDragState = null;
        },

        syncSelectedImage() {
            if (this.selectedImage) {
                this.selectedImage.size = this.selectedImageSize;
                this.selectedImage.rotation = this.selectedImageRotation;
                this.refreshImageStyle(this.selectedImage);
            }
        },

        loadSelectedImageControls() {
            if (!this.selectedImage) {
                return;
            }

            this.selectedImageSize = this.selectedImage.size;
            this.selectedImageRotation = this.selectedImage.rotation;
        },

        refreshImageStyles() {
            this.placedImages.forEach((imageLayer) => this.refreshImageStyle(imageLayer));
        },

        refreshImageStyle(imageLayer) {
            const flip = imageLayer.flipped ? -1 : 1;
            imageLayer.style = `left: ${imageLayer.x}%; top: ${imageLayer.y}%; width: ${imageLayer.size}%; transform: translate(-50%, -50%) rotate(${imageLayer.rotation}deg) scaleX(${flip});`;
            imageLayer.classNames = [imageLayer.id === this.selectedImageId ? 'is-selected' : '', imageLayer.layer === 'above' ? 'is-above-text' : '']
                .filter(Boolean)
                .join(' ');
        },

        setSelectedImageLayer(event) {
            if (!this.selectedImage || !['behind', 'above'].includes(event.currentTarget.dataset.imageLayer)) {
                return;
            }

            this.selectedImage.layer = event.currentTarget.dataset.imageLayer;
            this.refreshImageStyle(this.selectedImage);
            this.statusMessage = `${this.selectedImage.name} moved ${this.selectedImage.layer === 'above' ? 'above' : 'behind'} the headline.`;
        },

        flipSelectedImage() {
            if (!this.selectedImage) {
                return;
            }

            this.selectedImage.flipped = !this.selectedImage.flipped;
            this.refreshImageStyle(this.selectedImage);
        },

        bringSelectedForward() {
            const index = this.placedImages.findIndex((imageLayer) => imageLayer.id === this.selectedImageId);

            if (index < 0 || index === this.placedImages.length - 1) {
                return;
            }

            const [imageLayer] = this.placedImages.splice(index, 1);
            this.placedImages.splice(index + 1, 0, imageLayer);
        },

        sendSelectedBackward() {
            const index = this.placedImages.findIndex((imageLayer) => imageLayer.id === this.selectedImageId);

            if (index <= 0) {
                return;
            }

            const [imageLayer] = this.placedImages.splice(index, 1);
            this.placedImages.splice(index - 1, 0, imageLayer);
        },

        removeSelectedImage() {
            if (!this.selectedImageId) {
                return;
            }

            this.placedImages = this.placedImages.filter((imageLayer) => imageLayer.id !== this.selectedImageId);
            this.selectedImageId = null;
            this.refreshImageStyles();
            this.statusMessage = 'Image removed from the canvas.';
        },

        clearImages() {
            this.placedImages = [];
            this.selectedImageId = null;
            this.statusMessage = 'Canvas images cleared.';
        },

        handleKeyboard(event) {
            const isFormControl = ['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName);

            if (isFormControl || !this.selectedImage) {
                return;
            }

            if (['Backspace', 'Delete'].includes(event.key)) {
                event.preventDefault();
                this.removeSelectedImage();
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
            this.selectedImage.x += movements[event.key][0];
            this.selectedImage.y += movements[event.key][1];
            this.refreshImageStyle(this.selectedImage);
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
                width: `${this.selectedFormat.width}px`,
                minWidth: `${this.selectedFormat.width}px`,
                maxWidth: 'none',
                height: `${this.selectedFormat.height}px`,
                minHeight: `${this.selectedFormat.height}px`,
                maxHeight: 'none',
                aspectRatio: `${this.selectedFormat.width} / ${this.selectedFormat.height}`,
                boxShadow: 'none',
                pointerEvents: 'none',
            });

            exportCanvas.inert = true;
            exportCanvas.setAttribute('aria-hidden', 'true');
            document.body.append(exportCanvas);

            return exportCanvas;
        },

        async exportPng() {
            const canvas = this.$root.querySelector('[data-image-studio-canvas]');
            let exportCanvas = null;

            if (!canvas || canvas.offsetWidth === 0) {
                this.statusMessage = 'The canvas is not ready to export yet.';
                return;
            }

            this.isExporting = true;
            this.statusMessage = 'Rendering full-resolution PNG…';

            try {
                await document.fonts.ready;
                await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));

                exportCanvas = this.createExportCanvas(canvas);
                await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));

                const dataUrl = await domToPng(exportCanvas, {
                    width: this.selectedFormat.width,
                    height: this.selectedFormat.height,
                    scale: this.selectedFormat.exportScale,
                    style: {
                        width: `${this.selectedFormat.width}px`,
                        height: `${this.selectedFormat.height}px`,
                        maxWidth: 'none',
                        maxHeight: 'none',
                    },
                    backgroundColor: this.brand === 'cloud' ? '#0927c9' : '#f72b1c',
                    onCloneEachNode: (clone) => this.sanitizeExportClone(clone),
                    filter: (node) => !(node instanceof Element && node.hasAttribute('data-export-ignore')),
                });

                const link = document.createElement('a');
                link.download = `${this.slugify(this.title)}-thumbnail.png`;
                link.href = dataUrl;
                link.click();
                this.statusMessage = `${this.exportWidth} × ${this.exportHeight} PNG exported.`;
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
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-|-$/g, '')
                    .slice(0, 48) || 'imagen-design'
            );
        },
    }));
});
