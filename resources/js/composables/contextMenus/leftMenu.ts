import Constants from "@/services/constants";
import { UserStore } from "@/stores/UserState";
import { FavouriteStore } from "@/stores/FavouriteState";
import { LeftMenuStateStore } from "@/stores/LeftMenuState";
import { type GlobalRightsStore } from "@/stores/GlobalRightsState";
import { LycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
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
	globalRightsStore: GlobalRightsStore,
	authStore: UserStore,
	favourites: FavouriteStore,
	route: RouteLocationNormalizedLoadedGeneric,
) {
	const { user } = storeToRefs(authStore);

	const { left_menu_open } = storeToRefs(LeftMenuStateStore);
	const { rights } = storeToRefs(globalRightsStore);
	const {
		clockwork_url,
		is_se_info_hidden,
		is_favourite_enabled,
		is_timeline_page_enabled,
		use_admin_dashboard,
		is_embed_enabled,
		is_white_label_enabled,
		is_face_recognition_enabled,
	} = storeToRefs(lycheeStore);
	const openLycheeAbout = ref(false);
	const logsEnabled = ref(true);

	const canSeeAdmin = computed(() => {
		return (
			rights.value?.settings.can_edit ||
			rights.value?.user_management.can_edit ||
			rights.value?.settings.can_see_diagnostics ||
			rights.value?.settings.can_see_logs ||
			rights.value?.settings.can_acess_user_groups ||
			false
		);
	});

	const items = computed<MenyType[]>(() => {
		if (!rights.value) {
			return [];
		}

		const baseMenu = [
			{
				label: "gallery.title",
				icon: "pi pi-images",
				access: !(route.name as string).startsWith("gallery") && user.value?.id === null,
				route: "/gallery",
			},
			{
				label: "flow.title",
				icon: "pi pi-arrows-v",
				access: !(route.name as string).startsWith("flow") && (rights.value.modules.is_mod_flow_enabled ?? false),
				route: "/flow",
			},
			{
				label: "gallery.timeline.title",
				icon: "pi pi-clock",
				route: "/timeline",
				access: !(route.name as string).includes("timeline") && is_timeline_page_enabled.value,
			},
			{
				label: "tags.title",
				icon: "pi pi-tags",
				access: user.value?.id !== null,
				route: "/tags",
			},
			{
				label: "people.title",
				icon: "pi pi-users",
				access: (is_face_recognition_enabled.value ?? false) && user.value?.id !== null,
				route: "/people",
			},
			{
				label: "gallery.favourites",
				icon: "pi pi-heart",
				route: "/gallery/favourites",
				access: is_favourite_enabled.value && (favourites.photos?.length ?? 0) > 0,
			},
			{
				label: "left-menu.frame",
				icon: "pi pi-desktop",
				route: "/frame",
				access: rights.value.modules.is_mod_frame_enabled ?? false,
			},
			{
				label: "left-menu.map",
				icon: "pi pi-map",
				access: rights.value.modules.is_map_enabled ?? false,
				route: "/map",
			},
			{
				label: "orders",
				icon: "pi pi-shopping-cart",
				route: "/orders",
				access: (rights.value.modules.is_mod_webshop_enabled ?? false) && user.value?.id !== null,
			},
			{
				label: "left-menu.embed_stream",
				icon: "pi pi-code",
				access: (is_embed_enabled.value ?? true) && user.value?.id !== null,
				command: () => {
					const togglableStore = useTogglablesStateStore();
					togglableStore.embed_code_mode = "stream";
					togglableStore.is_embed_code_visible = true;
				},
			},
			{
				label: "left-menu.contact",
				icon: "pi pi-envelope",
				route: "/contact",
				access: rights.value.modules.is_contact_enabled && !canSeeAdmin.value,
			},
			...((use_admin_dashboard.value ?? true)
				? [
						{
							label: "left-menu.admin",
							icon: "pi pi-th-large",
							route: "/admin",
							access: canSeeAdmin.value,
						},
					]
				: [
						{
							label: "left-menu.admin",
							access: canSeeAdmin.value,
							items: [
								{
									label: "settings.title",
									icon: "cog",
									route: "/admin/settings",
									access: rights.value.settings.can_edit ?? false,
								},
								{
									label: "users.title",
									icon: "pi pi-user",
									route: "/admin/users",
									access: rights.value.user_management.can_edit ?? false,
								},
								{
									label: "user-groups.title",
									icon: "pi pi-users",
									route: "/admin/user-groups",
									access: rights.value.settings.can_acess_user_groups ?? false,
								},
								{
									label: "Purchasables",
									icon: "pi pi-shopping-bag",
									route: "/admin/purchasables",
									access: (rights.value.modules.is_mod_webshop_enabled ?? false) && (rights.value.settings.can_edit ?? false),
								},
								{
									label: "left-menu.shopSizes",
									icon: "pi pi-expand",
									route: "/admin/shop/sizes",
									access: (rights.value.modules.is_mod_webshop_enabled ?? false) && (rights.value.settings.can_edit ?? false),
								},
								{
									label: "left-menu.messages",
									icon: "pi pi-inbox",
									route: "/admin/contact-messages",
									access: rights.value.modules.is_contact_enabled && canSeeAdmin.value,
									num: rights.value.modules.messages_count,
								},
								{
									label: "left-menu.webhooks",
									icon: "pi pi-send",
									route: "/admin/webhooks",
									access: rights.value.modules.is_mod_webhook_enabled ?? false,
								},
								{
									label: "bulk_album_edit.title",
									icon: "pi pi-objects-column",
									route: "/bulk-album-edit",
									access: authStore.isAdmin,
								},
								{
									label: "moderation.title",
									icon: "pi pi-shield",
									route: "/admin/moderation",
									access: canSeeAdmin.value,
								},
								{
									label: "diagnostics.title",
									icon: "wrench",
									route: "/diagnostics",
									access: rights.value.settings.can_see_diagnostics ?? false,
								},
								{
									label: "maintenance.title",
									icon: "timer",
									route: "/admin/maintenance",
									access: rights.value.settings.can_edit ?? false,
								},
								{
									label: "maintenance.face_quality.title",
									icon: "pi pi-face-smile",
									route: "/admin/maintenance/faces",
									access: (rights.value.settings.can_edit ?? false) && (is_face_recognition_enabled.value ?? false),
								},
								{
									label: "left-menu.logs",
									icon: "excerpt",
									url: Constants.BASE_URL + "/Logs",
									access: (rights.value.settings.can_see_logs ?? false) && logsEnabled.value,
								},
								{
									label: "left-menu.logs",
									icon: "excerpt",
									access: (rights.value.settings.can_see_logs ?? false) && !logsEnabled.value,
								},
								{
									label: "left-menu.jobs",
									icon: "project",
									route: "/admin/jobs",
									access: rights.value.settings.can_see_logs ?? false,
								},
								{
									label: "left-menu.clockwork",
									icon: "telescope",
									url: clockwork_url.value ?? "",
									access: clockwork_url.value !== null && (rights.value.settings.can_access_dev_tools ?? false),
								},
							],
						},
					]),
			{
				label: "Lychee",
				items: [
					{
						label: "left-menu.about",
						icon: "info",
						access: !is_white_label_enabled.value,
						command: () => (openLycheeAbout.value = true),
					},
					{
						label: "left-menu.changelog",
						icon: "copywriting",
						access: !is_white_label_enabled.value,
						route: "/changelogs",
					},
					{
						label: "left-menu.api",
						icon: "book",
						access: !is_white_label_enabled.value && (rights.value.settings.can_edit ?? false),
						url: Constants.BASE_URL + "/docs/api",
					},
					{
						label: "left-menu.source_code",
						icon: "code",
						access: !is_white_label_enabled.value && (user.value?.id === null || is_se_info_hidden.value === false),
						url: "https://github.com/LycheeOrg/Lychee",
					},
					{
						label: "left-menu.support",
						icon: "heart",
						access: !is_white_label_enabled.value && is_se_info_hidden.value === false,
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
		if (!rights.value) {
			return [];
		}
		const userMenu = [
			{
				label: "left-menu.user",
				icon: "pi pi-user-edit",
				route: "/profile",
				access: rights.value.user.can_edit ?? false,
			},
			{
				label: "sharing.title",
				icon: "cloud",
				route: "/sharing",
				access: rights.value.root_album.can_upload ?? false,
			},
			{
				label: "renamer.title",
				icon: "pi pi-file-edit",
				access: rights.value.modules.is_mod_renamer_enabled ?? false,
				route: "/renamerRules",
			},
		];

		return userMenu.filter((item) => item.access !== false);
	});

	return {
		user,
		left_menu_open,
		rights,
		openLycheeAbout,
		canSeeAdmin,
		items,
		profileItems,
	};
}
