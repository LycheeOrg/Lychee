import Constants from "@/services/constants";
import InitService from "@/services/init-service";
import { AuthStore } from "@/stores/Auth";
import { FavouriteStore } from "@/stores/FavouriteState";
import { LeftMenuStateStore } from "@/stores/LeftMenuState";
import { LycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { computed, ref } from "vue";
import { RouteLocationNormalizedLoadedGeneric } from "vue-router";

export type MenyType =
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

export function useLeftMenu(
	lycheeStore: LycheeStateStore,
	LeftMenuStateStore: LeftMenuStateStore,
	authStore: AuthStore,
	favourites: FavouriteStore,
	route: RouteLocationNormalizedLoadedGeneric,
) {
	const { user } = storeToRefs(authStore);

	const { initData, left_menu_open } = storeToRefs(LeftMenuStateStore);
	const { clockwork_url, is_se_enabled, is_se_preview_enabled, is_se_info_hidden, is_favourite_enabled } = storeToRefs(lycheeStore);
	const openLycheeAbout = ref(false);
	const logsEnabled = ref(true);

	const canSeeAdmin = computed(() => {
		return (
			initData.value?.settings.can_edit ||
			initData.value?.user_management.can_edit ||
			initData.value?.settings.can_see_diagnostics ||
			initData.value?.settings.can_see_logs ||
			false
		);
	});

	function load() {
		InitService.fetchGlobalRights().then((data) => {
			initData.value = data.data;
		});
	}

	const items = computed<MenyType[]>(() => {
		if (!initData.value) {
			return [];
		}

		const baseMenu = [
			{
				label: "gallery.favourites",
				icon: "pi pi-heart",
				route: "/gallery/favourites",
				access: lycheeStore.is_favourite_enabled && (favourites.photos?.length ?? 0) > 0,
			},
			{
				label: "left-menu.frame",
				icon: "pi pi-desktop",
				route: "/frame",
				access: !(route.name as string).includes("frame") && (initData.value.modules.is_mod_frame_enabled ?? false),
			},
			{
				label: "left-menu.map",
				icon: "pi pi-map",
				access: initData.value.modules.is_map_enabled ?? false,
				route: "/map",
			},
			{
				label: "gallery.timeline.title",
				icon: "clock",
				route: "/timeline",
				access: !(route.name as string).includes("timeline") && lycheeStore.is_timeline_page_enabled,
			},
			{
				label: "left-menu.admin",
				access: canSeeAdmin.value,
				items: [
					{
						label: "settings.title",
						icon: "cog",
						route: "/settings",
						access: initData.value.settings.can_edit ?? false,
					},
					{
						label: "users.title",
						icon: "people",
						route: "/users",
						access: initData.value.user_management.can_edit ?? false,
					},
					{
						label: "diagnostics.title",
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
						label: "left-menu.logs",
						icon: "excerpt",
						url: Constants.BASE_URL + "/Logs",
						access: (initData.value.settings.can_see_logs ?? false) && logsEnabled.value,
					},
					{
						label: "left-menu.logs",
						icon: "excerpt",
						access: (initData.value.settings.can_see_logs ?? false) && !logsEnabled.value,
					},
					{
						label: "left-menu.jobs",
						icon: "project",
						route: "/jobs",
						access: initData.value.settings.can_see_logs ?? false,
					},
					{
						label: "left-menu.clockwork",
						icon: "telescope",
						url: clockwork_url.value ?? "",
						access: clockwork_url.value !== null && (initData.value.settings.can_access_dev_tools ?? false),
					},
				],
			},
			{
				label: "Lychee",
				items: [
					{
						label: "left-menu.about",
						icon: "info",
						access: true,
						command: () => (openLycheeAbout.value = true),
					},
					{
						label: "left-menu.changelog",
						icon: "copywriting",
						access: true,
						route: "/changelogs",
					},
					{
						label: "left-menu.api",
						icon: "book",
						access: initData.value.settings.can_edit ?? false,
						url: "/docs/api",
					},
					{
						label: "left-menu.source_code",
						icon: "code",
						access: user.value?.id === null || is_se_info_hidden.value === false,
						url: "https://github.com/LycheeOrg/Lychee",
					},
					{
						label: "left-menu.support",
						icon: "heart",
						access: is_se_info_hidden.value === false,
						url: "https://lycheeorg.dev/get-supporter-edition/",
					},
				],
			},
		];

		return baseMenu.filter((item) => {
			if (item.items) {
				item.items = item.items.filter((subItem) => subItem.access !== false);
				return item.items.length > 0;
			}
			return item.access !== false;
		});
	});

	const profileItems = computed<MenyType[]>(() => {
		if (!initData.value) {
			return [];
		}
		const userMenu = [
			{
				label: "left-menu.user",
				icon: "person",
				route: "/profile",
				access: initData.value.user.can_edit ?? false,
			},
			{
				label: "sharing.title",
				icon: "cloud",
				route: "/sharing",
				access: initData.value.root_album.can_upload ?? false,
			},
			{
				label: "statistics.title",
				icon: "bar-chart",
				route: "/statistics",
				access: is_se_enabled.value === true,
			},
			{
				label: "statistics.title",
				icon: "bar-chart",
				route: "/statistics",
				access: is_se_enabled.value === false && is_se_preview_enabled.value === true,
				seTag: true,
			},
		];

		return userMenu.filter((item) => item.access !== false);
	});

	return {
		user,
		left_menu_open,
		initData,
		openLycheeAbout,
		canSeeAdmin,
		load,
		items,
		profileItems,
	};
}
