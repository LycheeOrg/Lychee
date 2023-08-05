<div class="w-full">
    <x-header.bar>
        <x-header.button wire:click="back" icon="chevron-left" />
        <x-header.title>{{ __('lychee.SETTINGS') }}</x-header.title>
    </x-header.bar>
	<div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)]">
		<div class="settings_view w-10/12 max-w-2xl text-neutral-400 text-sm mx-auto">
			<div class="pt-12">
				<p class="warning">
					{{ __("lychee.SETTINGS_ADVANCED_WARNING_EXPL") }}
				</p>
			</div>
			@php
				$previousCategory = '';
			@endphp
			@foreach ($form->configs as $idx => $config)
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
						<x-forms.inputs.text class="w-1/2"
							wire:model="form.values.{{ $idx }}"
							:has_error="$errors->has('form.values.' . $idx)"
							wire:dirty.class="text-yellow-500"
							/>
						@foreach ($errors->get('form.values.' . $idx) as $err)
						<span class="w-full block -mt-1 text-red-600 pb-1 pt-0">{{ $err }}</span>
						@endforeach
						@if($config->description !== '')
						<span class="w-full block -mt-1 text-neutral-500 pb-1 pt-0">{{ $config->description }}</span>
						@endif
					</p>
				</div>
			@endforeach
			<x-forms.buttons.danger
				@keydown.enter.window="$wire.openConfirmSave()"
				class="w-full mt-7 mb-8 rounded-md"
				wire:click="openConfirmSave">{{ __("lychee.SETTINGS_ADVANCED_SAVE") }}</x-forms.buttons.danger>
		</div>
		<x-footer />
	</div>
</div>