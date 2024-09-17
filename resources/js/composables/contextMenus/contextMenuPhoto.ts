import { computed, ref } from "vue";

// @if (!$is_starred)
// <x-context-menu.item wire:click='star' icon_class='hover:fill-yello-400' icon='star'>{{ __('lychee.STAR') }}</x-context-menu.item>
// @else
// <x-context-menu.item wire:click='unstar' icon='star'>{{ __('lychee.UNSTAR') }}</x-context-menu.item>
// @endif
// <x-context-menu.item wire:click='tag' icon='tag'>{{ __('lychee.TAG') }}</x-context-menu.item>
// @if($is_model_album)
// <x-context-menu.item wire:click='setAsCover' icon='folder-cover'>{{ __('lychee.SET_COVER') }}</x-context-menu.item>
// @endif
// @if($is_header === false)
// <x-context-menu.item wire:click='setAsHeader' icon='image'>{{ __('lychee.SET_HEADER') }}</x-context-menu.item>
// @elseif($is_header)
// <x-context-menu.item wire:click='setAsHeader' icon='x'>{{ __('lychee.REMOVE_HEADER') }}</x-context-menu.item>
// @endif
// <x-context-menu.separator />
// <x-context-menu.item wire:click='rename' icon='pencil'>{{ __('lychee.RENAME') }}</x-context-menu.item>
// <x-context-menu.item wire:click='copyTo' icon='layers'>{{ __('lychee.COPY_TO') }}</x-context-menu.item>
// <x-context-menu.item wire:click='move' icon='transfer'>{{ __('lychee.MOVE') }}</x-context-menu.item>
// <x-context-menu.item wire:click='delete' icon='trash'>{{ __('lychee.DELETE') }}</x-context-menu.item>
// <x-context-menu.item wire:click='download' icon='cloud-download'>{{ __('lychee.DOWNLOAD') }}</x-context-menu.item>

type Selectors = {
	getAlbumConfig: () => App.Http.Resources.GalleryConfigs.AlbumConfig;
	getAlbum: () =>
		| App.Http.Resources.Models.AlbumResource
		| App.Http.Resources.Models.TagAlbumResource
		| App.Http.Resources.Models.SmartAlbumResource;
	getSelectedPhotos: () => App.Http.Resources.Models.PhotoResource[];
};

type Callbacks = {
	star: () => void;
	unstar: () => void;
	setAsCover: () => void;
	setAsHeader: () => void;
	toggleTag: () => void;
	toggleRename: () => void;
	toggleCopyTo: () => void;
	toggleMove: () => void;
	toggleDelete: () => void;
	toggleDownload: () => void;
};

type MenuItem = {
	is_divider?: boolean;
	label?: string;
	icon?: string;
	callback?: () => void;
};

export function useContextMenuPhoto(selectors: Selectors, callbacks: Callbacks) {
	const photomenu = ref();
	const PhotoMenu = computed(() => {
		const menu = [] as MenuItem[];
		const photos = selectors.getSelectedPhotos();
		const album = selectors.getAlbum();

		const isStarred = selectors.getSelectedPhotos().reduce((acc, photo) => acc && photo.is_starred, true);

		if (isStarred) {
			menu.push({
				label: "lychee.UNSTAR",
				icon: "pi pi-star",
				callback: callbacks.unstar,
			});
		} else {
			menu.push({
				label: "lychee.STAR",
				icon: "pi pi-star",
				callback: callbacks.star,
			});
		}

		menu.push({
			label: "lychee.TAG",
			icon: "pi pi-tag",
			callback: callbacks.toggleTag,
		});

		const isSingle = photos.length === 1;
		if (isSingle && selectors.getAlbumConfig().is_model_album) {
			menu.push({
				label: "lychee.SET_COVER",
				icon: "pi pi-id-card",
				callback: callbacks.setAsCover,
			});
			// @ts-expect-error
			if (album?.header_id === photos[0].id) {
				menu.push({
					label: "lychee.REMOVE_HEADER",
					icon: "pi pi-image",
					callback: callbacks.setAsHeader,
				});
			} else {
				menu.push({
					label: "lychee.SET_HEADER",
					icon: "pi pi-image",
					callback: callbacks.setAsHeader,
				});
			}
		}

		menu.push({
			is_divider: true,
		});

		if (isSingle) {
			menu.push({
				label: "lychee.RENAME",
				icon: "pi pi-pencil",
				callback: callbacks.toggleRename,
			});
		}

		menu.push(
			...[
				{
					label: "lychee.COPY_TO",
					icon: "pi pi-copy",
					callback: callbacks.toggleCopyTo,
				},
				{
					label: "lychee.MOVE",
					icon: "pi pi-arrow-right-arrow-left",
					callback: callbacks.toggleMove,
				},
				{
					label: "lychee.DELETE",
					icon: "pi pi-trash",
					callback: callbacks.toggleDelete,
				},
				{
					label: "lychee.DOWNLOAD",
					icon: "pi pi-cloud-download",
					callback: callbacks.toggleDownload,
				},
			],
		);
		return menu;
	});

	return {
		photomenu,
		PhotoMenu,
	};
}
