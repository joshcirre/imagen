---
paths:
    - '{app/Http/Controllers/SharedImageController.php,resources/{js,views}/**}'
---

# Controllersjsviews

## Saved images are immediately shared

The private filesystem bucket is the source of truth for the image library. Keep uploads local until the user chooses “Save for everyone”; saving requires no approval state and makes the asset available to every editor session through signed, same-origin delivery.
