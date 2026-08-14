---
name: add-figma-thumbnail-template
description: Import a designer-provided, node-specific Figma YouTube thumbnail frame into Imagen as an exact editable template. Use when adding or updating a thumbnail from a Figma design URL and the implementation must preserve the source background, logos, typography, alignment, spacing, effects, and replaceable image slots rather than merely imitate the layout.
---

# Add Figma Thumbnail Template

Import one finished Figma thumbnail frame into Imagen as a faithful, editable 1280 × 720 recipe. Treat the Figma frame as the source of truth and verify the live editor and exported PNG against it.

## Required Input

Require a Figma `/design/` URL with a `node-id` for the finished thumbnail frame. Convert URL node IDs such as `5-9008` to Figma IDs such as `5:9008`.

If the URL points to a page or section containing several finished frames, inspect it first. Select the requested frame from clear surrounding context; ask the user only when multiple plausible frames remain ambiguous.

## Before Editing

1. Read the repository `AGENTS.md` instructions and `.ai/rules/index.md`.
2. Read every project rule whose glob covers the files in scope, then search `.ai/rules` for `figma`, `thumbnail`, the template name, and the affected asset or code terms.
3. Use Laravel Boost `application-info` and `search-docs` before application code changes.
4. Invoke the available `figma` skill and the mandatory `figma:figma-design-to-code` skill before fetching design context.
5. Invoke the project skills required by the affected code, including `pest-testing` for test changes and `playwriter` for browser verification. Follow any additional domain skill triggers from `AGENTS.md`.
6. Inspect the current recipe, picker, canvas, CSS, tests, and nearby templates before adding code.
7. Check the current branch and dirty worktree. Preserve unrelated user changes. Do not create a branch, commit, push, or open a pull request unless the user asks.

## Import Workflow

### 1. Capture the Source Frame

Fetch design context and a screenshot for the exact node. Record:

- file key, normalized node ID, page, frame name, and source dimensions;
- every text run's content, family, style, weight, width/stretch, size, line height, tracking, case, alignment, and bounding box;
- logo and brand-mark geometry;
- colors, gradients, strokes, shadows, blur, opacity, blend mode, masks, corner radii, rotation, and layer order;
- all raster/vector assets and their bounds;
- regions intended for replaceable people, products, screenshots, or other user images.

When the context response is too large, query meaningful child nodes recursively. Never infer small typography or geometry details from the screenshot when node properties are available.

Save the exact frame screenshot as `public/img/youtube-templates/<slug>-preview.png`. It must be 1280 × 720. Use the screenshot as the visual baseline, not as the editable template background.

### 2. Decompose the Frame

Classify every visible layer before implementing it:

- **Editable:** headline, eyebrow, supporting copy, logo, and designer-designated replaceable image slots.
- **Template art:** fixed backgrounds, grids, gradients, watermarks, product UI, decorative vectors, dividers, and masks.
- **Reference only:** the full-frame preview used by the picker and comparison.

Do not flatten editable text, logos, or replaceable subjects into a background. Preserve exact source assets instead of redrawing logos, icons, or complex UI. If the source itself is indivisible, explain the limitation and keep replacement overlays separate.

### 3. Persist Assets

Download Figma assets immediately into `public/img/youtube-templates/` with stable, descriptive, slug-prefixed names where practical. Temporary `figma.com/api/mcp/asset` URLs must never remain in application source.

Preserve SVG vectors and raster transparency. Avoid upscaling. Downsize oversized raster assets to the maximum resolution needed for a 1280 × 720 canvas while retaining enough pixels for their rendered bounds. Do not change dependencies merely to process an asset.

Use the exact font when it is licensed and available through the project's font-loading approach. Add the required family/style/weight to the application layout when missing. Never silently substitute a different typeface; report a licensing or availability blocker.

### 4. Implement the Recipe

Read [the Imagen template contract](references/imagen-template-contract.md), then update all required integration points:

1. Add the recipe to `thumbnailTemplates` in `resources/js/image-studio.js`.
2. Add the Figma-backed picker item and its exact preview in `resources/views/components/image-studio.blade.php`.
3. Add fixed template-art layers separately from editable copy, logo, and uploaded image layers.
4. Add template-scoped rules in `resources/css/app.css` for background, art, logo, copy, image slots, typography, effects, and stacking.
5. Add missing font loading to `resources/views/layouts/app.blade.php` only when required.
6. Update `tests/Feature/Pages/DashboardTest.php` with the recipe, node, preview, source assets, and distinctive typography/art assertions.
7. Record a durable Boost rule when the template intentionally overrides a generic editor convention or reveals a non-obvious constraint.

Derive metrics from the source frame rather than eyeballing. For a source frame `W × H`, use:

- horizontal position/width: `value / W × 100%`;
- vertical position/height: `value / H × 100%`;
- canvas-relative type/effect sizes: `value / W × 100cqw` where appropriate.

Preserve the original layer order. Keep template-specific overrides under `.artboard--<slug>` so they cannot alter generic templates.

### 5. Audit and Verify

Run the included audit after implementation:

```bash
python3 .agents/skills/add-figma-thumbnail-template/scripts/audit_template.py \
  --root . \
  --slug <slug> \
  --node-id <node:id> \
  --asset <slug>-logo.svg \
  --asset <slug>-background.png
```

Then run, at minimum:

```bash
php artisan test --compact tests/Feature/Pages/DashboardTest.php
npx prettier --check resources/js/image-studio.js resources/css/app.css resources/views/components/image-studio.blade.php
npx oxlint resources/js/image-studio.js
npm run build
git diff --check
```

Run `vendor/bin/pint --dirty --format agent` when PHP files changed. Add or run other focused tests required by the affected behavior.

Use the project `playwriter` skill against the running Imagen app. Select the new template, capture the live canvas at its final aspect ratio, and compare it side by side with `<slug>-preview.png`. Verify edited copy, logo dragging/scaling, every image slot, reset-to-Figma-defaults, and PNG export. Check recent browser logs for runtime or export errors.

Do not call the template complete while a visible mismatch remains in typography, alignment, spacing, background treatment, effects, asset placement, or layer order.

## Completion Report

Report the template name and Figma node, preserved editable layers, assets added, verification performed, and current branch/commit/push status. Call out any source limitation explicitly.
