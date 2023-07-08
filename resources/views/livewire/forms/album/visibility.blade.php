<div class="text-neutral-200 text-sm p-9 w-full min-w-fit">
	<form>
		<div class="input-group compact-no-indent">
			<label for="pp_dialog_public_check" class="font-bold">{{ __('lychee.ALBUM_PUBLIC') }}</label>
			<x-forms.toggle id="pp_dialog_public_check" wire:model="is_public" />
			<p class="my-1.5">{{ __('lychee.ALBUM_PUBLIC_EXPL') }}</p>
		</div>
		<div @class(['relative my-4 pl-9 transition-color duration-300', 
			'text-neutral-500'=> $is_public === false,
			'text-neutral-200' => $is_public === true ])>
			<label class="font-bold block " for="pp_dialog_full_check">{{ __('lychee.ALBUM_FULL') }}</label>
			<x-forms.defaulttickbox id="pp_dialog_full_check" wire:model='grants_full_photo_access' :disabled="$is_public !== true" />
			<p class="my-1.5">{{ __('lychee.ALBUM_FULL_EXPL') }}</p>
		</div>
		<div @class(['relative my-4 pl-9 transition-color duration-300', 
			'text-neutral-500'=> $is_public === false,
			'text-neutral-200' => $is_public === true ])>
			<label class="font-bold block " for="pp_dialog_link_check">{{ __('lychee.ALBUM_HIDDEN') }}</label>
			<x-forms.defaulttickbox id="pp_dialog_link_check" wire:model='is_link_required' :disabled="$is_public !== true" />
			<p class="my-1.5">{{ __('lychee.ALBUM_HIDDEN_EXPL') }}</p>
		</div>
		<div @class(['relative my-4 pl-9 transition-color duration-300', 
			'text-neutral-500'=> $is_public === false,
			'text-neutral-200' => $is_public === true ])>
			<label class="font-bold block " for="pp_dialog_downloadable_check">{{ __('lychee.ALBUM_DOWNLOADABLE') }}</label>
			<x-forms.defaulttickbox id="pp_dialog_downloadable_check" wire:model='grants_download' :disabled="$is_public !== true" />
			<p class="my-1.5">{{ __('lychee.ALBUM_DOWNLOADABLE_EXPL') }}</p>
		</div>
		<div @class(['relative my-4 pl-9 transition-color duration-300', 
			'text-neutral-500'=> $is_public === false,
			'text-neutral-200' => $is_public === true ])>
			<label class="font-bold block " for="pp_dialog_password_check">{{ __('lychee.ALBUM_PASSWORD_PROT') }}</label>
			<x-forms.defaulttickbox id="pp_dialog_password_check" wire:model='is_password_required' :disabled="$is_public !== true" />
			<p class="my-1.5">{{ __('lychee.ALBUM_PASSWORD_PROT_EXPL') }}</p>
			<div class="input-group stacked hidden">
				<input class="text" id="pp_dialog_password_input" name="password" type="text"
					placeholder="{{ __('lychee.ALBUM_PASSWORD') }}">
			</div>
		</div>
	</form>
	<hr class="block my-6 w-full border-t border-solid border-black/30">
	<form>
		<div class="input-group compact-no-indent">
			<label for="pp_dialog_nsfw_check" class="font-bold">{{ __('lychee.ALBUM_NSFW') }}</label>
			<x-forms.toggle id="pp_dialog_nsfw_check" wire:model="is_nsfw" />
			<p class="my-1.5">{{ __('lychee.ALBUM_NSFW_EXPL') }}</p>
		</div>
	</form>
</div>
