<x-maintenance.card>
    @if (count($result) > 0)
        <pre class="text-2xs m-4">
@foreach ($result as $resultLine)
{{ $resultLine }}
@endforeach
</pre>
    @else
        <x-maintenance.h1>{{ __('maintenance.optimize.title') }}</x-maintenance.h1>
        <x-maintenance.p>{{ __('maintenance.optimize.description') }}</x-maintenance.p>
        <x-maintenance.button wire:click="do" wire:loading.remove>{{ __('maintenance.optimize.button') }}</x-maintenance.button>
        <x-maintenance.loading />
    @endif
</x-maintenance.card>
