<x-view.content :mode="$mode" :title="__('lychee.USERS')">
	<div id="lychee_view_content" class="vflex-item-stretch contentZoomIn">
		<div class="users_view">
			<div class="users_view_line">
				<p>
					<span class="text">{{ __('lychee.USERNAME') }}</span>
					<span class="text">{{ __('lychee.LOGIN_PASSWORD') }}</span>
					<span class="text_icon" title="{{ __('lychee.ALLOW_UPLOADS') }}">
						<x-icons.iconic icon="data-transfer-upload" />
					</span>
					<span class="text_icon" title="{{ __('lychee.ALLOW_USER_SELF_EDIT') }}">
						<x-icons.iconic icon="lock-unlocked" />
					</span>
				</p>
			</div>
			@foreach ($this->users as $user)
				<livewire:modules.users.user-line :user="$user" key="user-{{$user->id}}" />
			@endforeach

			<div class="users_view_line">
				<p>
					<input class="text" wire:model="username" type="text" placeholder="{{ __('lychee.LOGIN_USERNAME') }}">
					<input class="text" wire:model="password" type="text" placeholder="{{ __('lychee.LOGIN_PASSWORD') }}">
					<span class="choice" title="{{ __('lychee.ALLOW_UPLOADS') }}">
						<label>
							<input type="checkbox" wire:model="may_upload">
							<span class="checkbox"><svg class="iconic ">
									<use xlink:href="#check"></use>
								</svg></span>
						</label>
					</span>
					<span class="choice" title="{{ __('lychee.ALLOW_USER_SELF_EDIT') }}">
						<label>
							<input type="checkbox" wire:model="may_edit_own_settings">
							<span class="checkbox"><svg class="iconic ">
									<use xlink:href="#check"></use>
								</svg></span>
						</label>
					</span>
				</p>
				<a wire:click="create" class="basicModal__button basicModal__button_CREATE">{{ __('lychee.CREATE') }}</a>
			</div>
		</div>
	</div>
</x-view.content>