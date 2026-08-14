---
paths:
    - resources/js/image-studio.js
---

# Js

## Lay out exports at final dimensions

modern-screenshot freezes computed child styles from the source node. Export from an off-screen clone already sized to the final 1280×720 or 1200×630 dimensions; resizing only inside domToPng causes percentage and container-unit layers to drift.
