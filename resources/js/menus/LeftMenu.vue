<template>
	<Drawer v-model:visible="left_menu_open">
		<Menu :model="items" v-if="initData" class="!border-none">
			<template #submenulabel="{ item }">
				<span class="text-primary-emphasis font-bold" :class="item.access !== false ? '' : 'hidden'">{{ $t(item.label) }}</span>
			</template>
			<template #item="{ item, props }">
				<template v-if="item.access">
					<router-link v-if="item.route" v-slot="{ href, navigate }" :to="item.route" custom>
						<a v-ripple :href="href" v-bind="props.action" @click="navigate">
							<MiniIcon :icon="item.icon" :class="'w-3 h-3'" />
							<span class="ml-2">{{ $t(item.label) }}</span>
							<SETag v-if="item.seTag" />
						</a>
					</router-link>
					<a v-if="item.url" v-ripple :href="item.url" :target="item.target" v-bind="props.action">
						<MiniIcon :icon="item.icon" :class="'w-3 h-3'" />
						<span class="ml-2">{{ $t(item.label) }}</span>
						<SETag v-if="item.seTag" />
					</a>
					<a v-if="!item.route && !item.url" v-ripple v-bind="props.action">
						<MiniIcon :icon="item.icon" :class="'w-3 h-3'" />
						<span class="ml-2">{{ $t(item.label) }}</span>
						<SETag v-if="item.seTag" />
					</a>
				</template>
			</template>
		</Menu>
		<AboutLychee v-model:visible="openLycheeAbout" />
	</Drawer>
</template>
<script setup lang="ts">
import { computed, Ref, ref, watch } from "vue";
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
import SETag from "@/components/icons/SETag.vue";

type MenyType =
	| {
			label: string;
			icon: string;
			route?: string;
			url?: string;
			target?: string;
			access: boolean;
			seTag?: boolean;
			command?: () => void;
	  }
	| {
			label: string;
			items: MenyType[];
	  };

const initData = ref(undefined) as Ref<undefined | App.Http.Resources.Rights.GlobalRightsResource>;
const openLycheeAbout = ref(false);
const items = ref([] as MenyType[]);
const logsEnabled = ref(true);

const authStore = useAuthStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();

const { left_menu_open, clockwork_url, is_se_enabled, is_se_preview_enabled } = storeToRefs(lycheeStore);
const { user } = storeToRefs(authStore);
authStore.getUser();

watch(
	() => user.value,
	(newValue, oldValue) => {
		if (newValue === null) {
			initData.value = undefined;
		}
		else if (newValue.id !== oldValue?.id) {
			load();
		}
	},
);

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

const canSeeAdmin = computed(() => {
	return (
		initData.value?.settings.can_edit ||
		initData.value?.user_management.can_edit ||
		initData.value?.settings.can_see_diagnostics ||
		initData.value?.settings.can_see_logs ||
		false
	);
});

function loadMenu() {
	if (!initData.value) {
		return;
	}

	items.value = [
		{
			label: "Admin",
			access: canSeeAdmin.value,
			items: [
				{
					label: "lychee.SETTINGS",
					icon: "cog",
					route: "/settings",
					access: initData.value.settings.can_edit ?? false,
				},
				{
					label: "lychee.USERS",
					icon: "people",
					route: "/users",
					access: initData.value.user_management.can_edit ?? false,
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
			],
		},
		{
			label: "lychee.PROFILE",
			items: [
				{
					label: "User",
					icon: "person",
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
					label: "Statistics",
					icon: "bar-chart",
					route: "/statistics",
					access: is_se_enabled.value,
				},
				{
					label: "Statistics",
					icon: "bar-chart",
					route: "/statistics",
					access: is_se_preview_enabled.value,
					seTag: true,
				},
				{
					label: "lychee.SIGN_OUT",
					icon: "account-logout",
					access: true,
					command: logout,
				},
			],
		},

		{
			label: "lychee.ABOUT_LYCHEE",
			icon: "info",
			access: true,
			command: () => (openLycheeAbout.value = true),
		},
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
