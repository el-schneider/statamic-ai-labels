<?php

namespace ElSchneider\StatamicAiLabels;

use Statamic\Facades\Asset;
use Statamic\Fieldtypes\Bard\ImageNode;
use Statamic\Support\Str;
use Tiptap\Utils\HTML;

class BadgedImageNode extends ImageNode
{
    public function renderHTML($node, $HTMLAttributes = [])
    {
        if (! $label = ServiceProvider::label($this->asset($node)?->get(config('ai_labels.field_handle')))) {
            return parent::renderHTML($node, $HTMLAttributes);
        }

        $text = __('statamic-ai-labels::labels.disclosure.'.$label);
        $image = HTML::renderAttributes(HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes));

        return [
            'content' => '<figure class="ai-label-bard" data-ai-label="'.$label.'">'
                .'<img'.$image.'><figcaption class="ai-label-bard__badge">'.$text.'</figcaption></figure>',
        ];
    }

    private function asset($node)
    {
        $src = $node->attrs->src ?? null;

        if (! $src) {
            return null;
        }

        return Str::startsWith($src, 'asset::')
            ? Asset::find(Str::after($src, 'asset::'))
            : null;
    }
}
