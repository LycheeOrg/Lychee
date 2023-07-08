<div class="w-full">
	<!-- toolbar -->
	<livewire:components.header
		:page_mode="App\Enum\Livewire\PageMode::SETTINGS"
		:title="__('lychee.SETTINGS')" />
	<div class="overflow-clip-auto">
		<div class="settings_view w-10/12 max-w-2xl text-neutral-400 text-sm mx-auto">
			<div class="pt-12">
				<p class="warning">
					{{ __("lychee.SETTINGS_ADVANCED_WARNING_EXPL") }}
				</p>
			</div>
			@php
				$previousCategory = '';
			@endphp
			@foreach ($configs as $idx => $config)
				@if($config->cat !== $previousCategory)
				<div class="setting_category text-xl px-1 w-full mt-5 pt-3 font-bold text-white">
					<p>{{ $config->cat }}</p>
				</div>
				@php
				$previousCategory = $config->cat;
				@endphp
				@endif
				<div class="setting_line my-0.5">
					<p class="break-words w-full" wire:key="config-{{ $config->id }}">
						<span class="inline-block text pt-2 pb-0 px-1 w-80 text-white">{{ $config->key }}</span>
						<x-forms.inputs.text class="w-1/2" wire:model="configs.{{ $idx }}.value" />
						@if($config->description !== '')
						<span class="text" class="w-full block -mt-1 text-neutral-500 pb-1 pt-0">{{ $config->description }}</span>
						@endif
					</p>
				</div>
			@endforeach
			<x-forms.buttons.danger class="w-full mt-7 mb-8 rounded-md" wire:click="openConfirmSave">{{ __("lychee.SETTINGS_ADVANCED_SAVE") }}</x-forms.buttons.danger>
		</div>
		<x-footer />
	</div>
</div>