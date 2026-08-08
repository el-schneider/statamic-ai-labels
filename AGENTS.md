**Golden Rule: maintain AGENTS.md as you work.** Every pitfall you document saves future agents and humans from repeating the same mistake. Keep entries minimal and terse — only what can't be discovered by reading the code.

## Project Overview

**AI Labels** — Labels AI-generated/-manipulated media in Statamic per EU AI Act Art. 50(4) and the EU Code of Practice on marking and labelling of AI-generated content. Editorial per-asset classification, layered rendering surfaces (burned-in Glide watermark, markup partials, metadata write-back). Never claims compliance.

Package: `el-schneider/statamic-ai-labels` · Statamic v6 · PHP 8.2+ (runtime) / 8.4+ (dev tooling)

> ⚠️ **Pre-v1, active development.** Breaking changes acceptable and expected. Remove this notice at v1.

## Development Commands

```bash
./vendor/bin/pest                    # Pest 5
./vendor/bin/pest --filter=SomeTest
./vendor/bin/pint --test             # style check
```

Tests run on Testbench 11 / Laravel 13; sandbox is Laravel 12. Both are supported by `statamic/cms ^6`.

## Contributing

- Comments say why, not what changed. History belongs in the PR.
- Template/tag output changes: verify rendered output in a real Statamic app and say what you checked.
- Add nothing you can derive or reuse.
- Fix the cause, not the reported symptom.
- No abstraction with a single caller.
- Let failures surface. No try/catch for tidiness. Silent non-labelling is this addon's worst failure mode — fail loud.
- Never use the word "compliant"/"compliance" in README, UI strings, or marketplace copy.

## Testing Pitfalls

- `AddonTestCase` does not load Statamic CP routes; CP-save tests must register core controller route explicitly.

## Off-Limits Files

- `vendor/` — managed by Composer.

## Pitfalls

- `docs/` is ignored; append decision-log entries with `>>`, never overwrite with a file-write tool.

## Local Setup

See `AGENTS.local.md` (untracked) for sandboxes, design docs, and machine-specific notes.
