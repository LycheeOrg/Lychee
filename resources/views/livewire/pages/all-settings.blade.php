<x-view.content :mode="$mode" :title="__('lychee.SETTINGS')">
	<div id="lychee_view_content" class="vflex-item-stretch contentZoomIn">
		<div class="settings_view">
			<div id="fullSettings">
				<div class="setting_line">
					<p class="warning">
						{{ __("lychee.SETTINGS_ADVANCED_WARNING_EXPL") }}
					</p>
				</div>
				@php
					$previousCategory = '';
				@endphp
				@foreach ($configs as $idx => $config)
					@if($config->cat !== $previousCategory)
					<div class="setting_category">
						<p>{{ $config->cat }}</p>
					</div>
					@php
					$previousCategory = $config->cat;
					@endphp
					@endif
					<div class="setting_line">
						<p wire:key="config-{{ $config->id }}">
							<span class="text">{{ $config->key }}</span>
							<input class="text" type="text" wire:model="configs.{{ $idx }}.value">
							@if($config->description !== '')
							<span class="text" style="color:darkGray; padding-bottom: 1em; padding-top:0">{{ $config->description }}</span>
							@endif
						</p>
					</div>
				@endforeach
				<a class="basicModal__button basicModal__button_SAVE" wire:click="openConfirmSave">{{ __("lychee.SETTINGS_ADVANCED_SAVE") }}</a>
			</div>
		</div>
	</div>
</x-view.content>