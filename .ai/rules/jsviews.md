---
paths:
    - 'resources/{js,views}/**'
---

# Jsviews

## Sanitize Alpine directives from export clones

modern-screenshot serializes the artboard clone as XML. Remove Alpine directive attributes (`x-*`, `@*`, and `:*`) in `onCloneEachNode` after computed state/styles are copied, or the SVG becomes invalid and exports as only the fallback color.
