# Statamic AI Labels

Editorial labels for AI-generated or AI-manipulated Statamic assets.

## Install

```bash
composer require el-schneider/statamic-ai-labels
php artisan vendor:publish --tag=statamic-ai-labels
php artisan vendor:publish --tag=ai-labels-config
```

Badge watermark files must live below `public_path()` because Glide reads its default watermark filesystem there. Configure custom paths relative to that directory in `ai_labels.badges`.

Asset labels are global per asset, not localized. Asset-less media needs manual markup in a later release.

## Template badge

Wrap editable media with `{{ ai_label :asset="image" }}…{{ /ai_label }}` or render `{{ ai_label:badge :asset="image" }}`. When slot uses Glide, pass `mark=""` to that Glide call so the wrapper overlay is the only badge. The tag emits `data-ai-label` to make missed suppression visible in rendered HTML.

## Badge assets

TODO: replace placeholder pill PNGs with official EU icon pills. Custom badges remain supported and must be clear AI disclosures.
