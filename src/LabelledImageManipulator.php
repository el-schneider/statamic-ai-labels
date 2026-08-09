<?php

namespace ElSchneider\StatamicAiLabels;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Imaging\ImageManipulator;
use Statamic\Facades\Asset as Assets;

class LabelledImageManipulator implements ImageManipulator
{
    private mixed $item = null;

    private array $params = [];

    public function __construct(private ImageManipulator $manipulator) {}

    public function item($item)
    {
        $this->item = $item;
        $this->manipulator->item($item);

        return $this;
    }

    public function build()
    {
        $params = $this->params;

        // Laravel turns mark="" into null before Glide verifies its signed URL.
        if (($params['mark'] ?? null) === '') {
            $params['mark'] = 'vendor/statamic-ai-labels/badges/empty.png';
        }

        $params = array_merge($params, LabelledImageGenerator::watermarkParams($this->asset(), $params));

        return $this->manipulator->params($params)->build();
    }

    public function params($params)
    {
        $this->params = $params;
        $this->manipulator->params($params);

        return $this;
    }

    public function setParam($param, $value)
    {
        $this->params[$param] = $value;
        $this->manipulator->setParam($param, $value);

        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function __call($method, $arguments)
    {
        $this->manipulator->$method(...$arguments);
        $this->params = $this->manipulator->getParams();

        return $this;
    }

    private function asset(): ?Asset
    {
        return $this->item instanceof Asset ? $this->item : Assets::find($this->item);
    }
}
