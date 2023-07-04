<div class="users_view_line w-full flex my-1">
	<p class="w-full flex">
		<input class="text w-full py-0 h-8 px-1 text-white border-b border-b-solid border-b-neutral-800 bg-transparent placeholder:text-neutral-500
		hover:border-b-sky-400 focus:border-b-sky-400 shadow shadow-white/5" wire:model="username" type="text" placeholder="{{ __('lychee.LOGIN_USERNAME') }}">
		<input class="text w-full py-0 h-8 px-1 text-white border-b border-b-solid border-b-neutral-800 bg-transparent placeholder:text-neutral-500
		hover:border-b-red-700 focus:border-b-red-700 shadow shadow-white/5" wire:model="password" type="text" placeholder="{{ __('lychee.LOGIN_PASSWORD') }}">
		<span class="choice inline-block" title="{{ __('lychee.ALLOW_UPLOADS') }}">
			<label>
				<input wire:model='may_upload' type="checkbox" class="absolute m-0 opacity-0">
				<span class="checkbox inline-block w-4 h-4 mt-2.5 mx-2 bg-black/50 rounded-sm">
					<x-icons.iconic class=" fill-sky-500 opacity-0 p-0.5 w-full h-full scale-0 mb-2" icon="check" />
				</span>
			</label>
		</span>
		<span class="choice inline-block" title="{{ __('lychee.ALLOW_USER_SELF_EDIT') }}">
			<label>
				<input wire:model='may_edit_own_settings' type="checkbox" class="absolute m-0 opacity-0">
				<span class="checkbox inline-block w-4 h-4 mt-2.5 mx-2 bg-black/50 rounded-sm">
					<x-icons.iconic class=" fill-sky-500 opacity-0 p-0.5 w-full h-full scale-0 mb-2" icon="check" />
				</span>
			</label>
		</span>
	</p>
	@if($this->hasChanged)
	<a wire:click='save' class="inline-block h-10 w-1/6 pt-2 pb-4 flex-shrink border-t border-t-dark-800
    cursor-pointer font-bold text-center transition-colors select-none text-sky-500
    hover:bg-sky-500 hover:text-white rounded">{{ __('lychee.SAVE') }}</a>
	@elseif($user->may_administrate !== true)
	<a wire:click='delete' class="inline-block h-10 w-1/6 pt-2 pb-4 flex-shrink border-t border-t-dark-800
    cursor-pointer font-bold text-center transition-colors select-none text-red-700
    hover:bg-red-700 hover:text-white rounded ">{{ __('lychee.DELETE') }}</a>
	@else 
	<a class="inline-block h-10 invisible w-1/6 pt-2 pb-4 border-t border-t-dark-800">Save</a>
	@endif
</div>