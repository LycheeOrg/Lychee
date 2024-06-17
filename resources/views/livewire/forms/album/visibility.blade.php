<div class="text-text-main-200 text-sm p-4 xl:px-9 sm:min-w-[32rem]">
	<form>
		<div class="h-12 mb-4">
			<label for="pp_dialog_public_check" class="font-bold">{{ __('lychee.ALBUM_PUBLIC') }}</label>
			<x-forms.toggle id="pp_dialog_public_check" wire:model.live="is_public" />
			<p class="my-1.5">{{ __('lychee.ALBUM_PUBLIC_EXPL') }}</p>
		</div>
		@if($is_base_album)
		<div @class(['relative h-12 my-4 pl-9 transition-color duration-300', 
			'text-text-main-400'=> !$is_public,
			'text-text-main-200' => $is_public ])>
			<label class="font-bold block " for="pp_dialog_full_check">{{ __('lychee.ALBUM_FULL') }}</label>
			<x-forms.defaulttickbox id="pp_dialog_full_check" wire:model.live='grants_full_photo_access' :disabled="!$is_public" />
			<p class="my-1.5">{{ __('lychee.ALBUM_FULL_EXPL') }}</p>
		</div>
		<div @class(['relative h-12 my-4 pl-9 transition-color duration-300', 
			'text-text-main-400'=> !$is_public,
			'text-text-main-200' => $is_public ])>
			<label class="font-bold block " for="pp_dialog_link_check">{{ __('lychee.ALBUM_HIDDEN') }}</label>
			<x-forms.defaulttickbox id="pp_dialog_link_check" wire:model.live='is_link_required' :disabled="!$is_public" />
			<p class="my-1.5">{{ __('lychee.ALBUM_HIDDEN_EXPL') }}</p>
		</div>
		<div @class(['relative h-12 my-4 pl-9 transition-color duration-300', 
			'text-text-main-400'=> !$is_public,
			'text-text-main-200' => $is_public ])>
			<label class="font-bold block " for="pp_dialog_downloadable_check">{{ __('lychee.ALBUM_DOWNLOADABLE') }}</label>
			<x-forms.defaulttickbox id="pp_dialog_downloadable_check" wire:model.live='grants_download' :disabled="!$is_public" />
			<p class="my-1.5">{{ __('lychee.ALBUM_DOWNLOADABLE_EXPL') }}</p>
		</div>
		<div @class(['relative h-12 my-4 pl-9 transition-color duration-300', 
			'text-text-main-400'=> !$is_public,
			'text-text-main-200' => $is_public ])>
			<label class="font-bold block " for="pp_dialog_password_check">{{ __('lychee.ALBUM_PASSWORD_PROT') }}</label>
			<x-forms.defaulttickbox id="pp_dialog_password_check" wire:model.live='is_password_required' :disabled="!$is_public" />
			<p class="my-1.5">{{ __('lychee.ALBUM_PASSWORD_PROT_EXPL') }}</p>
			<div @class(["hidden" => !$is_password_required])>
				<x-forms.inputs.text wire:model.live.debounce.500ms='password' placeholder="{{ __('lychee.ALBUM_PASSWORD') }}" />
			</div>
		</div>
		@endif
	</form>
	@if($is_base_album)
	<hr class="block my-6 w-full border-t border-solid border-black/30">
	<form>
		<div class="relative h-12 my-4 transition-color duration-300">
			<label for="pp_dialog_nsfw_check" class="font-bold">{{ __('lychee.ALBUM_NSFW') }}</label>
			<x-forms.toggle id="pp_dialog_nsfw_check" wire:model.live="is_nsfw" />
			<p class="my-1.5">{{ __('lychee.ALBUM_NSFW_EXPL') }}</p>
		</div>
	</form>
	@endif
</div>
