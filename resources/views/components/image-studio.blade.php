<section
    class="image-studio"
    x-data="imageStudio"
    x-on:keydown.window="handleKeyboard"
    x-on:pointermove.window="movePointer"
    x-on:pointerup.window="stopPointer"
    x-on:pointercancel.window="stopPointer"
>
    <header class="studio-heading">
        <div>
            <p class="studio-kicker">Image studio</p>
            <h1>Create share-ready graphics.</h1>
            <p>Brand-safe backgrounds, flexible people cutouts, and fast layout variations.</p>
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
                            <img src="/img/laravel-cloud.svg" alt="" />
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
                            <img src="/img/laravel.svg" alt="" />
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
                    <span>Layout</span>
                    <span class="studio-panel__meta">4 systems</span>
                </div>

                <div class="template-grid">
                    <button type="button" data-template="editorial" x-on:click="selectTemplate" x-bind:aria-pressed="template === 'editorial'">
                        <span class="template-preview template-preview--editorial">
                            <i></i>
                            <i></i>
                        </span>
                        <span>Editorial</span>
                    </button>
                    <button type="button" data-template="split" x-on:click="selectTemplate" x-bind:aria-pressed="template === 'split'">
                        <span class="template-preview template-preview--split">
                            <i></i>
                            <i></i>
                        </span>
                        <span>Split</span>
                    </button>
                    <button type="button" data-template="stacked" x-on:click="selectTemplate" x-bind:aria-pressed="template === 'stacked'">
                        <span class="template-preview template-preview--stacked">
                            <i></i>
                            <i></i>
                        </span>
                        <span>Stacked</span>
                    </button>
                    <button
                        type="button"
                        data-template="lower-third"
                        x-on:click="selectTemplate"
                        x-bind:aria-pressed="template === 'lower-third'"
                    >
                        <span class="template-preview template-preview--lower">
                            <i></i>
                            <i></i>
                        </span>
                        <span>Lower third</span>
                    </button>
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
                <div>
                    <span class="status-dot"></span>
                    Live canvas
                </div>
                <span x-text="formatDescription"></span>
            </div>

            <div class="studio-stage__surface">
                <div
                    class="artboard"
                    data-image-studio-canvas
                    x-bind:class="artboardClassNames"
                    x-bind:style="artboardStyle"
                    x-on:pointerdown.self="deselectPerson"
                >
                    <img class="artboard__custom-background" x-show="backgroundImage" x-bind:src="backgroundImage" alt="" />
                    <div class="artboard__wash"></div>
                    <div class="artboard__flare artboard__flare--one"></div>
                    <div class="artboard__flare artboard__flare--two"></div>

                    <div class="artboard__brand">
                        <img class="artboard__brand-logo" x-bind:src="brandLogo" alt="" />
                    </div>

                    <div
                        class="artboard__copy"
                        data-draggable-copy
                        x-bind:class="{ 'is-dragging': copyDragState }"
                        x-bind:style="copyStyle"
                        x-on:pointerdown.prevent="startCopyDrag"
                    >
                        <h2 x-bind:style="headlineStyle" x-text="title"></h2>
                        <span class="artboard__rule"></span>
                    </div>

                    <template x-for="person in placedPeople" x-bind:key="person.id">
                        <button
                            class="artboard-person"
                            type="button"
                            x-bind:class="person.classNames"
                            x-bind:style="person.style"
                            x-bind:data-layer-id="person.id"
                            x-on:pointerdown="startPersonDrag"
                            x-on:click="selectPerson"
                            x-bind:aria-label="`Move ${person.name}`"
                        >
                            <img x-bind:src="person.src" x-bind:alt="person.name" draggable="false" />
                            <span
                                class="artboard-person__handle artboard-person__handle--top-left"
                                data-resize-handle="top-left"
                                data-export-ignore
                                aria-hidden="true"
                                x-on:pointerdown.stop.prevent="startPersonResize"
                                x-on:click.stop
                            ></span>
                            <span
                                class="artboard-person__handle artboard-person__handle--top-right"
                                data-resize-handle="top-right"
                                data-export-ignore
                                aria-hidden="true"
                                x-on:pointerdown.stop.prevent="startPersonResize"
                                x-on:click.stop
                            ></span>
                            <span
                                class="artboard-person__handle artboard-person__handle--middle-left"
                                data-resize-handle="middle-left"
                                data-export-ignore
                                aria-hidden="true"
                                x-on:pointerdown.stop.prevent="startPersonResize"
                                x-on:click.stop
                            ></span>
                            <span
                                class="artboard-person__handle artboard-person__handle--middle-right"
                                data-resize-handle="middle-right"
                                data-export-ignore
                                aria-hidden="true"
                                x-on:pointerdown.stop.prevent="startPersonResize"
                                x-on:click.stop
                            ></span>
                            <span
                                class="artboard-person__handle artboard-person__handle--bottom-left"
                                data-resize-handle="bottom-left"
                                data-export-ignore
                                aria-hidden="true"
                                x-on:pointerdown.stop.prevent="startPersonResize"
                                x-on:click.stop
                            ></span>
                            <span
                                class="artboard-person__handle artboard-person__handle--bottom-right"
                                data-resize-handle="bottom-right"
                                data-export-ignore
                                aria-hidden="true"
                                x-on:pointerdown.stop.prevent="startPersonResize"
                                x-on:click.stop
                            ></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="studio-stage__footer">
                <span x-text="statusMessage"></span>
                <span>Drag people or the headline to move · pull any visible handle to resize.</span>
            </div>
        </main>

        <aside class="studio-panel studio-panel--inspector" aria-label="Design controls">
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
                        size="xs"
                        variant="ghost"
                        icon="bars-3-bottom-left"
                        data-alignment="left"
                        x-on:click="selectAlignment"
                        x-bind:aria-pressed="alignment === 'left'"
                        aria-label="Align left"
                    />
                    <flux:button
                        size="xs"
                        variant="ghost"
                        icon="bars-3"
                        data-alignment="center"
                        x-on:click="selectAlignment"
                        x-bind:aria-pressed="alignment === 'center'"
                        aria-label="Align center"
                    />
                    <flux:button
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
                    <span>People</span>
                    <span class="studio-panel__meta" x-text="`${peopleLibrary.length} approved`"></span>
                </div>

                <flux:file-upload
                    class="studio-file-upload"
                    data-upload-dropzone="people"
                    accept="image/png"
                    multiple
                    x-on:change="handlePeopleUpload"
                >
                    <flux:file-upload.dropzone inline icon="user-plus" heading="Drop people PNGs" text="Transparent cutouts · 10 MB max" />
                </flux:file-upload>

                <div class="people-empty" x-show="peopleLibrary.length === 0">
                    <div class="people-empty__faces">
                        <i></i>
                        <i></i>
                        <i></i>
                    </div>
                    <p>Your approved faces will stay in this session tray.</p>
                </div>

                <div class="people-library" x-show="peopleLibrary.length > 0">
                    <template x-for="person in peopleLibrary" x-bind:key="person.id">
                        <button
                            type="button"
                            x-bind:data-person-id="person.id"
                            x-on:click="addPerson"
                            x-bind:aria-label="`Add ${person.name} to canvas`"
                        >
                            <img x-bind:src="person.src" x-bind:alt="person.name" />
                            <span x-text="person.name"></span>
                            <i><flux:icon.plus /></i>
                        </button>
                    </template>
                </div>
            </div>

            <div class="studio-panel__section" x-show="selectedPerson">
                <div class="studio-panel__heading">
                    <span>Selected person</span>
                    <span class="studio-panel__meta" x-text="selectedPerson?.name"></span>
                </div>

                <div class="studio-range">
                    <span>
                        <strong>Scale</strong>
                        <output x-text="`${Math.round(selectedPersonSize)}%`"></output>
                    </span>
                    <flux:slider
                        min="16"
                        max="200"
                        step="1"
                        big-step="10"
                        aria-label="Image scale"
                        x-model.number="selectedPersonSize"
                        x-on:input="syncSelectedPerson"
                    />
                </div>

                <div class="studio-range">
                    <span>
                        <strong>Rotation</strong>
                        <output x-text="`${selectedPersonRotation}°`"></output>
                    </span>
                    <flux:slider
                        min="-15"
                        max="15"
                        step="1"
                        big-step="5"
                        aria-label="Image rotation"
                        x-model.number="selectedPersonRotation"
                        x-on:input="syncSelectedPerson"
                    />
                </div>

                <div class="layer-position">
                    <span>Text overlap</span>
                    <flux:button.group>
                        <flux:button
                            size="xs"
                            icon="arrow-down"
                            data-person-layer="behind"
                            x-on:click="setSelectedPersonLayer"
                            x-bind:aria-pressed="selectedPerson?.layer !== 'above'"
                        >
                            Behind text
                        </flux:button>
                        <flux:button
                            size="xs"
                            icon="arrow-up"
                            data-person-layer="above"
                            x-on:click="setSelectedPersonLayer"
                            x-bind:aria-pressed="selectedPerson?.layer === 'above'"
                        >
                            Above text
                        </flux:button>
                    </flux:button.group>
                </div>

                <div class="layer-actions">
                    <flux:button size="xs" icon="arrows-right-left" x-on:click="flipSelectedPerson">Flip</flux:button>
                    <flux:button size="xs" icon="arrow-down" x-on:click="sendSelectedBackward">Back</flux:button>
                    <flux:button size="xs" icon="arrow-up" x-on:click="bringSelectedForward">Front</flux:button>
                    <flux:button size="xs" variant="danger" icon="trash" x-on:click="removeSelectedPerson">Remove</flux:button>
                </div>
            </div>

            <div class="studio-panel__section" x-show="placedPeople.length > 0">
                <div class="studio-panel__heading">
                    <span>Layers</span>
                    <flux:button size="xs" variant="ghost" class="text-red-600!" x-on:click="clearPeople">Clear</flux:button>
                </div>

                <div class="layer-list">
                    <template x-for="person in reversedPlacedPeople" x-bind:key="person.id">
                        <button
                            type="button"
                            x-bind:data-layer-id="person.id"
                            x-on:click="selectPerson"
                            x-bind:aria-pressed="selectedPersonId === person.id"
                        >
                            <img x-bind:src="person.src" alt="" />
                            <span x-text="person.name"></span>
                            <flux:icon.bars-2 />
                        </button>
                    </template>
                </div>
            </div>
        </aside>
    </div>
</section>
