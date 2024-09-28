<template>
	<Drawer v-model:visible="left_menu_open">
		<Menu :model="items" v-if="initData" class="!border-none">
			<template #item="{ item, props }">
				<template v-if="item.access">
					<router-link v-if="item.route" v-slot="{ href, navigate }" :to="item.route" custom>
						<a v-ripple :href="href" v-bind="props.action" @click="navigate">
							<MiniIcon :icon="item.icon" :class="'w-3 h-3'" />
							<span class="ml-2">{{ $t(item.label) }}</span>
						</a>
					</router-link>
					<a v-if="item.url" v-ripple :href="item.url" :target="item.target" v-bind="props.action">
						<MiniIcon :icon="item.icon" :class="'w-3 h-3'" />
						<span class="ml-2">{{ $t(item.label) }}</span>
					</a>
					<a v-if="!item.route && !item.url" v-ripple v-bind="props.action">
						<MiniIcon :icon="item.icon" :class="'w-3 h-3'" />
						<span class="ml-2">{{ $t(item.label) }}</span>
					</a>
				</template>
			</template>
		</Menu>
		<AboutLychee v-model:visible="openLycheeAbout" />
	</Drawer>
</template>
<script setup lang="ts">
import { Ref, ref } from "vue";
import { storeToRefs } from "pinia";
import Drawer from "primevue/drawer";
import Menu from "primevue/menu";
import MiniIcon from "@/components/icons/MiniIcon.vue";
import AboutLychee from "@/components/modals/AboutLychee.vue";
import AuthService from "@/services/auth-service";
import InitService from "@/services/init-service";
import { useAuthStore } from "@/stores/Auth";
import { useLycheeStateStore } from "@/stores/LycheeState";
import AlbumService from "@/services/album-service";

type MenyType = {
	label: string;
	icon: string;
	route?: string;
	url?: string;
	target?: string;
	access: boolean;
	command?: () => void;
};

const initData = ref(undefined) as Ref<undefined | App.Http.Resources.Rights.GlobalRightsResource>;
const openLycheeAbout = ref(false);
const items = ref([] as MenyType[]);
const logsEnabled = ref(true);

const authStore = useAuthStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();

const { left_menu_open, clockwork_url } = storeToRefs(lycheeStore);

lycheeStore.$subscribe((_mutation, state) => {
	authStore.getUser().then(
		(user) => {
			if (user.id) {
				load();
			}
		},
		(error) => {
			console.error(error);
		},
	);
});

load();

function load() {
	InitService.fetchGlobalRights()
		.then((data) => {
			initData.value = data.data;
			loadMenu();
		})
		.catch((error) => {
			console.error(error);
		});
}

function logout() {
	AuthService.logout().then(() => {
		lycheeStore.left_menu_open = false;
		initData.value = undefined;
		authStore.setUser(null);
		AlbumService.clearCache();
		window.location.href = "/gallery";
	});
}

function loadMenu() {
	if (!initData.value) {
		return;
	}

	items.value = [
		{
			label: "lychee.SETTINGS",
			icon: "cog",
			route: "/settings",
			access: initData.value.settings.can_edit ?? false,
		},
		{
			label: "lychee.PROFILE",
			icon: "person",
			route: "/profile",
			access: initData.value.user.can_edit ?? false,
		},
		{
			label: "lychee.USERS",
			icon: "people",
			route: "/users",
			access: initData.value.user_management.can_edit ?? false,
		},
		{
			label: "lychee.U2F",
			icon: "key",
			route: "/profile",
			access: initData.value.user.can_edit ?? false,
		},
		{
			label: "lychee.SHARING",
			icon: "cloud",
			route: "/sharing",
			access: initData.value.root_album.can_upload ?? false,
		},
		{
			label: "lychee.LOGS",
			icon: "excerpt",
			url: "/Logs",
			access: (initData.value.settings.can_see_logs ?? false) && logsEnabled.value,
		},
		{
			label: "lychee.LOGS",
			icon: "excerpt",
			access: (initData.value.settings.can_see_logs ?? false) && !logsEnabled.value,
		},
		{
			label: "lychee.JOBS",
			icon: "project",
			route: "/jobs",
			access: initData.value.settings.can_see_logs ?? false,
		},
		{
			label: "lychee.DIAGNOSTICS",
			icon: "wrench",
			route: "/diagnostics",
			access: initData.value.settings.can_see_diagnostics ?? false,
		},
		{
			label: "maintenance.title",
			icon: "timer",
			route: "/maintenance",
			access: initData.value.settings.can_edit ?? false,
		},
		{
			label: "lychee.ABOUT_LYCHEE",
			icon: "info",
			access: true,
			command: () => (openLycheeAbout.value = true),
		},
		{
			label: "lychee.SIGN_OUT",
			icon: "account-logout",
			access: true,
			command: logout,
		},
		// {
		// 	label: "Statistics ✨",
		// 	icon: "account",
		// 	route: "/stats",
		// 	access: !authStore.user,
		// },
	];

	if (clockwork_url.value && initData.value.settings.can_access_dev_tools) {
		items.value.push({
			label: "Clockwork App",
			icon: "telescope",
			url: clockwork_url.value,
			access: initData.value.settings.can_edit ?? false,
		});
	}
}
</script>
