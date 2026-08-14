# Imagen Thumbnail Template Contract

Use this contract after inspecting the current surrounding code. Existing conventions win when the application evolves.

## Canonical Integration Points

| Concern                  | File                                                | Required result                                                                        |
| ------------------------ | --------------------------------------------------- | -------------------------------------------------------------------------------------- |
| Recipe/state             | `resources/js/image-studio.js`                      | One `thumbnailTemplates` entry keyed by the slug                                       |
| Picker and canvas layers | `resources/views/components/image-studio.blade.php` | One picker button plus only the fixed template-art markup the recipe needs             |
| Exact presentation       | `resources/css/app.css`                             | Template-scoped background, typography, geometry, effects, and stacking                |
| Font delivery            | `resources/views/layouts/app.blade.php`             | Exact missing source font styles/weights, loaded once                                  |
| Permanent assets         | `public/img/youtube-templates/`                     | 1280 × 720 preview and every referenced vector/raster asset                            |
| Regression coverage      | `tests/Feature/Pages/DashboardTest.php`             | Recipe, Figma node, preview dimensions, assets, markup, and distinctive CSS assertions |

## Recipe Shape

Follow the live schema in `thumbnailTemplates`. A normal entry contains:

```js
'template-slug': {
    label: 'Human label',
    brand: 'laravel',
    figmaNode: '12:345',
    fontLabel: 'Exact Family Style',
    logo: '/img/youtube-templates/template-slug-logo.svg',
    alignment: 'left',
    headlineSize: 100,
    eyebrow: 'Optional eyebrow',
    title: 'Editable headline',
    supportingText: 'Optional supporting copy',
    slotLabel: 'Add person cutout',
    imageSlots: [{ x: 80, y: 58, size: 50, rotation: 0, layer: 'above' }],
},
```

- `figmaNode` is the normalized source node ID, not the page ID.
- `fontLabel` names the actual template type system for the UI.
- `logo` points to the exact permanent source asset or is `null` only when the design has no logo.
- `headlineSize` remains the editor's relative size control default; exact template CSS multiplies its source size by `--headline-scale`.
- `imageSlots` describe default positions for user-replaceable layers. Use `layer: 'behind'` or `'above'` to reproduce the source stacking.
- Use `slotLabel: null` and an empty `imageSlots` array when the frame has no replaceable subject.

Do not add fields speculatively. Extend the shared schema only when the new design requires behavior the current recipe cannot express, and test that behavior.

## Picker Contract

Add a button containing:

- `data-template="template-slug"`;
- `data-figma-node="12:345"`;
- `x-on:click="selectTemplate"`;
- `x-bind:aria-pressed="template === 'template-slug'"`;
- `<img class="template-preview" src="/img/youtube-templates/template-slug-preview.png" alt="" />`;
- the human label.

The preview is the direct full-frame Figma screenshot. It is a baseline and picker image only.

## Canvas Layer Contract

Keep the shared layer model intact:

1. template background and fixed art;
2. user image layers whose recipe says `behind`;
3. editable logo and copy;
4. user image layers whose recipe says `above`;
5. editor-only controls and safe areas, excluded from export.

Add fixed art inside `.artboard__template-art` under a template-specific wrapper and show it only for the active slug. Do not duplicate shared editable logo/copy/image markup.

When a custom background hides template art, preserve the application's existing behavior unless the source requires a documented exception.

## CSS Contract

Scope every source-specific rule beneath `.artboard--template-slug` or `.template-art--template-slug`.

Define all details that make the frame recognizable:

- base color, source background, grid, and gradients;
- fixed art bounds, crop, mask, blend mode, opacity, rotation, and shadow;
- logo bounds and effects;
- copy bounding box and alignment;
- exact family, weight, stretch, style, size, line height, tracking, case, wrapping, and shadow for each text role;
- image-slot bounds and layer order;
- decorative borders, rules, and overlays.

Use percentages for bounds relative to the 1280 × 720 frame and `cqw`/`cqh` for canvas-relative lengths. Do not use viewport units. Do not change generic headline metrics to accommodate one Figma frame.

## Asset Contract

- Required preview: `<slug>-preview.png`, exactly 1280 × 720.
- Use stable local `/img/youtube-templates/...` paths.
- Preserve SVGs when available and meaningful transparency for PNG/WebP assets.
- Name assets descriptively; prefix with the template slug when it avoids ambiguity.
- Never commit temporary Figma MCP asset URLs, data URLs, screenshots from browser automation, or throwaway comparison montages.
- Never use the full preview as the implementation background to fake fidelity.

## Test Contract

Extend the focused dashboard feature test to prove:

- the recipe slug exists in JavaScript;
- the exact `figmaNode` exists;
- the picker renders the label and node metadata;
- the 1280 × 720 preview exists;
- every referenced permanent asset exists;
- template-specific CSS includes the distinctive typography and art selectors;
- the existing export safeguards remain present.

The included Python audit complements Pest; it does not replace application tests or visual browser verification.
