import Constants from "@/services/constants";
import { UserStore } from "@/stores/UserState";
import { FavouriteStore } from "@/stores/FavouriteState";
import { LeftMenuStateStore } from "@/stores/LeftMenuState";
import { GlobalRightsStore } from "@/stores/GlobalRightsState";
import { LycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { computed, ref } from "vue";
import { RouteLocationNormalizedLoadedGeneric } from "vue-router";
import { trans } from "laravel-vue-i18n";
import type { NavigationMenuItem } from "@nuxt/ui";

export type LeftMenuItem = NavigationMenuItem & { seTag?: boolean; count?: number };

// Internal-only shape: a menu entry before its `access` gate is applied and it's
// translated into the NavigationMenuItem fields the template actually renders.
type MenuEntry = {
	label: string;
	icon: string;
	route?: string;
	url?: string;
	target?: string;
	access: boolean;
	seTag?: boolean;
	command?: () => void;
	num?: number;
};

function toLeafItem(item: MenuEntry): LeftMenuItem {
	return {
		label: trans(item.label),
		icon: item.icon,
		seTag: item.seTag,
		count: item.num && item.num > 0 ? item.num : undefined,
		to: item.route ?? item.url,
		target: item.target as LeftMenuItem["target"],
		onSelect: item.command,
	};
}

function toSection(menu: MenuEntry[]): LeftMenuItem[] {
	return menu.filter((item) => item.access !== false).map(toLeafItem);
}

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
		is_se_enabled,
		is_se_preview_enabled,
		is_favourite_enabled,
		is_timeline_page_enabled,
		use_admin_dashboard,
		is_embed_enabled,
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

	// Main gallery/user navigation (guests + logged in users).
	const items = computed<LeftMenuItem[]>(() => {
		if (!rights.value) {
			return [];
		}

		return toSection([
			{
				label: "gallery.title",
				icon: "lucide:images",
				access: !(route.name as string).startsWith("gallery") && user.value?.id === null,
				route: "/gallery",
			},
			{
				label: "flow.title",
				icon: "lucide:move-vertical",
				access: !(route.name as string).startsWith("flow") && (rights.value.modules.is_mod_flow_enabled ?? false),
				route: "/flow",
			},
			{
				label: "gallery.timeline.title",
				icon: "lucide:clock",
				route: "/timeline",
				access: !(route.name as string).includes("timeline") && is_timeline_page_enabled.value,
			},
			{
				label: "tags.title",
				icon: "lucide:tags",
				access: user.value?.id !== null,
				route: "/tags",
			},
			{
				label: "people.title",
				icon: "lucide:users",
				access: (lycheeStore.is_face_recognition_enabled ?? false) && user.value?.id !== null,
				route: "/people",
			},
			{
				label: "gallery.favourites",
				icon: "lucide:heart",
				route: "/gallery/favourites",
				access: is_favourite_enabled.value && (favourites.photos?.length ?? 0) > 0,
			},
			{
				label: "left-menu.frame",
				icon: "lucide:monitor",
				route: "/frame",
				access: rights.value.modules.is_mod_frame_enabled ?? false,
			},
			{
				label: "left-menu.map",
				icon: "lucide:map",
				access: rights.value.modules.is_map_enabled ?? false,
				route: "/map",
			},
			{
				label: "webshop.orderList.orders",
				icon: "lucide:shopping-cart",
				route: "/orders",
				access: (rights.value.modules.is_mod_webshop_enabled ?? false) && user.value?.id !== null,
			},
			{
				label: "left-menu.embed_stream",
				icon: "lucide:code",
				access: (is_embed_enabled.value ?? true) && user.value?.id !== null,
				command: () => {
					const togglableStore = useTogglablesStateStore();
					togglableStore.embed_code_mode = "stream";
					togglableStore.is_embed_code_visible = true;
				},
			},
			{
				label: "left-menu.contact",
				icon: "lucide:mail",
				route: "/contact",
				access: rights.value.modules.is_contact_enabled && !canSeeAdmin.value,
			},
			{
				label: "left-menu.admin",
				icon: "lucide:layout-grid",
				route: "/admin",
				access: canSeeAdmin.value && (use_admin_dashboard.value ?? true),
			},
		]);
	});

	// Admin section: a single link to the full dashboard, or - when that page is
	// disabled - the individual admin tool links inlined directly into the left menu.
	const adminItems = computed<LeftMenuItem[]>(() => {
		if (!rights.value) {
			return [];
		}

		return toSection([
			{
				label: "settings.title",
				icon: "cog",
				route: "/admin/settings",
				access: rights.value.settings.can_edit ?? false,
			},
			{
				label: "users.title",
				icon: "lucide:user",
				route: "/admin/users",
				access: rights.value.user_management.can_edit ?? false,
			},
			{
				label: "user-groups.title",
				icon: "lucide:users",
				route: "/admin/user-groups",
				access: rights.value.settings.can_acess_user_groups ?? false,
			},
			{
				label: "webshop.purchasablesList.purchasables",
				icon: "lucide:shopping-bag",
				route: "/admin/purchasables",
				access: (rights.value.modules.is_mod_webshop_enabled ?? false) && (rights.value.settings.can_edit ?? false),
			},
			{
				label: "left-menu.shopSizes",
				icon: "lucide:expand",
				route: "/admin/shop/sizes",
				access: (rights.value.modules.is_mod_webshop_enabled ?? false) && (rights.value.settings.can_edit ?? false),
			},
			{
				label: "left-menu.messages",
				icon: "lucide:inbox",
				route: "/admin/contact-messages",
				access: rights.value.modules.is_contact_enabled && canSeeAdmin.value,
				num: rights.value.modules.messages_count,
			},
			{
				label: "left-menu.webhooks",
				icon: "lucide:send",
				route: "/admin/webhooks",
				access: rights.value.modules.is_mod_webhook_enabled ?? false,
			},
			{
				label: "bulk_album_edit.title",
				icon: "lucide:columns-3",
				route: "/bulk-album-edit",
				access: authStore.isAdmin,
			},
			{
				label: "moderation.title",
				icon: "lucide:shield",
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
				icon: "lucide:smile",
				route: "/admin/maintenance/faces",
				access: (rights.value.settings.can_edit ?? false) && (lycheeStore.is_face_recognition_enabled ?? false),
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
		]);
	});

	const profileItems = computed<LeftMenuItem[]>(() => {
		if (!rights.value) {
			return [];
		}

		return toSection([
			{
				label: "left-menu.user",
				icon: "lucide:user-pen",
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
				label: "insights.title",
				icon: "lucide:chart-column",
				route: "/insights",
				access: is_se_enabled.value === true,
			},
			{
				label: "insights.title",
				icon: "lucide:chart-column",
				route: "/insights",
				access: is_se_enabled.value === false && is_se_preview_enabled.value === true,
				seTag: true,
			},
			{
				label: "renamer.title",
				icon: "lucide:file-edit",
				access: rights.value.modules.is_mod_renamer_enabled ?? false,
				route: "/renamerRules",
			},
		]);
	});

	return {
		user,
		left_menu_open,
		rights,
		openLycheeAbout,
		canSeeAdmin,
		items,
		adminItems,
		profileItems,
	};
}
