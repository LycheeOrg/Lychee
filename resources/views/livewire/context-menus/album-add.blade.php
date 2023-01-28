<div class="basicContext" id="addMenu" style="left: 1173px; top: 28px; transform-origin: 154px 0px; opacity: 1;">
	<table>
		<tbody>

			<tr class="basicContext__item ">
				<td class="basicContext__data" data-num="0"><x-icons.iconic icon="image" />{{ __('lychee.UPLOAD_PHOTO') }}</td>
			</tr>

			<tr class="basicContext__item basicContext__item--separator"></tr>

			<tr class="basicContext__item ">
				<td class="basicContext__data" data-num="2"><x-icons.iconic icon="link-intact" />{{ __('lychee.IMPORT_LINK') }}</td>
			</tr>
			@can(AlbumPolicy::CAN_IMPORT_FROM_SERVER, [App\Contracts\Models\AbstractAlbum::class])
			@if(Configs::getValueAsString('dropbox_key') !== '')
			<tr class="basicContext__item ">
				<td class="basicContext__data" data-num="2"><x-icons.iconic icon="dropbox" class="ionicons" />{{ __('lychee.IMPORT_DROPBOX') }}</td>
			</tr>
			@endif
			<tr class="basicContext__item ">
				<td class="basicContext__data" data-num="3"><x-icons.iconic icon="terminal" />{{ __('lychee.IMPORT_SERVER') }}</td>
			</tr>
			@endcan

			<tr class="basicContext__item basicContext__item--separator"></tr>

			<tr wire:click='openAlbumCreateModal' class="basicContext__item ">
				<td class="basicContext__data" data-num="5"><x-icons.iconic icon="folder" />{{ __('lychee.NEW_ALBUM') }}</td>
			</tr>

			<tr wire:click='openTagAlbumCreateModal' class="basicContext__item ">
				<td class="basicContext__data" data-num="6"><x-icons.iconic icon="tags" />{{ __('lychee.NEW_TAG_ALBUM') }}</td>
			</tr>

		</tbody>
	</table>
</div>