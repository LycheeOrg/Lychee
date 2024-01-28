<x-maintenance.card>
    @if (count($result) > 0)
        <pre class="text-2xs m-4">
@foreach ($result as $resultLine)
{{ $resultLine }}
@endforeach
</pre>
    @else
        <x-maintenance.h1>{{ __('maintenance.update.title') }}</x-maintenance.h1>
        <x-maintenance.p>{{ $channelName }}<br>{{ $info }}<br>{{ $extra }}</x-maintenance.p>
        @if($can_check)
        <x-maintenance.button wire:click="check" wire:loading.remove>{{ __('lychee.CHECK_FOR_UPDATE') }}</x-maintenance.button>
        @endif
        @if($can_update)
        <x-maintenance.button href="{{ route('update') }}">{{ __('lychee.UPDATE') }}</x-maintenance.button>
        @endif
        <x-maintenance.loading />
        @if(!$can_update && !$can_check)
        <span
            class="absolute mt-4 bottom-0 text-primary-500 font-bold text-center block w-full pb-4">{{ __('maintenance.update.no-pending-updates') }}
        </span>
        @endif
    @endif
</x-maintenance.card>
