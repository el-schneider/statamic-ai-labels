<?php

namespace ElSchneider\StatamicAiLabels\Tags;

use ElSchneider\StatamicAiLabels\ServiceProvider;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Asset as Assets;
use Statamic\Tags\Tags;

class AiLabel extends Tags
{
    public function index(): string
    {
        $content = $this->parse();

        if (! $label = $this->label()) {
            return $content;
        }

        return view('statamic-ai-labels::wrapper', [
            'badge' => $this->renderBadge($label),
            'label' => $label,
            'slot' => $content,
        ])->render();
    }

    public function badge(): string
    {
        if (! $label = $this->label()) {
            return '';
        }

        return $this->renderBadge($label);
    }

    private function label(): ?string
    {
        return ServiceProvider::label($this->asset()?->get(config('ai_labels.field_handle')));
    }

    private function asset(): ?Asset
    {
        $asset = $this->params->get('asset', $this->context->value('asset'));

        return $asset instanceof Asset ? $asset : Assets::find($asset);
    }

    private function renderBadge(string $label): string
    {
        return view('statamic-ai-labels::badge', [
            'badge' => config("ai_labels.badges.{$label}"),
            'label' => $label,
            'text' => __('statamic-ai-labels::labels.disclosure.'.$label),
        ])->render();
    }
}
