@props(['disabled' => false])
<div class="text-text-main-400 text-sm rounded-lg min-h-40 shadow shadow-bg-950 relative {{ $disabled ? 'hidden' : '' }}">
{{ $slot }}
</div>