import { domToPng } from 'modern-screenshot';

const formats = {
    thumbnail: {
        label: 'Thumbnail',
        width: 1280,
        height: 720,
    },
    og: {
        label: 'Open Graph',
        width: 1200,
        height: 630,
    },
};

const templates = ['editorial', 'split', 'stacked', 'lower-third'];

document.addEventListener('alpine:init', () => {
    window.Alpine.data('imageStudio', () => ({
        brand: 'cloud',
        format: 'thumbnail',
        template: 'editorial',
        alignment: 'left',
        title: 'Ship Laravel without\nthe server stress.',
        headlineSize: 100,
        backgroundImage: null,
        peopleLibrary: [],
        placedPeople: [],
        selectedPersonId: null,
        selectedPersonSize: 46,
        selectedPersonRotation: 0,
        copyX: null,
        copyY: null,
        copyDragState: null,
        dragState: null,
        resizeState: null,
        isExporting: false,
        variationIndex: 0,
        statusMessage: 'Ready to design.',

        get selectedFormat() {
            return formats[this.format];
        },

        get formatDescription() {
            return `${this.selectedFormat.label} · ${this.selectedFormat.width} × ${this.selectedFormat.height}`;
        },

        get exportButtonLabel() {
            return this.isExporting ? 'Rendering…' : 'Export PNG';
        },

        get brandName() {
            return this.brand === 'cloud' ? 'Laravel Cloud' : 'Laravel';
        },

        get brandLogo() {
            return this.brand === 'cloud' ? '/img/laravel-cloud.svg' : '/img/laravel.svg';
        },

        get artboardClassNames() {
            return [
                `artboard--${this.brand}`,
                `artboard--${this.template}`,
                `artboard--${this.format}`,
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

        get selectedPerson() {
            return this.placedPeople.find((person) => person.id === this.selectedPersonId) ?? null;
        },

        get reversedPlacedPeople() {
            return [...this.placedPeople].reverse();
        },

        selectBrand(event) {
            this.brand = event.currentTarget.dataset.brand;
            this.statusMessage = `${this.brandName} brand system applied.`;
        },

        selectFormat(event) {
            this.format = event.currentTarget.dataset.format;
            this.statusMessage = `${this.selectedFormat.label} canvas ready.`;
        },

        selectTemplate(event) {
            this.template = event.currentTarget.dataset.template;
            this.resetCopyPosition();
            this.statusMessage = `${this.templateLabel(this.template)} layout applied.`;
        },

        selectAlignment(event) {
            this.alignment = event.currentTarget.dataset.alignment;
        },

        templateLabel(template) {
            return template
                .split('-')
                .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        },

        generateVariation() {
            this.variationIndex += 1;
            this.template = templates[this.variationIndex % templates.length];
            this.headlineSize = [100, 92, 112, 86][this.variationIndex % 4];
            this.alignment = this.template === 'stacked' ? 'center' : this.template === 'split' ? 'right' : 'left';
            this.resetCopyPosition();

            this.placedPeople.forEach((person, index) => {
                const positions = this.personPositionsForTemplate(index);
                person.x = positions.x;
                person.y = positions.y;
                person.rotation = positions.rotation;
                this.refreshPersonStyle(person);
            });

            this.statusMessage = `${this.templateLabel(this.template)} variation generated.`;
        },

        personPositionsForTemplate(index) {
            const offset = Math.min(index * 12, 30);

            if (this.template === 'split') {
                return { x: 26 + offset, y: 58, rotation: index % 2 === 0 ? -2 : 2 };
            }

            if (this.template === 'stacked') {
                return { x: index % 2 === 0 ? 18 + offset : 82 - offset, y: 64, rotation: index % 2 === 0 ? -3 : 3 };
            }

            if (this.template === 'lower-third') {
                return { x: 67 + offset / 2, y: 52, rotation: index % 2 === 0 ? 1 : -1 };
            }

            return { x: 74 - offset, y: 58, rotation: index % 2 === 0 ? 2 : -2 };
        },

        async handlePeopleUpload(event) {
            const upload = event.currentTarget;
            const files = this.filesFromUpload(upload);
            const validFiles = files.filter((file) => file.type === 'image/png' && file.size <= 10 * 1024 * 1024);

            if (validFiles.length === 0) {
                this.statusMessage = 'Choose transparent PNG files under 10 MB.';
                this.resetFileInput(upload);
                return;
            }

            const assets = await Promise.all(
                validFiles.map(async (file) => ({
                    id: window.crypto.randomUUID(),
                    name: file.name.replace(/\.png$/i, ''),
                    src: await this.readFile(file),
                }))
            );

            this.peopleLibrary.push(...assets);
            this.statusMessage = `${assets.length} approved ${assets.length === 1 ? 'person' : 'people'} added to the session tray.`;
            this.resetFileInput(upload);
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

        addPerson(event) {
            const asset = this.peopleLibrary.find((person) => person.id === event.currentTarget.dataset.personId);

            if (!asset) {
                return;
            }

            const positions = this.personPositionsForTemplate(this.placedPeople.length);
            const person = {
                id: window.crypto.randomUUID(),
                assetId: asset.id,
                name: asset.name,
                src: asset.src,
                x: positions.x,
                y: positions.y,
                size: 46,
                rotation: positions.rotation,
                flipped: false,
                layer: 'behind',
                style: '',
                classNames: '',
            };

            this.placedPeople.push(person);
            this.selectedPersonId = person.id;
            this.loadSelectedPersonControls();
            this.refreshPeopleStyles();
            this.statusMessage = `${person.name} added to the canvas.`;
        },

        selectPerson(event) {
            this.selectedPersonId = event.currentTarget.dataset.layerId;
            this.loadSelectedPersonControls();
            this.refreshPeopleStyles();
        },

        deselectPerson() {
            this.selectedPersonId = null;
            this.refreshPeopleStyles();
        },

        startPersonDrag(event) {
            event.preventDefault();
            this.selectedPersonId = event.currentTarget.dataset.layerId;
            this.loadSelectedPersonControls();

            const canvas = this.$root.querySelector('[data-image-studio-canvas]');
            const person = this.selectedPerson;

            if (!canvas || !person) {
                return;
            }

            this.dragState = {
                pointerId: event.pointerId,
                startClientX: event.clientX,
                startClientY: event.clientY,
                startX: person.x,
                startY: person.y,
                canvasRect: canvas.getBoundingClientRect(),
            };

            this.refreshPeopleStyles();
        },

        startPersonResize(event) {
            const personElement = event.currentTarget.closest('[data-layer-id]');
            const canvas = this.$root.querySelector('[data-image-studio-canvas]');

            if (!personElement || !canvas) {
                return;
            }

            this.selectedPersonId = personElement.dataset.layerId;
            this.loadSelectedPersonControls();

            const person = this.selectedPerson;

            if (!person) {
                return;
            }

            const canvasRect = canvas.getBoundingClientRect();
            const centerClientX = canvasRect.left + (person.x / 100) * canvasRect.width;
            const centerClientY = canvasRect.top + (person.y / 100) * canvasRect.height;
            const startDistance = Math.hypot(event.clientX - centerClientX, event.clientY - centerClientY);

            if (startDistance < 1) {
                return;
            }

            event.currentTarget.setPointerCapture?.(event.pointerId);
            this.dragState = null;
            this.resizeState = {
                pointerId: event.pointerId,
                startSize: person.size,
                centerClientX,
                centerClientY,
                startDistance,
            };
            this.refreshPeopleStyles();
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
            this.selectedPersonId = null;
            this.dragState = null;
            this.resizeState = null;
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
            this.refreshPeopleStyles();
        },

        movePointer(event) {
            if (this.copyDragState && event.pointerId === this.copyDragState.pointerId) {
                const deltaX = ((event.clientX - this.copyDragState.startClientX) / this.copyDragState.canvasRect.width) * 100;
                const deltaY = ((event.clientY - this.copyDragState.startClientY) / this.copyDragState.canvasRect.height) * 100;

                this.copyX = Math.min(this.copyDragState.maxX, Math.max(this.copyDragState.minX, this.copyDragState.startX + deltaX));
                this.copyY = Math.min(this.copyDragState.maxY, Math.max(this.copyDragState.minY, this.copyDragState.startY + deltaY));

                return;
            }

            if (this.resizeState && event.pointerId === this.resizeState.pointerId && this.selectedPerson) {
                const distance = Math.hypot(event.clientX - this.resizeState.centerClientX, event.clientY - this.resizeState.centerClientY);
                const size = this.resizeState.startSize * (distance / this.resizeState.startDistance);

                this.selectedPerson.size = Math.min(200, Math.max(16, size));
                this.selectedPersonSize = this.selectedPerson.size;
                this.refreshPersonStyle(this.selectedPerson);

                return;
            }

            if (!this.dragState || event.pointerId !== this.dragState.pointerId || !this.selectedPerson) {
                return;
            }

            const deltaX = ((event.clientX - this.dragState.startClientX) / this.dragState.canvasRect.width) * 100;
            const deltaY = ((event.clientY - this.dragState.startClientY) / this.dragState.canvasRect.height) * 100;

            this.selectedPerson.x = Math.min(110, Math.max(-10, this.dragState.startX + deltaX));
            this.selectedPerson.y = Math.min(110, Math.max(-10, this.dragState.startY + deltaY));
            this.refreshPersonStyle(this.selectedPerson);
        },

        stopPointer(event) {
            if (this.copyDragState && event.pointerId === this.copyDragState.pointerId) {
                this.statusMessage = 'Headline moved.';
                this.copyDragState = null;
            }

            if (this.resizeState && event.pointerId === this.resizeState.pointerId) {
                this.statusMessage = `${this.selectedPerson?.name ?? 'Image'} resized to ${Math.round(this.selectedPersonSize)}%.`;
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

        syncSelectedPerson() {
            if (this.selectedPerson) {
                this.selectedPerson.size = this.selectedPersonSize;
                this.selectedPerson.rotation = this.selectedPersonRotation;
                this.refreshPersonStyle(this.selectedPerson);
            }
        },

        loadSelectedPersonControls() {
            if (!this.selectedPerson) {
                return;
            }

            this.selectedPersonSize = this.selectedPerson.size;
            this.selectedPersonRotation = this.selectedPerson.rotation;
        },

        refreshPeopleStyles() {
            this.placedPeople.forEach((person) => this.refreshPersonStyle(person));
        },

        refreshPersonStyle(person) {
            const flip = person.flipped ? -1 : 1;
            person.style = `left: ${person.x}%; top: ${person.y}%; width: ${person.size}%; transform: translate(-50%, -50%) rotate(${person.rotation}deg) scaleX(${flip});`;
            person.classNames = [person.id === this.selectedPersonId ? 'is-selected' : '', person.layer === 'above' ? 'is-above-text' : '']
                .filter(Boolean)
                .join(' ');
        },

        setSelectedPersonLayer(event) {
            if (!this.selectedPerson || !['behind', 'above'].includes(event.currentTarget.dataset.personLayer)) {
                return;
            }

            this.selectedPerson.layer = event.currentTarget.dataset.personLayer;
            this.refreshPersonStyle(this.selectedPerson);
            this.statusMessage = `${this.selectedPerson.name} moved ${this.selectedPerson.layer === 'above' ? 'above' : 'behind'} the headline.`;
        },

        flipSelectedPerson() {
            if (!this.selectedPerson) {
                return;
            }

            this.selectedPerson.flipped = !this.selectedPerson.flipped;
            this.refreshPersonStyle(this.selectedPerson);
        },

        bringSelectedForward() {
            const index = this.placedPeople.findIndex((person) => person.id === this.selectedPersonId);

            if (index < 0 || index === this.placedPeople.length - 1) {
                return;
            }

            const [person] = this.placedPeople.splice(index, 1);
            this.placedPeople.splice(index + 1, 0, person);
        },

        sendSelectedBackward() {
            const index = this.placedPeople.findIndex((person) => person.id === this.selectedPersonId);

            if (index <= 0) {
                return;
            }

            const [person] = this.placedPeople.splice(index, 1);
            this.placedPeople.splice(index - 1, 0, person);
        },

        removeSelectedPerson() {
            if (!this.selectedPersonId) {
                return;
            }

            this.placedPeople = this.placedPeople.filter((person) => person.id !== this.selectedPersonId);
            this.selectedPersonId = null;
            this.refreshPeopleStyles();
            this.statusMessage = 'Person removed from the canvas.';
        },

        clearPeople() {
            this.placedPeople = [];
            this.selectedPersonId = null;
            this.statusMessage = 'Canvas people cleared.';
        },

        handleKeyboard(event) {
            const isFormControl = ['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName);

            if (isFormControl || !this.selectedPerson) {
                return;
            }

            if (['Backspace', 'Delete'].includes(event.key)) {
                event.preventDefault();
                this.removeSelectedPerson();
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
            this.selectedPerson.x += movements[event.key][0];
            this.selectedPerson.y += movements[event.key][1];
            this.refreshPersonStyle(this.selectedPerson);
        },

        async exportPng() {
            const canvas = this.$root.querySelector('[data-image-studio-canvas]');

            if (!canvas || canvas.offsetWidth === 0) {
                this.statusMessage = 'The canvas is not ready to export yet.';
                return;
            }

            this.isExporting = true;
            this.statusMessage = 'Rendering full-resolution PNG…';

            try {
                await document.fonts.ready;
                await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));

                const dataUrl = await domToPng(canvas, {
                    scale: this.selectedFormat.width / canvas.offsetWidth,
                    backgroundColor: this.brand === 'cloud' ? '#0927c9' : '#f72b1c',
                    filter: (node) => !(node instanceof Element && node.hasAttribute('data-export-ignore')),
                });

                const link = document.createElement('a');
                link.download = `${this.slugify(this.title)}-${this.format}.png`;
                link.href = dataUrl;
                link.click();
                this.statusMessage = `${this.selectedFormat.width} × ${this.selectedFormat.height} PNG exported.`;
            } catch (error) {
                console.error(error);
                this.statusMessage = 'Export failed. Check the browser console for details.';
            } finally {
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
