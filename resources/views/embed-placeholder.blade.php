<div class="ai-label-embed-placeholder" style="position:relative">
    {!! $slot ?? '' !!}
    {!! view('statamic-ai-labels::badge', compact('badge', 'label', 'text'))->render() !!}
</div>
