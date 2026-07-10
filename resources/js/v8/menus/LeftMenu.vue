<template>
	<USlideover v-model:open="left_menu_open" :side="isLTR() ? 'left' : 'right'">
		<template #header>
			<div v-if="user?.id === null">
				<RouterLink :to="{ name: 'login' }" class="flex items-center gap-2 text-muted hover:text-primary">
					<PiMiniIcon icon="pi pi-sign-in" class="w-4 h-4" />
					{{ $t("left-menu.login") }}
				</RouterLink>
			</div>
			<div v-else-if="!isGallery">
				<RouterLink :to="{ name: 'gallery' }" class="text-lg font-bold text-muted hover:text-primary">
					{{ $t("left-menu.back_to_gallery") }}
				</RouterLink>
			</div>
		</template>
		<template #body>
			<UNavigationMenu v-if="initData" orientation="vertical" :items="navSections">
				<template #item-leading="{ item }">
					<PiMiniIcon :icon="(item as LeftMenuItem).rawIcon" class="w-3 h-3" />
				</template>
				<template #item-trailing="{ item }">
					<UBadge v-if="(item as LeftMenuItem).count" size="sm" color="primary">{{ (item as LeftMenuItem).count }}</UBadge>
					<SETag v-if="(item as LeftMenuItem).seTag" />
				</template>
			</UNavigationMenu>
		</template>
		<template #footer>
			<div v-if="user?.id !== null" class="w-full">
				<UNavigationMenu orientation="vertical" :items="profileSections">
					<template #item-leading="{ item }">
						<PiMiniIcon :icon="(item as LeftMenuItem).rawIcon" class="w-3 h-3" />
					</template>
					<template #item-trailing="{ item }">
						<SETag v-if="(item as LeftMenuItem).seTag" />
					</template>
				</UNavigationMenu>
				<div class="border-t border-default pt-2 flex justify-between items-center pr-0">
					<div class="flex items-center gap-2">
						<PiMiniIcon icon="person" class="w-3 h-3" />
						<div class="capitalize ml-2 text-muted">
							{{ userStore.user?.username }}
							<PiMiniIcon v-if="canSeeAdmin" icon="pi pi-crown" class="w-6 h-6 text-orange-400 inline-block" />
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
import type { NavigationMenuItem } from "@nuxt/ui";
import PiMiniIcon from "@/v8/components/icons/PiMiniIcon.vue";
import SETag from "@/v8/components/icons/SETag.vue";
import AboutLychee from "@/v8/components/modals/AboutLychee.vue";
import AuthService from "@/services/auth-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import AlbumService from "@/services/album-service";
import Constants from "@/services/constants";
import { useRoute } from "vue-router";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useLeftMenu, type MenyType } from "@/composables/contextMenus/leftMenu";
import { useFavouriteStore } from "@/stores/FavouriteState";
import { useLtRorRtL } from "@/utils/Helpers";
import { useUserStore } from "@/stores/UserState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotoStore } from "@/stores/PhotoState";
import { trans } from "laravel-vue-i18n";

type LeftMenuItem = NavigationMenuItem & { rawIcon?: string; seTag?: boolean; count?: number };

const leftMenuState = useLeftMenuStateStore();
const route = useRoute();
const userStore = useUserStore();
const photosStore = usePhotosStore();
const albumsStore = useAlbumsStore();
const albumStore = useAlbumStore();
const photoStore = usePhotoStore();

const lycheeStore = useLycheeStateStore();
const favouritesStore = useFavouriteStore();
const { isLTR } = useLtRorRtL();

const { user, left_menu_open, initData, canSeeAdmin, load, items, profileItems, openLycheeAbout } = useLeftMenu(
	lycheeStore,
	leftMenuState,
	userStore,
	favouritesStore,
	route,
);

function toLeafItem(item: MenyType): LeftMenuItem {
	const leaf = item as Extract<MenyType, { icon: string }> & { num?: number };
	return {
		label: trans(leaf.label),
		rawIcon: leaf.icon,
		seTag: leaf.seTag,
		count: leaf.num && leaf.num > 0 ? leaf.num : undefined,
		to: leaf.route ?? leaf.url,
		target: leaf.target as LeftMenuItem["target"],
		onSelect: leaf.command,
	};
}

function toSections(menu: MenyType[]): LeftMenuItem[][] {
	return menu.map((item) => {
		if ("items" in item) {
			return [{ label: trans(item.label), type: "label" as const }, ...item.items.map(toLeafItem)];
		}
		return [toLeafItem(item)];
	});
}

const navSections = computed(() => toSections(items.value));
const profileSections = computed(() => toSections(profileItems.value));

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
		window.location.href = Constants.BASE_URL + "/home";
	});
}

const isGallery = computed(() => {
	return route.name === "gallery";
});

onMounted(() => {
	Promise.allSettled([lycheeStore.load(), userStore.load(), load()]);
});

watch(
	() => user.value,
	(newValue, oldValue) => {
		if (newValue === undefined) {
			initData.value = undefined;
		} else if (newValue.id !== oldValue?.id) {
			load();
		}
	},
);
</script>
