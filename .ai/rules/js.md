---
paths:
    - resources/js/image-studio.js
---

# Js

## Lay out exports at final dimensions

modern-screenshot freezes computed child styles from the source node. Export from an off-screen clone already sized to the final 1280×720 or 1200×630 dimensions; resizing only inside domToPng causes percentage and container-unit layers to drift.

## Export thumbnails at 2× raster density

Keep the editable thumbnail layout and off-screen export clone at 1280×720, then rasterize thumbnail PNGs with scale 2 for a 2560×1440 result. Keep Open Graph exports at their approved 1200×630 output size.
