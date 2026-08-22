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
import { useLeftMenu } from "@/v8/composables/contextMenus/leftMenu";
import { useAdminTiles } from "@/v8/composables/useAdminTiles";
import SearchService from "@/services/search-service";
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

const { items: menuItems, profileItems } = useLeftMenu(lycheeStore, leftMenuState, userStore, favouritesStore, route);
const adminTiles = useAdminTiles(lycheeStore, leftMenuState);

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

const isRemoteSearching = ref(false);
const remoteAlbums = ref<App.Http.Resources.Models.ThumbAlbumResource[]>([]);
const remotePhotos = ref<App.Http.Resources.Models.PhotoResource[]>([]);

const runRemoteSearch = useDebounceFn((term: string) => {
	// Guards against a stale response landing after the user kept typing (or cleared the box).
	if (term !== searchTerm.value || term.length < searchMinLength.value) {
		return;
	}
	isRemoteSearching.value = true;
	SearchService.search(ALL, term)
		.then((response) => {
			if (term !== searchTerm.value) {
				return;
			}
			remoteAlbums.value = response.data.albums;
			remotePhotos.value = response.data.photos;
		})
		.finally(() => {
			if (term === searchTerm.value) {
				isRemoteSearching.value = false;
			}
		});
}, 300);

watch(searchTerm, (term) => {
	if (term.trim().length < searchMinLength.value) {
		remoteAlbums.value = [];
		remotePhotos.value = [];
		isRemoteSearching.value = false;
		return;
	}
	runRemoteSearch(term);
});

watch(open, (isOpen) => {
	if (isOpen) {
		albumListStore.ensureLoaded();
		ensureSearchMinLength();
	} else {
		searchTerm.value = "";
		remoteAlbums.value = [];
		remotePhotos.value = [];
		isRemoteSearching.value = false;
	}
});

const navGroupItems = computed<SpotlightItem[]>(() => {
	const fromMenu: SpotlightItem[] = [...menuItems.value, ...profileItems.value]
		.filter((item) => item.label !== undefined)
		.map((item) => ({
			label: item.label as string,
			icon: item.icon,
			kind: "nav",
			onSelect: () => {
				close();
				if (item.onSelect) {
					item.onSelect(new Event("select"));
				} else if (item.to !== undefined) {
					router.push(item.to);
				}
			},
		}));

	const fromAdmin: SpotlightItem[] = adminTiles
		.filter((tile) => tile.visible.value)
		.map((tile) => ({
			label: trans(tile.label),
			icon: tile.icon,
			kind: "nav",
			onSelect: () => {
				close();
				if (tile.isExternal) {
					window.open(tile.to, "_blank");
				} else {
					router.push(tile.to);
				}
			},
		}));

	return [...fromMenu, ...fromAdmin];
});

const albumsGroupItems = computed<SpotlightItem[]>(() =>
	albumListStore.rows.map((row) => ({
		label: row.title,
		description: albumListStore.buildBreadcrumb(row.id),
		kind: "album",
		albumId: row.id,
		photoId: row.coverId,
		onSelect: () => {
			close();
			router.push({ name: "album", params: { albumId: row.id } });
		},
	})),
);

const remoteGroupItems = computed<SpotlightItem[]>(() => [
	...remoteAlbums.value.map((album): SpotlightItem => ({
		label: album.title,
		kind: "remote-album",
		thumbUrl: album.thumb?.thumb ?? null,
		onSelect: () => {
			close();
			router.push({ name: "album", params: { albumId: album.id } });
		},
	})),
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
		result.push({ id: "navigation", label: trans("search-palette.navigation"), items: navGroupItems.value as CommandPaletteItem[] });
	}

	if (albumsGroupItems.value.length > 0) {
		result.push({
			id: "albums",
			label: trans("search-palette.albums"),
			items: albumsGroupItems.value as CommandPaletteItem[],
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
