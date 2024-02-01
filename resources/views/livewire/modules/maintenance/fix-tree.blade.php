<x-maintenance.card :disabled="$this->no_errors_found && $result === null">
    <x-maintenance.h1>{{ __('maintenance.fix-tree.title') }}</x-maintenance.h1>
    <x-maintenance.p class=" !text-left">
		{{ __('maintenance.fix-tree.Oddness') }}: {{ $stats['oddness'] }}<br/>
		{{ __('maintenance.fix-tree.Duplicates') }}: {{ $stats['duplicates'] }}<br/>
		{{ __('maintenance.fix-tree.Wrong parents') }}: {{ $stats['wrong_parent'] }}<br/>
		{{ __('maintenance.fix-tree.Missing parents') }}: {{ $stats['missing_parent'] }}<br/>
    </x-maintenance.p>
    <x-maintenance.button wire:click="do" wire:loading.remove>{{ __('maintenance.fix-tree.button') }}</x-maintenance.button>
    <x-maintenance.loading />
</x-maintenance.card>