<div class="hflex-item-stretch vflex-container">
	<!-- toolbar -->
	<livewire:components.header :page_mode="$mode" :title="Lang::get('SETTINGS')" />

	<!--
		This container vertically shares space with the toolbar.
		It fills the remaining vertical space not taken by the toolbar.
		It contains the right sidebar and the workbench.
	-->
	<div class="vflex-item-stretch hflex-container">
		<div id="lychee_workbench_container" class="hflex-item-stretch">
			<!--
			The view container covers the entire workbench and
			contains the content and the footer.
			It provides a vertical scroll bar if the content
			grows too large.
			Opposed to the map view and image view the view container
			holds views which are scrollable (e.g. settings,
			album listings, etc.)
			-->
			<div id="lychee_view_container" class="vflex-container">
				<!--
				Content
				Vertically shares space with the footer.
				The minimum height is set such the footer is positioned
				at the bottom even if the content is smaller.
				-->
				<div id="lychee_view_content" class="vflex-item-stretch contentZoomIn">
					<div class="settings_view">
						<div id="fullSettings">
							<div class="setting_line">
								<p class="warning">
									{{ Lang::get("SETTINGS_ADVANCED_WARNING_EXPL") }}
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
							<a class="basicModal__button basicModal__button_SAVE" wire:click="openConfirmSave">{{ Lang::get("SETTINGS_ADVANCED_SAVE") }}</a>
						</div>
					</div>
				</div>
				<livewire:components.footer />
			</div>
		</div>
		<livewire:components.base.modal />
	</div>
</div>