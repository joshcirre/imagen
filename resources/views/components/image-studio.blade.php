<section
    class="image-studio"
    x-data="imageStudio"
    x-on:keydown.window="handleKeyboard"
    x-on:pointermove.window="movePointer"
    x-on:pointerup.window="stopPointer"
    x-on:pointercancel.window="stopPointer"
>
    <header class="studio-heading">
        <div class="studio-heading__copy">
            <p class="studio-kicker">Laravel Cloud</p>
            <h1>YouTube thumbnail studio</h1>
            <p>Compose on-brand thumbnails with the approved Cloud system.</p>
        </div>

        <flux:button
            size="sm"
            variant="primary"
            color="zinc"
            icon="arrow-down-tray"
            aria-label="Export PNG"
            x-on:click="exportPng"
            x-bind:disabled="!canExport"
        >
            <span x-text="exportButtonLabel">Export PNG</span>
        </flux:button>
    </header>

    <div class="studio-workspace">
        <aside class="studio-panel" aria-label="Thumbnail controls">
            <div class="studio-panel__intro">
                <div>
                    <p class="studio-panel__eyebrow">Thumbnail</p>
                    <h2>Content is flexible. Brand stays locked.</h2>
                </div>
                <span class="studio-panel__format">1280 × 720 · 2× export</span>
            </div>

            <div class="studio-panel__section">
                <div class="studio-panel__heading">
                    <span>Headline</span>
                    <span class="studio-panel__meta tabular-nums" x-text="`${headline.length}/56`"></span>
                </div>

                <flux:field>
                    <flux:label>Thumbnail copy</flux:label>
                    <flux:textarea
                        name="headline"
                        rows="4"
                        resize="none"
                        maxlength="56"
                        placeholder="SHIP WITH CONFIDENCE"
                        x-model="headline"
                        x-on:input="queueHeadlineValidation"
                        x-on:blur="normalizeHeadline"
                        x-bind:aria-invalid="Boolean(headlineError)"
                        aria-describedby="headline-help headline-error"
                    />
                </flux:field>

                <div class="studio-field-meta" id="headline-help">
                    <span>Four words or fewer. No trailing punctuation.</span>
                    <span class="tabular-nums" x-text="`${headlineLineCount}/4 lines`"></span>
                </div>

                <p class="studio-field-error" id="headline-error" role="alert" x-show="headlineError" x-text="headlineError"></p>
            </div>

            <fieldset class="studio-panel__section">
                <legend class="studio-panel__heading">
                    <span>Background</span>
                    <span class="studio-panel__meta">Blue recommended</span>
                </legend>

                <div class="background-options">
                    <button
                        type="button"
                        class="background-option"
                        data-background="dark"
                        data-figma-node="4070:370747"
                        x-bind:aria-pressed="background === 'dark'"
                        x-on:click="selectBackground"
                    >
                        <span class="background-option__preview background-option__preview--dark" aria-hidden="true"></span>
                        <span>Dark</span>
                    </button>
                    <button
                        type="button"
                        class="background-option"
                        data-background="light"
                        data-figma-node="4070:370786"
                        x-bind:aria-pressed="background === 'light'"
                        x-on:click="selectBackground"
                    >
                        <span class="background-option__preview background-option__preview--light" aria-hidden="true"></span>
                        <span>Light</span>
                    </button>
                    <button
                        type="button"
                        class="background-option"
                        data-background="blue"
                        data-figma-node="4070:370825"
                        x-bind:aria-pressed="background === 'blue'"
                        x-on:click="selectBackground"
                    >
                        <span class="background-option__preview background-option__preview--blue" aria-hidden="true"></span>
                        <span>Blue</span>
                    </button>
                </div>
            </fieldset>

            <div class="studio-panel__section">
                <div class="studio-panel__heading">
                    <span>Subject</span>
                    <span class="studio-panel__meta">One transparent cutout</span>
                </div>

                <flux:file-upload
                    class="studio-file-upload"
                    name="subject"
                    data-upload-dropzone="subject"
                    accept="image/png"
                    x-on:change="handleSubjectUpload"
                >
                    <flux:file-upload.dropzone inline icon="photo" heading="Drop subject PNG" text="Transparent PNG · 15 MB max" />
                </flux:file-upload>

                <div class="subject-file" x-show="subject.src">
                    <img x-bind:src="subject.src" alt="" />
                    <div>
                        <p x-text="subject.name"></p>
                        <span>Drag and resize inside the subject zone.</span>
                    </div>
                    <flux:button size="xs" variant="ghost" icon="x-mark" aria-label="Remove subject" x-on:click="removeSubject" />
                </div>

                <p class="studio-panel__hint">
                    Use a waist-up person cutout or an isometric illustration. The locked treatment is applied automatically.
                </p>
            </div>
        </aside>

        <main class="studio-stage" aria-label="Thumbnail canvas">
            <div class="studio-stage__toolbar">
                <span x-text="activeBackground.label"></span>
                <span>Feed-safe preview</span>
            </div>

            <div class="studio-stage__surface">
                <div
                    class="artboard"
                    data-image-studio-canvas
                    data-figma-file="VSARPN3yuAv3TICdMX3ZlC"
                    x-bind:data-figma-node="activeBackground.figmaNode"
                    x-bind:class="artboardClassNames"
                    x-bind:style="artboardStyle"
                    x-on:pointerdown.self="deselectSubject"
                >
                    <img class="artboard__background" x-bind:src="activeBackground.asset" alt="" draggable="false" />
                    <img class="artboard__logo" x-bind:src="activeBackground.logo" alt="Laravel Cloud" draggable="false" />

                    <div class="artboard__copy">
                        <h2 x-ref="headline" x-text="displayHeadline"></h2>
                    </div>

                    <div class="artboard__subject-zone" data-export-ignore aria-hidden="true">
                        <span>Subject zone</span>
                    </div>

                    <div class="artboard__empty-subject" x-show="!subject.src" data-export-ignore>
                        <flux:icon.photo />
                        <span>Add a subject</span>
                    </div>

                    <button
                        class="artboard-subject"
                        type="button"
                        x-show="subject.src"
                        x-bind:class="{ 'is-selected': isSubjectSelected }"
                        x-bind:style="subjectStyle"
                        x-on:pointerdown="startSubjectDrag"
                        x-on:click="selectSubject"
                        x-bind:aria-label="`Move ${subject.name}`"
                    >
                        <img class="artboard-subject__shadow" x-bind:src="subject.src" alt="" draggable="false" />
                        <img class="artboard-subject__warm" x-bind:src="subject.src" alt="" draggable="false" />
                        <img class="artboard-subject__overlay" x-bind:src="subject.src" alt="" draggable="false" />
                        <img class="artboard-subject__image" x-bind:src="subject.src" x-bind:alt="subject.name" draggable="false" />
                        <span
                            class="artboard-subject__handle artboard-subject__handle--top-left"
                            data-resize-handle="top-left"
                            data-export-ignore
                            aria-hidden="true"
                            x-on:pointerdown.stop.prevent="startSubjectResize"
                            x-on:click.stop
                        ></span>
                        <span
                            class="artboard-subject__handle artboard-subject__handle--top-right"
                            data-resize-handle="top-right"
                            data-export-ignore
                            aria-hidden="true"
                            x-on:pointerdown.stop.prevent="startSubjectResize"
                            x-on:click.stop
                        ></span>
                        <span
                            class="artboard-subject__handle artboard-subject__handle--bottom-left"
                            data-resize-handle="bottom-left"
                            data-export-ignore
                            aria-hidden="true"
                            x-on:pointerdown.stop.prevent="startSubjectResize"
                            x-on:click.stop
                        ></span>
                        <span
                            class="artboard-subject__handle artboard-subject__handle--bottom-right"
                            data-resize-handle="bottom-right"
                            data-export-ignore
                            aria-hidden="true"
                            x-on:pointerdown.stop.prevent="startSubjectResize"
                            x-on:click.stop
                        ></span>
                    </button>
                </div>
            </div>

            <div class="studio-stage__footer" aria-live="polite">
                <span x-text="statusMessage"></span>
                <span>Logo, typography, color, and layout are locked by Design.</span>
            </div>
        </main>
    </div>
</section>
