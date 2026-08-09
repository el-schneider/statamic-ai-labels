# AI Labels

AI Labels is a Statamic labelling toolkit for editorially classified AI-generated and AI-modified media. It adds an asset field and puts a visible EU AI label on Glide derivatives without template changes. It has deliberate limits: operators decide which assets need disclosure, and not every delivery path can be labelled automatically.

## Install

```bash
composer require el-schneider/statamic-ai-labels
```

## How it works

L0 adds an editorial `ai_label` choice (`AI`, `AI generated`, or `AI modified`) to CP asset forms; no value means unlabelled. L1 burns the matching badge into Glide-rendered image derivatives, including CP thumbnails, and changes derivative URLs when the label changes. L1.5 adds label markup to flagged Bard images because this addon owns that rendered markup. L2 is the `{{ ai_label }}` tag for template-controlled media, where an accessible responsive overlay is preferable; pass `mark=""` to Glide inside its slot so only L2 appears.

Asset labels are global per asset, not localized. This package never detects or infers AI use.

## Configuration

Publish configuration only when defaults need changing:

```bash
php artisan vendor:publish --tag=ai-labels-config
```

| Key | Default | Purpose |
| --- | --- | --- |
| `badges.ai`, `badges.generated`, `badges.modified` | shipped EU pills | Paths below `public_path()` used by L1 and L2. Replace any path with a custom PNG, but keep custom badges a clear AI disclosure. Plain official alternatives are published at `vendor/statamic-ai-labels/badges/eu/{ai,generated,modified}-{black,white}.png`. |
| `position` | `bottom-right` | Glide watermark position. |
| `clamp.min`, `clamp.max` | `32`, `96` | Minimum and maximum pre-DPR watermark width in pixels. |
| `small_derivative.threshold` | `200` | Derivatives below this width use the minimum icon badge. |
| `small_derivative.behavior` | `icon` | `icon` keeps the minimum badge at small sizes; set `skip` only when omission is intentional. |
| `field_handle` | `ai_label` | Asset field handle, for sites with a collision. |

## Template-controlled media (L2)

```antlers
{{ ai_label :asset="image" }}
    {{ glide :src="image" width="800" mark="" }}
{{ /ai_label }}
```

Use `{{ ai_label:badge :asset="image" }}` for an adjacent label, including audio and external video placeholders. The tag renders nothing for an unlabelled asset.

## Capability matrix

“Visible at first exposure” and “survives download/reshare” describe delivery paths, not a legal conclusion. L1 badges are burned into the derivative, so saving that derivative retains its badge; saving an original does not.

| Delivery mode | Visible at first exposure | Survives download/reshare |
| --- | --- | --- |
| Glide-rendered image | L1 ✓ | L1 ✓ as downloaded derivative |
| Raw asset URL image (`{{ asset:url }}`) | Not covered — operator action required (L3) | Not covered — operator action required (L3) |
| CSS background image | Not covered — operator action required | Not covered — operator action required |
| Video poster rendered through Glide with ffmpeg | L1 ✓, conditional on thumbnail extraction | L1 ✓ as downloaded derivative, conditional |
| External video embed | L2 placeholder only | Not covered — operator action required |
| Audio | L2 adjacent label only | Not covered — operator action required |
| Small Glide derivative | L1 minimal icon badge ✓ | L1 ✓ as downloaded derivative |
| JavaScript-injected content | Not covered — operator action required | Not covered — operator action required |
| Hotlinked original | Not covered — operator action required (L3) | Not covered — operator action required (L3) |

Use L2 where templates are editable, especially for tight crops, fixed-ratio cards, audio, and embeds. Glide applies its watermark after its own crop and resize, but CSS `object-fit` or extreme `object-position` can still crop a corner from view. Raw URLs, CSS backgrounds, external embeds, hotlinks, and JavaScript-injected media need operator action.

## Legal framing

Article 50(4) of the EU AI Act places a visible-disclosure duty on deployers for specified deepfakes and certain AI-generated public-interest text from 2 August 2026. Article 50(2) places machine-readable marking duties on providers, not site operators. Scope is fact-specific: assess content and exceptions, including artistic or satirical works and human editorial responsibility, against your facts.

This toolkit supports an editorial labelling workflow. It is not legal advice and does not decide whether a case is in scope. Read [Regulation (EU) 2024/1689, Article 50](https://eur-lex.europa.eu/eli/reg/2024/1689/oj) and the European Commission’s [Code of Practice on marking and labelling of AI-generated content](https://digital-strategy.ec.europa.eu/en/library/code-practice-marking-and-labelling-ai-generated-content) with appropriate advice.

## Roadmap

Planned work: L3 original-file badging, L4 metadata write-back, Markdown image renderer, and approval records. These are not configuration switches today.

## Credits and licence

Default and plain badges use European Commission EU AI label icons. The Commission permits free use without attribution. Custom badges remain the operator’s responsibility and must remain a clear AI disclosure.

Package source is [MIT licensed](LICENSE).
