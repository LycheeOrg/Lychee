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
import { useDarkMode } from "@/v8/composables/useDarkMode";
import { useLanguageSwitcher } from "@/v8/composables/useLanguageSwitcher";
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
const { items: menuItems, profileItems, initData } = useLeftMenu(lycheeStore, leftMenuState, userStore, favouritesStore, route);
const adminTiles = useAdminTiles(lycheeStore, leftMenuState);
const { isDark, toggle: toggleDarkMode, toggleGlobal: toggleDarkModeGlobal } = useDarkMode();

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

	return [themeItem, ...fromAdmin];
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
