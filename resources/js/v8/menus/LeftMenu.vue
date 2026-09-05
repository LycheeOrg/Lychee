<template>
	<USlideover
		v-model:open="left_menu_open"
		:side="isLTR() ? 'left' : 'right'"
		:ui="{
			content: 'max-w-xs',
		}"
	>
		<template #header>
			<div v-if="user?.id === null">
				<RouterLink :to="{ name: 'login' }" class="flex items-center gap-2 text-muted hover:text-primary">
					<PiMiniIcon icon="lucide:log-in" class="w-4 h-4" />
					{{ $t("left-menu.login") }}
				</RouterLink>
			</div>
			<div v-else>
				<!-- Shown on every route, gallery included. The route watcher below cannot close the
				     menu when the link points at the page we are already on - `fullPath` never
				     changes - so close it here explicitly. -->
				<RouterLink :to="{ name: 'gallery' }" class="text-lg font-bold text-muted hover:text-primary" @click="left_menu_open = false">
					{{ $t("left-menu.back_to_gallery") }}
				</RouterLink>
			</div>
		</template>
		<template #body>
			<UNavigationMenu v-if="initData" orientation="vertical" :items="items" :dir="dir">
				<template #item-leading="{ item }">
					<PiMiniIcon :icon="item.icon" class="w-3 h-3" />
				</template>
				<template #item-trailing="{ item }">
					<UBadge v-if="item.count" size="sm" color="primary">{{ item.count }}</UBadge>
					<SETag v-if="item.seTag" />
				</template>
			</UNavigationMenu>
			<template v-if="!use_admin_dashboard">
				<div class="mt-4 px-2.5 text-lg text-toned font-bold">{{ $t("left-menu.admin") }}</div>
				<UNavigationMenu v-if="initData" orientation="vertical" :items="adminItems" :dir="dir">
					<template #item-leading="{ item }">
						<PiMiniIcon :icon="item.icon" class="w-3 h-3" />
					</template>
					<template #item-trailing="{ item }">
						<UBadge v-if="item.count" size="sm" color="primary">{{ item.count }}</UBadge>
						<SETag v-if="item.seTag" />
					</template>
				</UNavigationMenu>
			</template>
			<template v-if="!is_white_label_enabled">
				<div class="mt-4 px-2.5 text-lg text-toned font-bold">Lychee</div>
				<UNavigationMenu orientation="vertical" :items="lycheeItems" :dir="dir">
					<template #item-leading="{ item }">
						<PiMiniIcon :icon="item.icon" class="w-3 h-3" />
					</template>
				</UNavigationMenu>
			</template>
		</template>
		<template #footer>
			<div v-if="user?.id !== null" class="w-full">
				<UNavigationMenu orientation="vertical" :items="profileSections" :dir="dir">
					<template #item-leading="{ item }">
						<PiMiniIcon :icon="item.icon" class="w-3 h-3" />
					</template>
					<template #item-trailing="{ item }">
						<SETag v-if="item.seTag" />
					</template>
				</UNavigationMenu>
				<div class="border-t border-default pt-2 flex justify-between items-center px-2.5">
					<div class="flex items-center gap-2">
						<PiMiniIcon icon="person" class="w-3 h-3" />
						<div class="capitalize ml-2 text-muted">
							{{ userStore.user?.username }}
							<PiMiniIcon v-if="canSeeAdmin" icon="lucide:crown" class="w-4 h-4 text-orange-400 inline-block" />
						</div>
					</div>
					<UButton variant="ghost" color="neutral" class="cursor-pointer" @click="logout">
						<PiMiniIcon icon="account-logout" class="w-4 h-4" />
					</UButton>
				</div>
			</div>
		</template>
	</USlideover>
	<AboutLychee v-model:open="openLycheeAbout" />
</template>
<script setup lang="ts">
import { computed, watch, onMounted } from "vue";
import PiMiniIcon from "@/v8/components/icons/PiMiniIcon.vue";
import SETag from "@/v8/components/icons/SETag.vue";
import AboutLychee from "@/v8/components/modals/AboutLychee.vue";
import AuthService from "@/services/auth-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import AlbumService from "@/services/album-service";
import Constants from "@/services/constants";
import { useRoute } from "vue-router";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useLeftMenu, type LeftMenuItem } from "@/v8/composables/contextMenus/leftMenu";
import { useFavouriteStore } from "@/stores/FavouriteState";
import { useLtRorRtL } from "@/utils/Helpers";
import { useUserStore } from "@/stores/UserState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotoStore } from "@/stores/PhotoState";
import { useAlbumListStore } from "@/stores/AlbumListState";
import { trans } from "laravel-vue-i18n";
import { storeToRefs } from "pinia";

const leftMenuState = useLeftMenuStateStore();
const route = useRoute();
const userStore = useUserStore();
const photosStore = usePhotosStore();
const albumsStore = useAlbumsStore();
const albumStore = useAlbumStore();
const photoStore = usePhotoStore();
const albumListStore = useAlbumListStore();

const lycheeStore = useLycheeStateStore();
const { is_white_label_enabled, use_admin_dashboard } = storeToRefs(lycheeStore);
const favouritesStore = useFavouriteStore();
const { isLTR } = useLtRorRtL();

const dir = computed(() => (isLTR() ? "ltr" : "rtl"));

const { user, left_menu_open, initData, canSeeAdmin, load, items, adminItems, profileItems, openLycheeAbout } = useLeftMenu(
	lycheeStore,
	leftMenuState,
	userStore,
	favouritesStore,
	route,
);

// Hardcoded (not user/permission-driven data from the API): the app's own "about" links.
const lycheeItems = computed<LeftMenuItem[]>(() => {
	const entries: (LeftMenuItem & { access: boolean })[] = [
		{
			label: trans("left-menu.about"),
			icon: "info",
			access: true,
			onSelect: () => (openLycheeAbout.value = true),
		},
		{
			label: trans("left-menu.changelog"),
			icon: "copywriting",
			access: true,
			to: "/changelogs",
		},
		{
			label: trans("left-menu.api"),
			icon: "book",
			access: initData.value?.settings.can_edit ?? false,
			to: Constants.BASE_URL + "/docs/api",
		},
		{
			label: trans("left-menu.source_code"),
			icon: "code",
			access: user.value?.id === null || lycheeStore.is_se_info_hidden === false,
			to: "https://github.com/LycheeOrg/Lychee",
		},
		{
			label: trans("left-menu.support"),
			icon: "heart",
			access: lycheeStore.is_se_info_hidden === false,
			to: "https://lycheeorg.dev/get-supporter-edition/",
		},
	];
	return entries.filter((entry) => entry.access);
});

const profileSections = computed(() => profileItems.value.map((item) => [item]));

function logout() {
	AuthService.logout().then(() => {
		left_menu_open.value = false;
		initData.value = undefined;
		photoStore.reset();
		photosStore.reset();
		albumsStore.reset();
		albumStore.reset();
		userStore.setUser(undefined);
		AlbumService.clearCache();
		albumListStore.invalidate();
		window.location.href = Constants.BASE_URL + "/home";
	});
}

onMounted(() => {
	Promise.allSettled([lycheeStore.load(), userStore.load(), load()]);
});

// Fold the menu as soon as one of its entries navigates somewhere,
// otherwise the slideover stays open on top of the freshly loaded page.
watch(
	() => route.fullPath,
	() => {
		left_menu_open.value = false;
	},
);

// `userStore.refresh()` (called by every gallery page's own mount bootstrap -
// Albums.vue/Album.vue/Search.vue/...) synchronously resets `user.value` to
// `undefined` while it re-fetches - a transient loading sentinel, not a
// logout (a guest has `id: null`, never `undefined`). `lastKnownUserId` lets
// the watcher below see straight through that blip instead of treating it as
// a logout: reacting to it here (wiping `initData` and then never reloading
// it, since the next resolution's `oldValue` is that same forced `undefined`)
// left `initData` stuck blank on essentially every page mount.
let lastKnownUserId: number | null | undefined;

watch(
	() => user.value,
	(newValue) => {
		if (newValue === undefined) {
			return;
		}
		if (lastKnownUserId !== undefined && newValue.id !== lastKnownUserId) {
			// A real login/logout while already mounted - rights depend on identity.
			load();
		}
		lastKnownUserId = newValue.id;
	},
);
</script>
