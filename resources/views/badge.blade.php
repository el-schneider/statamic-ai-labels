<style>
.ai-label-badge{align-items:center;background:#111;color:#fff;display:flex;gap:.35rem;padding:.3rem .45rem}.ai-label-badge img{height:1.25rem;width:auto}.sr-only{clip:rect(0,0,0,0);clip-path:inset(50%);height:1px;overflow:hidden;position:absolute;white-space:nowrap;width:1px}@container (max-width: 320px){.ai-label-badge__text{display:none}}@container (max-width: 180px){.ai-label-badge img{display:none}}
</style>
<span class="ai-label-badge ai-label-badge--{{ config('ai_labels.position') }}" style="pointer-events:none">
    <img src="{{ asset($badge) }}" alt="" aria-hidden="true">
    <span class="ai-label-badge__text">{{ $text }}</span>
    <span class="sr-only">{{ $text }}</span>
</span>
