/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { computed, type ComputedRef } from "vue";
import { storeToRefs } from "pinia";
import { type LeftMenuStateStore } from "@/stores/LeftMenuState";
import { type LycheeStateStore } from "@/stores/LycheeState";

export type AdminTile = {
	key: string;
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
			label: "settings.title",
			icon: "pi pi-cog",
			to: "/admin/settings",
			isExternal: false,
			visible: computed(() => initData.value?.settings.can_edit ?? false),
		},
		{
			key: "users",
			label: "users.title",
			icon: "pi pi-user",
			to: "/admin/users",
			isExternal: false,
			visible: computed(() => initData.value?.user_management.can_edit ?? false),
		},
		{
			key: "user-groups",
			label: "user-groups.title",
			icon: "pi pi-users",
			to: "/admin/user-groups",
			isExternal: false,
			visible: computed(() => initData.value?.settings.can_acess_user_groups ?? false),
		},
		{
			key: "purchasables",
			label: "Purchasables",
			icon: "pi pi-shopping-bag",
			to: "/admin/purchasables",
			isExternal: false,
			visible: computed(() => (initData.value?.modules.is_mod_webshop_enabled ?? false) && (initData.value?.settings.can_edit ?? false)),
		},
		{
			key: "contact-messages",
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
			label: "left-menu.webhooks",
			icon: "pi pi-send",
			to: "/admin/webhooks",
			isExternal: false,
			visible: computed(() => initData.value?.modules.is_mod_webhook_enabled ?? false),
		},
		{
			key: "moderation",
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
			label: "maintenance.title",
			icon: "pi pi-wrench",
			to: "/admin/maintenance",
			isExternal: false,
			visible: computed(() => initData.value?.settings.can_edit ?? false),
		},
		{
			key: "jobs",
			label: "left-menu.jobs",
			icon: "pi pi-briefcase",
			to: "/admin/jobs",
			isExternal: false,
			visible: computed(() => initData.value?.settings.can_see_logs ?? false),
		},
		{
			key: "diagnostics",
			label: "diagnostics.title",
			icon: "pi pi-wrench",
			to: "/diagnostics",
			isExternal: false,
			visible: computed(() => initData.value?.settings.can_see_diagnostics ?? false),
		},
		{
			key: "logs",
			label: "left-menu.logs",
			icon: "pi pi-file-edit",
			to: "/Logs",
			isExternal: true,
			visible: computed(() => initData.value?.settings.can_see_logs ?? false),
		},
		{
			key: "clockwork",
			label: "left-menu.clockwork",
			icon: "pi pi-clock",
			to: clockwork_url.value ?? "",
			isExternal: true,
			visible: computed(() => clockwork_url.value !== null && (initData.value?.settings.can_access_dev_tools ?? false)),
		},
	];
}
