/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { computed, type ComputedRef } from "vue";
import { storeToRefs } from "pinia";
import { type LeftMenuStateStore } from "@/stores/LeftMenuState";
import { type LycheeStateStore } from "@/stores/LycheeState";

export type AdminTileGroup = "core" | "monitoring" | "extensions";

export type AdminTile = {
	key: string;
	group: AdminTileGroup;
	label: string;
	icon: string;
	to: string;
	isExternal: boolean;
	visible: ComputedRef<boolean>;
	num?: ComputedRef<number>;
};

export function useAdminTiles(lycheeStore: LycheeStateStore, leftMenuStore: LeftMenuStateStore): AdminTile[] {
	const { clockwork_url } = storeToRefs(lycheeStore);
	const { initData } = storeToRefs(leftMenuStore);

	return [
		{
			key: "settings",
			group: "core",
			label: "settings.title",
			icon: "cog",
			to: "/admin/settings",
			isExternal: false,
			visible: computed(() => initData.value?.settings.can_edit ?? false),
		},
		{
			key: "diagnostics",
			group: "monitoring",
			label: "diagnostics.title",
			icon: "wrench",
			to: "/diagnostics",
			isExternal: false,
			visible: computed(() => initData.value?.settings.can_see_diagnostics ?? false),
		},
		{
			key: "users",
			group: "core",
			label: "users.title",
			icon: "pi pi-user",
			to: "/admin/users",
			isExternal: false,
			visible: computed(() => initData.value?.user_management.can_edit ?? false),
		},
		{
			key: "user-groups",
			group: "core",
			label: "user-groups.title",
			icon: "pi pi-users",
			to: "/admin/user-groups",
			isExternal: false,
			visible: computed(() => initData.value?.settings.can_acess_user_groups ?? false),
		},
		{
			key: "purchasables",
			group: "extensions",
			label: "Purchasables",
			icon: "pi pi-shopping-bag",
			to: "/admin/purchasables",
			isExternal: false,
			visible: computed(() => (initData.value?.modules.is_mod_webshop_enabled ?? false) && (initData.value?.settings.can_edit ?? false)),
		},
		{
			key: "contact-messages",
			group: "extensions",
			label: "left-menu.messages",
			icon: "pi pi-inbox",
			to: "/admin/contact-messages",
			isExternal: false,
			num: computed(() => initData.value?.modules.messages_count ?? 0),
			visible: computed(
				() =>
					(initData.value?.modules.is_contact_enabled ?? false) &&
					((initData.value?.settings.can_edit ?? false) ||
						(initData.value?.user_management.can_edit ?? false) ||
						(initData.value?.settings.can_see_diagnostics ?? false) ||
						(initData.value?.settings.can_see_logs ?? false) ||
						(initData.value?.settings.can_acess_user_groups ?? false)),
			),
		},
		{
			key: "webhooks",
			group: "extensions",
			label: "left-menu.webhooks",
			icon: "pi pi-send",
			to: "/admin/webhooks",
			isExternal: false,
			visible: computed(() => initData.value?.modules.is_mod_webhook_enabled ?? false),
		},
		{
			key: "faces",
			group: "extensions",
			label: "maintenance.face_quality.title",
			icon: "pi pi-face-smile",
			to: "/admin/maintenance/faces",
			isExternal: false,
			visible: computed(() => (initData.value?.settings.can_edit ?? false) && (initData.value?.modules.is_ai_vision_enabled ?? false)),
		},
		{
			key: "moderation",
			group: "core",
			label: "moderation.title",
			icon: "pi pi-shield",
			to: "/admin/moderation",
			isExternal: false,
			visible: computed(
				() =>
					(initData.value?.settings.can_edit ?? false) ||
					(initData.value?.user_management.can_edit ?? false) ||
					(initData.value?.settings.can_see_diagnostics ?? false) ||
					(initData.value?.settings.can_see_logs ?? false) ||
					(initData.value?.settings.can_acess_user_groups ?? false),
			),
		},
		{
			key: "maintenance",
			group: "core",
			label: "maintenance.title",
			icon: "timer",
			to: "/admin/maintenance",
			isExternal: false,
			visible: computed(() => initData.value?.settings.can_edit ?? false),
		},
		{
			key: "jobs",
			group: "monitoring",
			label: "left-menu.jobs",
			icon: "project",
			to: "/admin/jobs",
			isExternal: false,
			visible: computed(() => initData.value?.settings.can_see_logs ?? false),
		},

		{
			key: "logs",
			group: "monitoring",
			label: "left-menu.logs",
			icon: "excerpt",
			to: "/Logs",
			isExternal: true,
			visible: computed(() => initData.value?.settings.can_see_logs ?? false),
		},
		{
			key: "clockwork",
			group: "monitoring",
			label: "left-menu.clockwork",
			icon: "telescope",
			to: clockwork_url.value ?? "",
			isExternal: true,
			visible: computed(() => clockwork_url.value !== null && (initData.value?.settings.can_access_dev_tools ?? false)),
		},
	];
}
