<template>
	<UModal v-model:open="open" :dismissible="true" :ui="{ overlay: 'backdrop-blur-sm', content: 'max-w-xl' }">
		<template #content>
			<UCommandPalette
				v-model:search-term="searchTerm"
				:groups="groups"
				:loading="isRemoteSearching"
				:placeholder="trans('search-palette.placeholder')"
				:fuse="{ resultLimit: 8 }"
			>
				<template #item-leading="{ item }">
					<Thumb
						v-if="item.kind === 'album'"
						:album-id="item.albumId"
						:photo-id="item.photoId ?? null"
						type="thumb"
						class="size-8 rounded shrink-0 object-cover"
					/>
					<img
						v-else-if="(item.kind === 'remote-album' || item.kind === 'remote-photo') && item.thumbUrl"
						:src="item.thumbUrl"
						alt=""
						class="size-8 rounded shrink-0 object-cover"
					/>
					<PiMiniIcon v-else-if="item.icon" :icon="item.icon" class="size-4 shrink-0" />
				</template>
				<template #empty>
					<p class="text-center text-muted text-sm py-6">{{ trans("search-palette.empty") }}</p>
				</template>
			</UCommandPalette>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { trans } from "laravel-vue-i18n";
import { useDebounceFn } from "@vueuse/core";
import type { CommandPaletteGroup, CommandPaletteItem } from "@nuxt/ui";
import Thumb from "@/v8/components/thumbs/Thumb.vue";
import PiMiniIcon from "@/v8/components/icons/PiMiniIcon.vue";
import { useAlbumListStore } from "@/stores/AlbumListState";

import { useLycheeStateStore } from "@/stores/LycheeState";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useUserStore } from "@/stores/UserState";
import { useFavouriteStore } from "@/stores/FavouriteState";
import { useAlbumStore } from "@/stores/AlbumState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { usePhotosStore } from "@/stores/PhotosState";
import { usePhotoStore } from "@/stores/PhotoState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { useLeftMenu } from "@/v8/composables/contextMenus/leftMenu";
import { useAdminTiles } from "@/v8/composables/useAdminTiles";
import { useDarkMode } from "@/v8/composables/useDarkMode";
import { useLanguageSwitcher } from "@/v8/composables/useLanguageSwitcher";
import { useAppToast } from "@/v8/composables/useAppToast";
import SearchService from "@/services/search-service";
import FaceDetectionService from "@/services/face-detection-service";
import { ALL } from "@/config/constants";

type SpotlightItem = CommandPaletteItem & {
	kind: "nav" | "album" | "remote-album" | "remote-photo";
	albumId?: string;
	photoId?: string | null;
	thumbUrl?: string | null;
};

const router = useRouter();
const route = useRoute();

const open = ref(false);
const searchTerm = ref("");

const lycheeStore = useLycheeStateStore();
const leftMenuState = useLeftMenuStateStore();
const userStore = useUserStore();
const favouritesStore = useFavouriteStore();

const albumListStore = useAlbumListStore();
const { items: menuItems, profileItems, initData } = useLeftMenu(lycheeStore, leftMenuState, userStore, favouritesStore, route);
const adminTiles = useAdminTiles(lycheeStore, leftMenuState);
const { isDark, toggle: toggleDarkMode, toggleGlobal: toggleDarkModeGlobal } = useDarkMode();

const albumStore = useAlbumStore();
const albumsStore = useAlbumsStore();
const photosStore = usePhotosStore();
const photoStore = usePhotoStore();
const togglableStore = useTogglablesStateStore();
const toast = useAppToast();
const { toggleCreateAlbum, toggleCreateTagAlbum, toggleUpload, toggleShareAlbum, toggleApplyRenamer, toggleWatermarkConfirm } =
	useGalleryModals(togglableStore);

defineShortcuts({
	meta_k: () => {
		open.value = true;
	},
});

function close() {
	open.value = false;
}

// Cached across opens: `SearchService.init` is itself response-cached, but this keeps us
// from re-reading the response on every reopen once we already have the value.
const searchMinLength = ref(2);
let searchMinLengthRequested = false;

function ensureSearchMinLength() {
	if (searchMinLengthRequested) {
		return;
	}
	searchMinLengthRequested = true;
	SearchService.init(ALL).then((response) => {
		searchMinLength.value = response.data.search_minimum_length;
	});
}

// Only admins can change the (instance-wide) language config.
const canEditSettings = computed(() => initData.value?.settings.can_edit ?? false);
const { availableLanguages, setLanguage } = useLanguageSwitcher(canEditSettings);

const isRemoteSearching = ref(false);
const remotePhotos = ref<App.Http.Resources.Models.PhotoResource[]>([]);

// Identifies a search request rather than its text: re-searching the same term (e.g. "cat",
// then something else, then "cat" again) would make a plain term comparison mistake the first
// (stale) request's response for the second's, since both share the same `searchTerm.value`.
let remoteSearchGeneration = 0;

const runRemoteSearch = useDebounceFn((term: string, generation: number) => {
	// Guards against a stale response landing after the user kept typing (or cleared the box).
	if (generation !== remoteSearchGeneration || term.length < searchMinLength.value) {
		return;
	}
	isRemoteSearching.value = true;
	SearchService.search(ALL, term)
		.then((response) => {
			if (generation !== remoteSearchGeneration) {
				return;
			}
			remotePhotos.value = response.data.photos;
		})
		.finally(() => {
			if (generation === remoteSearchGeneration) {
				isRemoteSearching.value = false;
			}
		});
}, 300);

watch(searchTerm, (term) => {
	remoteSearchGeneration++;
	if (term.trim().length < searchMinLength.value) {
		remotePhotos.value = [];
		isRemoteSearching.value = false;
		return;
	}
	// Cleared here (not just once the new response lands) so the "remote" group - which sets
	// `ignoreFilter: true` in `groups` below - doesn't keep showing the previous term's results
	// while this one is still debouncing/in flight.
	remotePhotos.value = [];
	runRemoteSearch(term, remoteSearchGeneration);
});

watch(open, (isOpen) => {
	if (isOpen) {
		albumListStore.ensureLoaded();
		ensureSearchMinLength();
	} else {
		searchTerm.value = "";
		remotePhotos.value = [];
		isRemoteSearching.value = false;
	}
});

const navGroupItems = computed<SpotlightItem[]>(() =>
	[...menuItems.value, ...profileItems.value]
		// The "Admin panel" link only leads to a page of tiles; the spotlight lists those
		// tiles directly (see actionsGroupItems below), so the link itself would be redundant here.
		.filter((item) => item.label !== undefined && item.to !== "/admin")
		.map((item) => ({
			label: item.label as string,
			icon: item.icon,
			kind: "nav" as const,
			onSelect: () => {
				close();
				if (item.onSelect) {
					item.onSelect(new Event("select"));
				} else if (item.to !== undefined) {
					router.push(item.to);
				}
			},
		})),
);

// Mirrors AlbumHero.vue's own `needSizeVariantsWatermark` (duplicated there and in
// Dock.vue rather than shared, so matching that existing convention here too).
function needSizeVariantsWatermark(sizeVariants: App.Http.Resources.Models.SizeVariantsResouce): boolean {
	return (
		(sizeVariants.thumb && !sizeVariants.thumb.is_watermarked) ||
		(sizeVariants.thumb2x && !sizeVariants.thumb2x.is_watermarked) ||
		(sizeVariants.small && !sizeVariants.small.is_watermarked) ||
		(sizeVariants.small2x && !sizeVariants.small2x.is_watermarked) ||
		(sizeVariants.medium && !sizeVariants.medium.is_watermarked) ||
		(sizeVariants.medium2x && !sizeVariants.medium2x.is_watermarked) ||
		false
	);
}

// The dialogs these actions open only ever get mounted while the matching route is
// active (see Album.vue/Albums.vue), so gating visibility on the route is enough -
// no dialog relocation is needed.
const insideAlbum = computed(() => (route.name === "album" || route.name === "flow-album") && albumStore.isLoaded);
const atGalleryRoot = computed(() => route.name === "gallery");

const galleryActionItems = computed<SpotlightItem[]>(() => {
	const items: SpotlightItem[] = [];
	const album = albumStore.album;
	const albumTitle = insideAlbum.value ? album?.title : undefined;

	if (
		(atGalleryRoot.value && albumsStore.rootRights?.can_upload) ||
		(insideAlbum.value && albumStore.rights?.can_upload && albumStore.config?.is_model_album)
	) {
		items.push({
			label: trans("gallery.menus.new_album"),
			description: albumTitle,
			icon: "lucide:folder",
			kind: "nav",
			onSelect: () => {
				close();
				toggleCreateAlbum();
			},
		});
	}

	if (atGalleryRoot.value && albumsStore.rootRights?.can_upload) {
		items.push({
			label: trans("gallery.menus.new_tag_album"),
			icon: "lucide:tags",
			kind: "nav",
			onSelect: () => {
				close();
				toggleCreateTagAlbum();
			},
		});
	}

	// Restricted to model albums with no photo open: MoveDialog's photo-open binding in
	// Album.vue always targets the current photo, leaving no slot for an album move, and
	// there's no existing precedent for moving a tag/smart/person album.
	if (insideAlbum.value && !photoStore.isLoaded && albumStore.rights?.can_move && albumStore.config?.is_model_album && album !== undefined) {
		items.push({
			label: trans("gallery.menus.move"),
			description: albumTitle,
			icon: "lucide:folder",
			kind: "nav",
			onSelect: () => {
				close();
				togglableStore.move_album_override = { id: album.id, title: album.title } as App.Http.Resources.Models.ThumbAlbumResource;
				togglableStore.is_move_visible = true;
			},
		});
	}

	if (insideAlbum.value && albumStore.rights?.can_edit) {
		items.push({
			label: trans("gallery.hero.edit"),
			description: albumTitle,
			icon: "lucide:settings",
			kind: "nav",
			onSelect: () => {
				close();
				togglableStore.is_album_edit_open = true;
			},
		});
	}

	if (insideAlbum.value && albumStore.rights?.can_share) {
		items.push({
			label: trans("gallery.hero.share"),
			description: albumTitle,
			icon: "lucide:share-2",
			kind: "nav",
			onSelect: () => {
				close();
				toggleShareAlbum();
			},
		});
	}

	if ((atGalleryRoot.value && albumsStore.rootRights?.can_upload) || (insideAlbum.value && albumStore.rights?.can_upload)) {
		items.push({
			label: trans("gallery.menus.upload_photo"),
			description: albumTitle,
			icon: "lucide:upload",
			kind: "nav",
			onSelect: () => {
				close();
				toggleUpload();
			},
		});
	}

	if (
		insideAlbum.value &&
		albumStore.rights?.can_edit &&
		initData.value?.modules.is_watermarker_enabled &&
		photosStore.photos.some((p) => needSizeVariantsWatermark(p.size_variants))
	) {
		items.push({
			label: trans("gallery.hero.watermark"),
			description: albumTitle,
			icon: "lucide:barcode",
			kind: "nav",
			onSelect: () => {
				close();
				toggleWatermarkConfirm();
			},
		});
	}

	if (
		insideAlbum.value &&
		albumStore.rights?.can_edit &&
		lycheeStore.is_face_recognition_enabled &&
		photosStore.photos.length > 0 &&
		album !== undefined
	) {
		items.push({
			label: trans("people.scan_faces"),
			description: albumTitle,
			icon: "lucide:smile",
			kind: "nav",
			onSelect: () => {
				close();
				FaceDetectionService.scanAlbum(album.id)
					.then(() => {
						toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.scan_success"), life: 3000 });
					})
					.catch((e) => {
						toast.add({
							severity: "error",
							summary: trans("toasts.error"),
							detail: e.response?.data?.message || trans("toasts.error"),
							life: 3000,
						});
					});
			},
		});
	}

	if (insideAlbum.value && albumStore.rights?.can_edit && initData.value?.modules.is_mod_renamer_enabled) {
		items.push({
			label: trans("gallery.menus.apply_renamer"),
			description: albumTitle,
			icon: "lucide:pencil",
			kind: "nav",
			onSelect: () => {
				close();
				toggleApplyRenamer();
			},
		});
	}

	return items;
});

const actionsGroupItems = computed<SpotlightItem[]>(() => {
	const fromAdmin: SpotlightItem[] = adminTiles
		.filter((tile) => tile.visible.value)
		.map((tile) => ({
			label: trans(tile.label),
			icon: tile.icon,
			kind: "nav" as const,
			onSelect: () => {
				close();
				if (tile.isExternal) {
					window.open(tile.to, "_blank");
				} else {
					router.push(tile.to);
				}
			},
		}));

	// Admins persist the choice as the instance-wide default (see General.vue's own dark
	// mode setting); everyone else only gets a browser-local override.
	const themeItem: SpotlightItem = {
		label: isDark.value ? trans("search-palette.light_mode") : trans("search-palette.dark_mode"),
		icon: isDark.value ? "lucide:sun" : "lucide:moon",
		kind: "nav",
		onSelect: () => {
			close();
			if (initData.value?.settings.can_edit) {
				toggleDarkModeGlobal();
			} else {
				toggleDarkMode();
			}
		},
	};

	return [...galleryActionItems.value, themeItem, ...fromAdmin];
});

const languageGroupItems = computed<SpotlightItem[]>(() =>
	availableLanguages.value.map((code) => ({
		label: code,
		description: trans("search-palette.language"),
		icon: "lucide:languages",
		kind: "nav",
		onSelect: () => {
			close();
			setLanguage(code);
		},
	})),
);

const albumsGroupItems = computed<SpotlightItem[]>(() =>
	albumListStore.rows.map((row) => ({
		label: row.title,
		description: albumListStore.buildBreadcrumb(row.id),
		kind: "album" as const,
		albumId: row.id,
		photoId: row.coverId,
		onSelect: () => {
			close();
			router.push({ name: "album", params: { albumId: row.id } });
		},
	})),
);

const remoteGroupItems = computed<SpotlightItem[]>(() => [
	...remotePhotos.value.map((photo): SpotlightItem => ({
		label: photo.title,
		kind: "remote-photo",
		thumbUrl: photo.size_variants.thumb?.url ?? null,
		onSelect: () => {
			close();
			router.push({ name: "album", params: { albumId: photo.album_id ?? ALL, photoId: photo.id } });
		},
	})),
]);

// Grouped as plain CommandPaletteItem[] (rather than CommandPaletteGroup<SpotlightItem>[]):
// postFilter's parameter position makes the generic invariant, so a SpotlightItem-typed group
// isn't assignable to what UCommandPalette's template inference expects. The extra fields
// (kind/albumId/photoId/thumbUrl) still reach #item-leading via CommandPaletteItem's own
// `[key: string]: any` index signature.
const groups = computed<CommandPaletteGroup[]>(() => {
	const result: CommandPaletteGroup[] = [];

	if (navGroupItems.value.length > 0) {
		result.push({
			id: "navigation",
			label: trans("search-palette.navigation"),
			items: navGroupItems.value as CommandPaletteItem[],
			// `ignoreFilter` + a local postFilter (as "actions"/"language" already do) keeps this
			// small, fixed list out of the shared Fuse pool: `useFuse` forwards `fuse.resultLimit`
			// straight into Fuse's own `.search(pattern, { limit })`, which caps the *combined*
			// cross-group result set before any per-group split - with enough albums matching, that
			// silently starves out this group's own matches (e.g. typing "st" hiding "Statistics").
			ignoreFilter: true,
			postFilter: (term: string, items: CommandPaletteItem[]) => {
				if (!term) return items;
				const t = term.toLowerCase();
				return items.filter((item) => (item.label as string | undefined)?.toLowerCase().includes(t));
			},
		});
	}
	if (actionsGroupItems.value.length > 0) {
		result.push({
			id: "actions",
			label: trans("search-palette.actions"),
			items: actionsGroupItems.value as CommandPaletteItem[],
			ignoreFilter: true,
			postFilter: (term: string, items: CommandPaletteItem[]) => {
				if (!term) return items;
				const t = term.toLowerCase();
				return items.filter(
					(item) =>
						(item.label as string | undefined)?.toLowerCase().includes(t) ||
						(item.description as string | undefined)?.toLowerCase().includes(t),
				);
			},
		});
	}

	if (languageGroupItems.value.length > 0) {
		result.push({
			id: "language",
			label: trans("search-palette.language"),
			items: languageGroupItems.value as CommandPaletteItem[],
			ignoreFilter: true,
			postFilter: (term: string, items: CommandPaletteItem[]) => {
				if (!term) return [];
				const t = term.toLowerCase();
				return items.filter(
					(item) =>
						(item.label as string | undefined)?.toLowerCase().includes(t) ||
						(item.description as string | undefined)?.toLowerCase().includes(t),
				);
			},
		});
	}

	if (albumsGroupItems.value.length > 0) {
		result.push({
			id: "albums",
			label: trans("search-palette.albums"),
			items: albumsGroupItems.value as CommandPaletteItem[],
			// Stays on the shared Fuse pool (unlike "navigation"/"actions"/"language") so a large,
			// freeform album collection still gets fuzzy/typo-tolerant matching rather than plain
			// substring matching.
			// Only show once the user has actually typed something -- otherwise the whole album
			// tree would dump onto screen the instant the palette opens.
			postFilter: (term, items) => (term ? items : []),
		});
	}

	if (remoteGroupItems.value.length > 0) {
		result.push({
			id: "remote",
			label: trans("search-palette.results"),
			items: remoteGroupItems.value as CommandPaletteItem[],
			ignoreFilter: true,
		});
	}

	return result;
});
</script>
