<x-maintenance.card :disabled="$this->number_of_size_variants_to_generate === 0">
    <x-maintenance.h1>{{ sprintf(__('maintenance.gen-sizevariants.title'), $this->sv->name()) }}</x-maintenance.h1>
    <x-maintenance.p>{!! sprintf(__('maintenance.gen-sizevariants.description'), $this->number_of_size_variants_to_generate, $this->sv->name()) !!}</x-maintenance.p>
    <x-maintenance.button wire:click="do" wire:loading.remove>{{ __('maintenance.gen-sizevariants.button') }}</x-maintenance.button>
    <x-maintenance.loading />
</x-maintenance.card>