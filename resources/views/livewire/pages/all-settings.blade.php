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
						<input class="inline-block text w-1/2 pt-2 pb-1 px-1 -mt-2 bg-transparent text-white border-b border-solid border-b-neutral-400
						hover:border-b-teal-400
						focus:border-b-teal-400 focus-visible:outline-none
						" type="text" wire:model="configs.{{ $idx }}.value">
						@if($config->description !== '')
						<span class="text" class="w-full block -mt-1 text-neutral-500 pb-1 pt-0">{{ $config->description }}</span>
						@endif
					</p>
				</div>
			@endforeach
			<a class="basicModal__button basicModal__button_SAVE mt-7 mb-8 cursor-pointer transition-colors ease-in-out w-full inline-block text-center pt-3 pb-4 font-bold text-red-800 rounded-md hover:text-white hover:bg-red-800" wire:click="openConfirmSave">{{ __("lychee.SETTINGS_ADVANCED_SAVE") }}</a>
		</div>
		<x-footer />
	</div>
</div>