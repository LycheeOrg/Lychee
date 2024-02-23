<x-maintenance.card :disabled="$this->number_of_missing_size <= 0">
    <x-maintenance.h1>{{ __('maintenance.fill-filesize-sizevariants.title') }}</x-maintenance.h1>
    <x-maintenance.p>{!! sprintf(__('maintenance.fill-filesize-sizevariants.description'), $this->number_of_missing_size) !!}</x-maintenance.p>
    <x-maintenance.button wire:click="do" wire:loading.remove>{{ __('maintenance.fill-filesize-sizevariants.button') }}</x-maintenance.button>
    <x-maintenance.loading />
</x-maintenance.card>