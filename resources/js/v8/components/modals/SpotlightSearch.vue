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
						type="small"
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
import { useSpotlightNavItems } from "@/v8/composables/spotlight/useSpotlightNavItems";
import { useSpotlightGalleryActions } from "@/v8/composables/spotlight/useSpotlightGalleryActions";
import { useSpotlightSystemActions } from "@/v8/composables/spotlight/useSpotlightSystemActions";
import { useSpotlightLanguageItems } from "@/v8/composables/spotlight/useSpotlightLanguageItems";
import { useSpotlightAlbumItems } from "@/v8/composables/spotlight/useSpotlightAlbumItems";
import { useSpotlightRemoteSearch } from "@/v8/composables/spotlight/useSpotlightRemoteSearch";
import type { SpotlightItem } from "@/v8/composables/spotlight/types";

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
const { toggleCreateAlbum, toggleCreateTagAlbum, toggleUpload, toggleApplyRenamer, toggleWatermarkConfirm } = useGalleryModals(togglableStore);

defineShortcuts({
	meta_k: () => {
		open.value = true;
	},
});

function close() {
	open.value = false;
}

// Only admins can change the (instance-wide) language config.
const canEditSettings = computed(() => initData.value?.settings.can_edit ?? false);
const { availableLanguages, setLanguage } = useLanguageSwitcher(canEditSettings);

const { isRemoteSearching, remoteGroupItems, ensureSearchMinLength, reset: resetRemoteSearch } = useSpotlightRemoteSearch(searchTerm, router, close);

watch(open, (isOpen) => {
	if (isOpen) {
		albumListStore.ensureLoaded();
		ensureSearchMinLength();
	} else {
		searchTerm.value = "";
		resetRemoteSearch();
	}
});

const navGroupItems = useSpotlightNavItems(menuItems, profileItems, router, close);

const galleryActionItems = useSpotlightGalleryActions(
	route,
	albumStore,
	albumsStore,
	photosStore,
	photoStore,
	togglableStore,
	lycheeStore,
	toast,
	initData,
	{ toggleCreateAlbum, toggleCreateTagAlbum, toggleUpload, toggleApplyRenamer, toggleWatermarkConfirm },
	close,
);

const systemActionItems = useSpotlightSystemActions(adminTiles, router, isDark, initData, toggleDarkMode, toggleDarkModeGlobal, close);

const actionsGroupItems = computed<SpotlightItem[]>(() => [...galleryActionItems.value, ...systemActionItems.value]);

const languageGroupItems = useSpotlightLanguageItems(availableLanguages, setLanguage, close);

const albumsGroupItems = useSpotlightAlbumItems(albumListStore, router, close);

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
