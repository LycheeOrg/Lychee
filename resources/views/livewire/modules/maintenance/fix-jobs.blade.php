<x-maintenance.card :disabled="$this->no_waiting_jobs_found">
    <x-maintenance.h1>{{ __('maintenance.fix-jobs.title') }}</x-maintenance.h1>
    <x-maintenance.p>{!! sprintf(__('maintenance.fix-jobs.description'), 'ready', 'started', 'failed') !!}</x-maintenance.p>
    <x-maintenance.button wire:click="do" wire:loading.remove>{{ __('maintenance.fix-jobs.button') }}</x-maintenance.button>
    <x-maintenance.loading />
</x-maintenance.card>