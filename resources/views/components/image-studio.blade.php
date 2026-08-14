<section
    class="image-studio"
    x-data="imageStudio"
    data-shared-images-index-url="{{ route('shared-images.index') }}"
    data-shared-images-store-url="{{ route('shared-images.store') }}"
    x-on:keydown.window="handleKeyboard"
    x-on:pointermove.window="movePointer"
    x-on:pointerup.window="stopPointer"
    x-on:pointercancel.window="stopPointer"
>
    <header class="studio-heading">
        <div>
            <p class="studio-kicker">Image studio</p>
            <h1>Create share-ready graphics.</h1>
            <p>Brand-safe templates, flexible image layers, and fast layout variations.</p>
        </div>

        <div class="studio-actions">
            <flux:button size="sm" icon="sparkles" x-on:click="generateVariation">Generate layout</flux:button>
            <flux:button
                size="sm"
                variant="primary"
                color="zinc"
                icon="arrow-down-tray"
                aria-label="Export PNG"
                x-on:click="exportPng"
                x-bind:disabled="isExporting"
            >
                <span x-text="exportButtonLabel"></span>
            </flux:button>
        </div>
    </header>

    <div class="studio-workspace">
        <aside class="studio-panel studio-panel--library" aria-label="Brand and template library">
            <div class="studio-panel__section">
                <div class="studio-panel__heading">
                    <span>Brand</span>
                    <span class="studio-panel__meta">Approved</span>
                </div>

                <div class="brand-options">
                    <button
                        type="button"
                        class="brand-option brand-option--cloud"
                        data-brand="cloud"
                        x-on:click="selectBrand"
                        x-bind:aria-pressed="brand === 'cloud'"
                    >
                        <span class="brand-option__preview">
                            <img src="/img/laravel-cloud-logo.png" alt="" />
                        </span>
                        <span>
                            <strong>Laravel Cloud</strong>
                            <small>Blue grid</small>
                        </span>
                        <flux:icon.check-circle class="brand-option__check" />
                    </button>

                    <button
                        type="button"
                        class="brand-option brand-option--laravel"
                        data-brand="laravel"
                        x-on:click="selectBrand"
                        x-bind:aria-pressed="brand === 'laravel'"
                    >
                        <span class="brand-option__preview">
                            <img src="/img/laravel-logo.png" alt="" />
                        </span>
                        <span>
                            <strong>Laravel</strong>
                            <small>Red grid</small>
                        </span>
                        <flux:icon.check-circle class="brand-option__check" />
                    </button>
                </div>
            </div>

            <div class="studio-panel__section">
                <div class="studio-panel__heading">
                    <span>Format</span>
                </div>

                <div class="format-options">
                    <button type="button" data-format="thumbnail" x-on:click="selectFormat" x-bind:aria-pressed="format === 'thumbnail'">
                        <span class="format-icon format-icon--wide"></span>
                        <span>
                            <strong>Thumbnail</strong>
                            <small>1280 × 720</small>
                        </span>
                    </button>
                    <button type="button" data-format="og" x-on:click="selectFormat" x-bind:aria-pressed="format === 'og'">
                        <span class="format-icon format-icon--og"></span>
                        <span>
                            <strong>Open Graph</strong>
                            <small>1200 × 630</small>
                        </span>
                    </button>
                </div>
            </div>

            <div class="studio-panel__section">
                <div class="studio-panel__heading">
                    <span>Template</span>
                    <span class="studio-panel__meta">Figma</span>
                </div>

                <div class="template-grid" x-show="format === 'thumbnail'">
                    <button
                        type="button"
                        data-template="person-text"
                        x-on:click="selectTemplate"
                        x-bind:aria-pressed="template === 'person-text'"
                    >
                        <span class="template-preview template-preview--person-text">
                            <i></i>
                            <i></i>
                        </span>
                        <span>Person + text</span>
                    </button>
                    <button
                        type="button"
                        data-template="person-code"
                        x-on:click="selectTemplate"
                        x-bind:aria-pressed="template === 'person-code'"
                    >
                        <span class="template-preview template-preview--person-code">
                            <i></i>
                            <i></i>
                        </span>
                        <span>Person + code</span>
                    </button>
                    <button
                        type="button"
                        data-template="custom-visual"
                        x-on:click="selectTemplate"
                        x-bind:aria-pressed="template === 'custom-visual'"
                    >
                        <span class="template-preview template-preview--custom-visual">
                            <i></i>
                            <i></i>
                        </span>
                        <span>Custom visual</span>
                    </button>
                    <button type="button" data-template="text-only" x-on:click="selectTemplate" x-bind:aria-pressed="template === 'text-only'">
                        <span class="template-preview template-preview--text-only">
                            <i></i>
                            <i></i>
                        </span>
                        <span>Text only</span>
                    </button>
                </div>

                <p class="studio-panel__hint" x-show="format === 'og'">Open Graph uses the approved brand-specific composition.</p>

                <div class="template-actions" x-show="format === 'thumbnail'">
                    <flux:button size="xs" variant="ghost" icon="arrow-path" x-on:click="resetTemplate">Reset template</flux:button>
                    <flux:button
                        size="xs"
                        variant="ghost"
                        icon="eye"
                        x-on:click="showSafeAreas = !showSafeAreas"
                        x-bind:aria-pressed="showSafeAreas"
                    >
                        Safe areas
                    </flux:button>
                </div>
            </div>

            <div class="studio-panel__section">
                <div class="studio-panel__heading">
                    <span>Background</span>
                    <span class="studio-panel__meta" x-show="backgroundImage">Custom + grid</span>
                </div>

                <flux:file-upload
                    class="studio-file-upload"
                    data-upload-dropzone="background"
                    accept="image/png,image/jpeg,image/webp"
                    x-on:change="handleBackgroundUpload"
                >
                    <flux:file-upload.dropzone inline icon="photo" heading="Drop approved artwork" text="PNG, JPG, or WebP · 15 MB max" />
                </flux:file-upload>

                <flux:button
                    size="xs"
                    variant="ghost"
                    icon="x-mark"
                    class="justify-self-start text-red-600!"
                    x-show="backgroundImage"
                    x-on:click="removeBackground"
                >
                    Remove custom background
                </flux:button>
            </div>
        </aside>

        <main class="studio-stage" aria-label="Design canvas">
            <div class="studio-stage__toolbar">
                <span x-text="formatDescription"></span>
            </div>

            <div class="studio-stage__surface">
                <div
                    class="artboard"
                    data-image-studio-canvas
                    x-bind:class="artboardClassNames"
                    x-bind:style="artboardStyle"
                    x-on:pointerdown.self="deselectImage"
                >
                    <img class="artboard__custom-background" x-show="backgroundImage" x-bind:src="backgroundImage" alt="" />
                    <div class="artboard__wash"></div>
                    <div class="artboard__flare artboard__flare--one"></div>
                    <div class="artboard__flare artboard__flare--two"></div>

                    <div class="artboard__safe-areas" x-show="showSafeAreas && format === 'thumbnail'" data-export-ignore aria-hidden="true">
                        <span class="artboard__safe-area artboard__safe-area--mobile">Mobile UI</span>
                        <span class="artboard__safe-area artboard__safe-area--time">Time</span>
                    </div>

                    <div
                        class="artboard__brand"
                        data-draggable-logo
                        x-bind:class="{ 'is-dragging': logoDragState }"
                        x-bind:style="logoStyle"
                        x-on:pointerdown.prevent="startLogoDrag"
                    >
                        <img class="artboard__brand-logo" x-bind:src="brandLogo" alt="" draggable="false" />
                    </div>

                    <div
                        class="artboard__copy"
                        data-draggable-copy
                        x-bind:class="{ 'is-dragging': copyDragState }"
                        x-bind:style="copyStyle"
                        x-on:pointerdown.prevent="startCopyDrag"
                    >
                        <h2 x-bind:style="headlineStyle" x-text="title"></h2>
                    </div>

                    <div
                        class="artboard__image-slot"
                        x-show="placedImages.length === 0 && templateSlotLabel && format === 'thumbnail'"
                        data-export-ignore
                    >
                        <flux:icon.photo />
                        <span x-text="templateSlotLabel"></span>
                    </div>

                    <template x-for="imageLayer in placedImages" x-bind:key="imageLayer.id">
                        <button
                            class="artboard-image"
                            type="button"
                            x-bind:class="imageLayer.classNames"
                            x-bind:style="imageLayer.style"
                            x-bind:data-layer-id="imageLayer.id"
                            x-on:pointerdown="startImageDrag"
                            x-on:click="selectImage"
                            x-bind:aria-label="`Move ${imageLayer.name}`"
                        >
                            <img x-bind:src="imageLayer.src" x-bind:alt="imageLayer.name" draggable="false" />
                            <span
                                class="artboard-image__handle artboard-image__handle--top-left"
                                data-resize-handle="top-left"
                                data-export-ignore
                                aria-hidden="true"
                                x-on:pointerdown.stop.prevent="startImageResize"
                                x-on:click.stop
                            ></span>
                            <span
                                class="artboard-image__handle artboard-image__handle--top-right"
                                data-resize-handle="top-right"
                                data-export-ignore
                                aria-hidden="true"
                                x-on:pointerdown.stop.prevent="startImageResize"
                                x-on:click.stop
                            ></span>
                            <span
                                class="artboard-image__handle artboard-image__handle--middle-left"
                                data-resize-handle="middle-left"
                                data-export-ignore
                                aria-hidden="true"
                                x-on:pointerdown.stop.prevent="startImageResize"
                                x-on:click.stop
                            ></span>
                            <span
                                class="artboard-image__handle artboard-image__handle--middle-right"
                                data-resize-handle="middle-right"
                                data-export-ignore
                                aria-hidden="true"
                                x-on:pointerdown.stop.prevent="startImageResize"
                                x-on:click.stop
                            ></span>
                            <span
                                class="artboard-image__handle artboard-image__handle--bottom-left"
                                data-resize-handle="bottom-left"
                                data-export-ignore
                                aria-hidden="true"
                                x-on:pointerdown.stop.prevent="startImageResize"
                                x-on:click.stop
                            ></span>
                            <span
                                class="artboard-image__handle artboard-image__handle--bottom-right"
                                data-resize-handle="bottom-right"
                                data-export-ignore
                                aria-hidden="true"
                                x-on:pointerdown.stop.prevent="startImageResize"
                                x-on:click.stop
                            ></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="studio-stage__footer">
                <span x-text="statusMessage"></span>
                <span>Drag images, the logo, or the headline to move · pull image handles to resize.</span>
            </div>
        </main>

        <aside class="studio-panel studio-panel--inspector" aria-label="Design controls">
            <div class="studio-panel__section">
                <div class="studio-panel__heading">
                    <span>Logo</span>
                    <span class="studio-panel__meta">Movable layer</span>
                </div>

                <p class="studio-panel__hint">Drag the logo directly on the canvas.</p>

                <div class="studio-range">
                    <span>
                        <strong>Scale</strong>
                        <output x-text="`${logoScale}%`"></output>
                    </span>
                    <flux:slider min="50" max="180" step="1" big-step="10" aria-label="Logo scale" x-model.number="logoScale" />
                </div>

                <flux:button
                    size="xs"
                    variant="ghost"
                    icon="arrow-path"
                    class="justify-self-start"
                    x-show="logoX !== null || logoScale !== 100"
                    x-on:click="
                        resetLogoLayer()
                        statusMessage = 'Logo position and scale reset.'
                    "
                >
                    Reset logo
                </flux:button>
            </div>

            <div class="studio-panel__section">
                <div class="studio-panel__heading">
                    <span>Copy</span>
                    <span class="studio-panel__meta">Instrument Sans</span>
                </div>

                <flux:field>
                    <flux:label>Headline</flux:label>
                    <flux:textarea x-model="title" rows="3" maxlength="120" resize="none" />
                </flux:field>

                <label class="studio-range">
                    <span>
                        <strong>Type scale</strong>
                        <output x-text="`${headlineSize}%`"></output>
                    </span>
                    <input type="range" min="72" max="132" step="2" x-model.number="headlineSize" />
                </label>

                <div class="alignment-options" aria-label="Text alignment">
                    <flux:button
                        class="h-8! w-full! rounded-none!"
                        size="xs"
                        variant="ghost"
                        icon="bars-3-bottom-left"
                        data-alignment="left"
                        x-on:click="selectAlignment"
                        x-bind:aria-pressed="alignment === 'left'"
                        aria-label="Align left"
                    />
                    <flux:button
                        class="h-8! w-full! rounded-none!"
                        size="xs"
                        variant="ghost"
                        icon="bars-3"
                        data-alignment="center"
                        x-on:click="selectAlignment"
                        x-bind:aria-pressed="alignment === 'center'"
                        aria-label="Align center"
                    />
                    <flux:button
                        class="h-8! w-full! rounded-none!"
                        size="xs"
                        variant="ghost"
                        icon="bars-3-bottom-right"
                        data-alignment="right"
                        x-on:click="selectAlignment"
                        x-bind:aria-pressed="alignment === 'right'"
                        aria-label="Align right"
                    />
                </div>

                <flux:button
                    size="xs"
                    variant="ghost"
                    icon="arrow-path"
                    class="justify-self-start"
                    x-show="copyX !== null"
                    x-on:click="
                        resetCopyPosition()
                        statusMessage = 'Headline position reset.'
                    "
                >
                    Reset headline position
                </flux:button>
            </div>

            <div class="studio-panel__section">
                <div class="studio-panel__heading">
                    <span>Images</span>
                    <span class="studio-panel__meta" x-text="`${savedImageCount} shared`"></span>
                </div>

                <flux:file-upload
                    class="studio-file-upload"
                    data-upload-dropzone="images"
                    accept="image/png,image/jpeg,image/webp"
                    multiple
                    x-on:change="handleImageUpload"
                >
                    <flux:file-upload.dropzone inline icon="photo" heading="Drop image layers" text="PNG, JPG, or WebP · 15 MB max" />
                </flux:file-upload>

                <div class="image-empty" x-show="imageLibrary.length === 0">
                    <div class="image-empty__preview">
                        <i></i>
                        <i></i>
                        <i></i>
                    </div>
                    <p>Add people, product shots, screenshots, or any other image. Save once to share it with everyone.</p>
                </div>

                <div class="image-library" x-show="imageLibrary.length > 0">
                    <template x-for="imageLayer in imageLibrary" x-bind:key="imageLayer.id">
                        <div class="image-library__item">
                            <button
                                class="image-library__add"
                                type="button"
                                x-bind:data-image-id="imageLayer.id"
                                x-on:click="addImage"
                                x-bind:aria-label="`Add ${imageLayer.name} to canvas`"
                            >
                                <img x-bind:src="imageLayer.src" x-bind:alt="imageLayer.name" />
                                <span x-text="imageLayer.name"></span>
                                <i><flux:icon.plus /></i>
                            </button>
                            <button
                                class="image-library__save"
                                type="button"
                                x-show="!imageLayer.isSaved"
                                x-bind:data-image-id="imageLayer.id"
                                x-bind:disabled="imageLayer.isSaving"
                                x-on:click="saveImage"
                                x-text="imageLayer.isSaving ? 'Saving…' : 'Save for everyone'"
                            ></button>
                            <span class="image-library__saved" x-show="imageLayer.isSaved">
                                <flux:icon.check-circle />
                                Shared
                            </span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="studio-panel__section" x-show="selectedImage">
                <div class="studio-panel__heading">
                    <span>Selected image</span>
                    <span class="studio-panel__meta" x-text="selectedImage?.name"></span>
                </div>

                <div class="studio-range">
                    <span>
                        <strong>Scale</strong>
                        <output x-text="`${Math.round(selectedImageSize)}%`"></output>
                    </span>
                    <flux:slider
                        min="16"
                        max="200"
                        step="1"
                        big-step="10"
                        aria-label="Image scale"
                        x-model.number="selectedImageSize"
                        x-on:input="syncSelectedImage"
                    />
                </div>

                <div class="studio-range">
                    <span>
                        <strong>Rotation</strong>
                        <output x-text="`${selectedImageRotation}°`"></output>
                    </span>
                    <flux:slider
                        min="-15"
                        max="15"
                        step="1"
                        big-step="5"
                        aria-label="Image rotation"
                        x-model.number="selectedImageRotation"
                        x-on:input="syncSelectedImage"
                    />
                </div>

                <div class="layer-position">
                    <span>Text overlap</span>
                    <flux:button.group>
                        <flux:button
                            size="xs"
                            icon="arrow-down"
                            data-image-layer="behind"
                            x-on:click="setSelectedImageLayer"
                            x-bind:aria-pressed="selectedImage?.layer !== 'above'"
                        >
                            Behind text
                        </flux:button>
                        <flux:button
                            size="xs"
                            icon="arrow-up"
                            data-image-layer="above"
                            x-on:click="setSelectedImageLayer"
                            x-bind:aria-pressed="selectedImage?.layer === 'above'"
                        >
                            Above text
                        </flux:button>
                    </flux:button.group>
                </div>

                <div class="layer-actions">
                    <flux:button size="xs" icon="arrows-right-left" x-on:click="flipSelectedImage">Flip</flux:button>
                    <flux:button size="xs" icon="arrow-down" x-on:click="sendSelectedBackward">Back</flux:button>
                    <flux:button size="xs" icon="arrow-up" x-on:click="bringSelectedForward">Front</flux:button>
                    <flux:button size="xs" variant="danger" icon="trash" x-on:click="removeSelectedImage">Remove</flux:button>
                </div>
            </div>

            <div class="studio-panel__section" x-show="placedImages.length > 0">
                <div class="studio-panel__heading">
                    <span>Layers</span>
                    <flux:button size="xs" variant="ghost" class="text-red-600!" x-on:click="clearImages">Clear</flux:button>
                </div>

                <div class="layer-list">
                    <template x-for="imageLayer in reversedPlacedImages" x-bind:key="imageLayer.id">
                        <button
                            type="button"
                            x-bind:data-layer-id="imageLayer.id"
                            x-on:click="selectImage"
                            x-bind:aria-pressed="selectedImageId === imageLayer.id"
                        >
                            <img x-bind:src="imageLayer.src" alt="" />
                            <span x-text="imageLayer.name"></span>
                            <flux:icon.bars-2 />
                        </button>
                    </template>
                </div>
            </div>
        </aside>
    </div>
</section>
