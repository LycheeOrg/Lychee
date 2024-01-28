<x-maintenance.card :disabled="$this->no_file_found">
    @if (count($result) > 0)
        <pre class="text-2xs m-4">
@foreach ($result as $resultLine)
{{ $resultLine }}
@endforeach
	</pre>
    @else
        <x-maintenance.h1>{{ sprintf(__('maintenance.cleaning.title'), str_replace(storage_path() . '/', '', $path)) }}</x-maintenance.h1>
        <x-maintenance.p>{!! sprintf(__('maintenance.cleaning.description'), str_replace(base_path() . '/', '', $path)) !!}</x-maintenance.p>
        <x-forms.buttons.danger class="absolute mt-4 bottom-0 border-t border-t-bg-800 rounded-br-md rounded-bl-md w-full !block"
            wire:click="do" wire:loading.remove>
            {{ __('maintenance.cleaning.button') }}
        </x-forms.buttons.danger>
        <x-maintenance.loading />
    @endif
</x-maintenance.card>
