---
paths:
    - 'public/fonts/**, resources/{css,js,views}/**'
---

# Fontscssjsviews

## Self-host export fonts

Keep Instrument Sans, Instrument Serif, and Rajdhani same-origin under public/fonts and define them in app.css. SVG-based PNG export cannot reliably embed cross-origin Google Font stylesheets and otherwise falls back to differently sized system fonts.
