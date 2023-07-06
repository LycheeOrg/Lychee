<div class="w-full">
	<!-- toolbar -->
	<livewire:components.header
		:page_mode="App\Enum\Livewire\PageMode::USERS"
		:title="__('lychee.USERS')" />
	<div class="overflow-clip-auto">
		<div class="settings_view max-w-3xl text-neutral-400 text-sm mx-auto">
			<div class="users_view_line w-full flex mt-5">
				<p class="full w-full flex">
					<span class="inline-block text font-bold w-full py-2 px-1 mr-2 mt-2.5 text-white">{{ __('lychee.USERNAME') }}</span>
					<span class="inline-block text font-bold w-full py-2 px-1 mr-2 mt-2.5 text-white">{{ __('lychee.LOGIN_PASSWORD') }}</span>
					<span class="inline-block text_icon mt-2.5 w-12 mx-2" title="{{ __('lychee.ALLOW_UPLOADS') }}">
						<x-icons.iconic class="fill-white w-4 h-4" icon="data-transfer-upload" />
					</span>
					<span class="inline-block text_icon mt-2.5 w-12 mx-1" title="{{ __('lychee.ALLOW_USER_SELF_EDIT') }}">
						<x-icons.iconic class="fill-white w-4 h-4" icon="lock-unlocked" />
					</span>
				</p>
				<a class="inline-block invisible w-1/6 pt-3 pb-4 border-t border-t-dark-800">Save</a>
			</div>
			@foreach ($this->users as $user)
				<livewire:modules.users.user-line :user="$user" key="user-{{$user->id}}" />
			@endforeach

			<div class="users_view_line w-full flex my-5">
				<p class="full w-full flex">
					<input class="text w-full py-2 px-1 text-white border-b border-b-solid border-b-neutral-800 bg-transparent placeholder:text-neutral-500
					hover:border-b-sky-400 focus:border-b-sky-400 shadow shadow-white/5" wire:model="username" type="text" placeholder="{{ __('lychee.LOGIN_USERNAME') }}">
					<input class="text w-full py-2 px-1 text-white border-b border-b-solid border-b-neutral-800 bg-transparent placeholder:text-neutral-500
					hover:border-b-red-700 focus:border-b-red-700 shadow shadow-white/5" wire:model="password" type="text" placeholder="{{ __('lychee.LOGIN_PASSWORD') }}">
					<span class="choice inline-block w-12" title="{{ __('lychee.ALLOW_UPLOADS') }}">
						<label>
							<input type="checkbox" wire:model="may_upload" class="absolute m-0 opacity-0">
							<span class="checkbox checkbox inline-block w-3.5 h-4 mt-2.5 mx-2 bg-black/50 rounded-sm">
								<svg class="iconic fill-sky-500 opacity-0 p-0.5 w-full h-full">
									<use xlink:href="#check"></use>
								</svg></span>
						</label>
					</span>
					<span class="choice inline-block w-12" title="{{ __('lychee.ALLOW_USER_SELF_EDIT') }}">
						<label>
							<input type="checkbox" wire:model="may_edit_own_settings" class="absolute m-0 opacity-0">
							<span class="checkbox checkbox inline-block w-3.5 h-4 mt-2.5 mx-2 bg-black/50 rounded-sm">
								<svg class="iconic fill-sky-500 opacity-0 p-0.5 w-full h-full">
									<use xlink:href="#check"></use>
								</svg></span>
						</label>
					</span>
				</p>
				<a wire:click="create" class="inline-block w-1/6 cursor-pointer transition-colors ease-in-out text-center pt-3 pb-4
				font-bold text-green-600 rounded-md hover:text-white hover:bg-green-700">{{ __('lychee.CREATE') }}</a>
			</div>
		</div>
	</div>
</div>