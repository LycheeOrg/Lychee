<template>
	<Drawer v-model:visible="left_menu_open" :pt:content:class="'flex flex-col justify-start gap-10'">
		<template #header>
			<div v-if="user?.id === null" class="flex items-center gap-2 text-muted-color hover:text-primary-400 w-full">
				<Button class="border-none w-full text-left justify-start" severity="secondary" text @click="togglableStore.toggleLogin()">
					<i class="pi pi-sign-in text-lg mr-2" />
					{{ $t("left-menu.login") }}
				</Button>
			</div>
			<div v-else class="flex items-center gap-2 text-muted-color hover:text-primary-400">
				<router-link v-if="!isGallery" v-slot="{ href, navigate }" :to="{ name: 'gallery' }" custom>
					<a v-ripple :href="href" @click="navigate">
						<span class="text-lg font-bold pl-3">{{ $t("left-menu.back_to_gallery") }}</span>
					</a>
				</router-link>
			</div>
		</template>
		<Menu :model="items" v-if="initData" class="!border-none">
			<template #submenuheader="{ item }">
				<span class="text-primary-emphasis font-bold" :class="item.access !== false ? '' : 'hidden'">
					<!-- @vue-ignore -->
					{{ $t(item.label) }}
				</span>
			</template>
			<template #item="{ item, props }">
				<template v-if="item.access">
					<router-link v-if="item.route" v-slot="{ href, navigate }" :to="item.route" custom>
						<a v-ripple :href="href" v-bind="props.action" @click="navigate">
							<PiMiniIcon :icon="item.icon" :key="item.icon" />
							<span class="ml-2">
								<!-- @vue-ignore -->
								{{ $t(item.label) }}
							</span>
							<SETag v-if="item.seTag" />
						</a>
					</router-link>
					<a v-if="item.url" v-ripple :href="item.url" :target="item.target" v-bind="props.action">
						<PiMiniIcon :icon="item.icon" :key="item.icon" />
						<span class="ml-2">
							<!-- @vue-ignore -->
							{{ $t(item.label) }}
						</span>
						<SETag v-if="item.seTag" />
					</a>
					<a v-if="!item.route && !item.url" v-ripple v-bind="props.action">
						<PiMiniIcon :icon="item.icon" :key="item.icon" />
						<span class="ml-2">
							<!-- @vue-ignore -->
							{{ $t(item.label) }}
						</span>
						<SETag v-if="item.seTag" />
					</a>
				</template>
			</template>
		</Menu>
		<AboutLychee v-model:visible="openLycheeAbout" />
		<div class="mt-auto" v-if="user?.id !== null">
			<Menu :model="profileItems" v-if="initData" class="!border-none">
				<template #item="{ item, props }">
					<router-link v-if="item.route" v-slot="{ href, navigate }" :to="item.route" custom>
						<a v-ripple :href="href" v-bind="props.action" @click="navigate">
							<MiniIcon :icon="item.icon ?? ''" :class="'w-3 h-3'" />
							<span class="ml-2">
								<!-- @vue-ignore -->
								{{ $t(item.label) }}
							</span>
							<SETag v-if="item.seTag" />
						</a>
					</router-link>
					<a v-if="item.url" v-ripple :href="item.url" :target="item.target" v-bind="props.action">
						<MiniIcon :icon="item.icon ?? ''" :class="'w-3 h-3'" />
						<span class="ml-2">
							<!-- @vue-ignore -->
							{{ $t(item.label) }}
						</span>
						<SETag v-if="item.seTag" />
					</a>
					<a v-if="!item.route && !item.url" v-ripple v-bind="props.action">
						<MiniIcon :icon="item.icon ?? ''" :class="'w-3 h-3'" />
						<span class="ml-2">
							<!-- @vue-ignore -->
							{{ $t(item.label) }}
						</span>
						<SETag v-if="item.seTag" />
					</a>
				</template>
			</Menu>
			<div class="border-t border-surface-700 pt-2 p-(--p-navigation-item-padding) flex justify-between pr-0">
				<div class="flex items-center gap-2">
					<MiniIcon icon="person" :class="'w-3 h-3'" />
					<div class="capitalize ml-2 text-muted-color">
						{{ authStore.user?.username }}
						<i class="pi pi-crown text-orange-400 text-xs" v-if="canSeeAdmin"></i>
					</div>
				</div>
				<Button text severity="secondary" class="cursor-pointer" @click="logout">
					<MiniIcon icon="account-logout" :class="'w-4 h-4'" />
				</Button>
			</div>
		</div>
	</Drawer>
</template>
<script setup lang="ts">
import { computed, watch } from "vue";
import Drawer from "primevue/drawer";
import Menu from "primevue/menu";
import MiniIcon from "@/components/icons/MiniIcon.vue";
import AboutLychee from "@/components/modals/AboutLychee.vue";
import AuthService from "@/services/auth-service";
import { useAuthStore } from "@/stores/Auth";
import { useLycheeStateStore } from "@/stores/LycheeState";
import AlbumService from "@/services/album-service";
import SETag from "@/components/icons/SETag.vue";
import Constants from "@/services/constants";
import { useRoute } from "vue-router";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import Button from "primevue/button";
import PiMiniIcon from "@/components/icons/PiMiniIcon.vue";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useLeftMenu } from "@/composables/contextMenus/leftMenu";
import { onMounted } from "vue";
import { useFavouriteStore } from "@/stores/FavouriteState";

const leftMenuState = useLeftMenuStateStore();
const route = useRoute();
const authStore = useAuthStore();
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const favouritesStore = useFavouriteStore();

const { user, left_menu_open, initData, openLycheeAbout, canSeeAdmin, load, items, profileItems } = useLeftMenu(
	lycheeStore,
	leftMenuState,
	authStore,
	favouritesStore,
	route,
);

function logout() {
	AuthService.logout().then(() => {
		left_menu_open.value = false;
		initData.value = undefined;
		authStore.setUser(null);
		AlbumService.clearCache();
		window.location.href = Constants.BASE_URL + "/home";
	});
}

const isGallery = computed(() => {
	return route.name === "gallery";
});

onMounted(() => {
	lycheeStore.init();
	authStore.getUser();
	load();
});

watch(
	() => user.value,
	(newValue, oldValue) => {
		if (newValue === null) {
			initData.value = undefined;
		} else if (newValue.id !== oldValue?.id) {
			load();
		}
	},
);
</script>
