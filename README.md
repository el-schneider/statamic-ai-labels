<img src="images/ail_banner.png" alt="AI Labels">

> Simple labels for AI-generated content. Official EU icons on every Glide-rendered image — one field per asset, no template changes.

> [!IMPORTANT]
> **No legal advice, no compliance guarantee.** This addon renders labels. Whether and how your content must be disclosed — under the EU AI Act or any other law — is your decision and your responsibility. Defaults follow the official EU icons and placement guidance, and will evolve as practical requirements become clearer.

AI Labels adds an `AI label` field to every asset and puts a visible EU AI label on Glide derivatives without template changes. It has deliberate limits: you decide which assets need disclosure, and not every delivery path can be labelled automatically — the coverage table below says exactly which.

## Install

```bash
composer require el-schneider/statamic-ai-labels
```

That's the whole install: badge images self-publish on first boot, the `AI label` field appears on every asset, and Glide-rendered images of labelled assets carry the badge. Requires Statamic 6; runtime PHP `^8.2` (test suite runs on 8.4/8.5).

## How it works

An editorial `ai_label` choice (`AI`, `AI generated`, or `AI modified`) is added to CP asset forms; no value means unlabelled. The matching badge is burned into Glide-rendered image derivatives, including CP thumbnails, and derivative URLs change when the label changes. Label markup is added to flagged Bard images, since this addon owns that rendered markup. For template-controlled media where an accessible responsive overlay is preferable, use the `{{ ai_label }}` tag; pass `mark=""` to Glide inside its slot so only the tag's overlay renders (no burned-in badge underneath).

Asset labels are global per asset, not localized. This package never detects or infers AI use.

## Configuration

Publish configuration only when defaults need changing:

```bash
php artisan vendor:publish --tag=ai-labels-config
```

| Key | Default | Purpose |
| --- | --- | --- |
| `badges.ai`, `badges.generated`, `badges.modified` | shipped EU capsules | Paths below `public_path()` used by the burned-in badge and the `{{ ai_label }}` tag, either a string or `['path' => …, 'scale' => 0.4]`. `scale` multiplies the computed width — use it for badges whose aspect ratio differs from the wide capsules (the round basic icon ships with `0.4`). Replace any path with a custom PNG, but keep custom badges a clear AI disclosure. The full official icon set (black/white, with and without transparency) is available from the European Commission as [PNG](https://ec.europa.eu/newsroom/dae/redirection/document/129547) and [SVG](https://ec.europa.eu/newsroom/dae/redirection/document/129546). |
| `position` | `bottom-right` | Glide watermark position: `top-left`, `top`, `top-right`, `left`, `center`, `right`, `bottom-left`, `bottom`, `bottom-right`. The badge sits flush with the image edge; add transparent margin to the badge PNG for spacing. |
| `clamp.min`, `clamp.max` | `32`, `96` | Minimum and maximum pre-DPR watermark width in pixels. |
| `small_derivative.threshold` | `200` | Derivatives below this width use the minimum icon badge. |
| `small_derivative.behavior` | `icon` | `icon` keeps the minimum badge at small sizes; set `skip` only when omission is intentional. |
| `small_derivative.min_width` | `0` | Hard floor: derivatives narrower than this are never badged (`0` = always badge). Anything below the floor counts as an unlabelled delivery path — pair with the `{{ ai_label }}` tag or adjacent disclosure. |
| `field_handle` | `ai_label` | Asset field handle, for sites with a collision. |

## Template-controlled media

```antlers
{{ ai_label :asset="image" }}
    {{ glide :src="image" width="800" mark="" }}
{{ /ai_label }}
```

Use `{{ ai_label:badge :asset="image" }}` for an adjacent label, including audio and external video placeholders. The tag renders nothing for an unlabelled asset.

## Coverage by delivery path

“Visible at first exposure” and “survives download/reshare” describe delivery paths, not a legal conclusion. The burned-in badge is part of the derivative pixels, so saving that derivative retains its badge; saving an original does not.

| Delivery mode | Visible at first exposure | Survives download/reshare |
| --- | --- | --- |
| Glide-rendered image | Burned-in badge ✓ | Burned-in badge ✓ as downloaded derivative |
| Raw asset URL image (`{{ asset:url }}`) | Not covered — operator action required (original-file badging planned) | Not covered — operator action required (original-file badging planned) |
| CSS background image | Not covered — operator action required | Not covered — operator action required |
| Video poster rendered through Glide with ffmpeg | Burned-in badge ✓, conditional on thumbnail extraction | Burned-in badge ✓ as downloaded derivative, conditional |
| External video embed | `{{ ai_label }}` placeholder only | Not covered — operator action required |
| Audio | `{{ ai_label }}` adjacent label only | Not covered — operator action required |
| Small Glide derivative | Burned-in minimal icon badge ✓ | Burned-in badge ✓ as downloaded derivative |
| JavaScript-injected content | Not covered — operator action required | Not covered — operator action required |
| Hotlinked original | Not covered — operator action required (original-file badging planned) | Not covered — operator action required (original-file badging planned) |

Use the `{{ ai_label }}` tag where templates are editable, especially for tight crops, fixed-ratio cards, audio, and embeds. Glide applies its watermark after its own crop and resize, but CSS `object-fit` or extreme `object-position` can still crop a corner from view. Raw URLs, CSS backgrounds, external embeds, hotlinks, and JavaScript-injected media need operator action.

## Legal framing

Article 50(4) of the EU AI Act places a visible-disclosure duty on deployers for specified deepfakes and certain AI-generated public-interest text from 2 August 2026. Article 50(2) places machine-readable marking duties on providers, not site operators. Scope is fact-specific: assess content and exceptions, including artistic or satirical works and human editorial responsibility, against your facts.

This toolkit supports an editorial labelling workflow. It is not legal advice and does not decide whether a case is in scope. Read [Regulation (EU) 2024/1689, Article 50](https://eur-lex.europa.eu/eli/reg/2024/1689/oj) and the European Commission’s [Code of Practice on marking and labelling of AI-generated content](https://digital-strategy.ec.europa.eu/en/library/code-practice-marking-and-labelling-ai-generated-content) with appropriate advice.

## Roadmap

Planned work: original-file badging, metadata write-back, a Markdown image renderer, and approval records. These are not configuration switches today.

## Credits and licence

Default and plain badges use European Commission EU AI label icons. The Commission permits free use without attribution. Custom badges remain the operator’s responsibility and must remain a clear AI disclosure.

Package source is [MIT licensed](LICENSE).
