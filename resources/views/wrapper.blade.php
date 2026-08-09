{{-- Pass mark="" to Glide inside this wrapper. The tag cannot rewrite slot content. --}}
<figure class="ai-label-wrapper" data-ai-label="{{ $label }}" style="container-type:inline-size;position:relative">
    {!! $slot !!}
    <div style="bottom:.5rem;position:absolute;right:.5rem">
        {!! $badge !!}
    </div>
</figure>
