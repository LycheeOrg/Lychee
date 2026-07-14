<template>
	<ImportFromLink v-if="timelineStore.rootRights?.can_upload" v-model:open="is_import_from_link_open" />
	<ImportFromServer v-if="timelineStore.rootRights?.can_import_from_server" v-model:open="is_import_from_server_open" />
	<DropBox v-if="timelineStore.rootRights?.can_upload" v-model:open="is_import_from_dropbox_open" />
	<UHeader :toggle="false" class="z-10" :ui="{ root: 'border-b-0', center: 'flex' }">
		<template #left>
			<OpenLeftMenu />
		</template>

		<div class="absolute top-0 py-3 left-1/2 -translate-x-1/2 h-14 flex items-center">
			<template v-if="lycheeStore.site_logo !== ''">
				<img :src="lycheeStore.site_logo" alt="logo" class="h-8 object-contain" @click="is_metrics_open = !is_metrics_open" />
			</template>
			<template v-else>
				<span class="sm:hidden font-bold">
					{{ $t("gallery.timeline.title") }}
				</span>
				<span class="hidden lg:block font-bold text-sm lg:text-base text-center w-full" @click="is_metrics_open = !is_metrics_open">
					{{ lycheeStore.title }}
				</span>
			</template>
		</div>

		<template #right>
			<template v-if="userStore.isGuest">
				<UButton as="router-link" :to="{ name: 'login' }" color="neutral" variant="ghost" class="py-2 px-4 hidden xl:inline-flex">
					{{ $t("dialogs.login.signin") }}
				</UButton>
				<UButton
					v-if="is_registration_enabled"
					as="router-link"
					:to="{ name: 'register' }"
					color="neutral"
					variant="ghost"
					class="py-2 px-4 mr-12 lg:mr-0 inline-flex"
				>
					{{ $t("profile.register.signup") }}
				</UButton>
			</template>
			<!-- Not logged in. -->
			<BackLinkButton v-if="userStore.isGuest && timelineStore.rootConfig" :config="timelineStore.rootConfig" />
			<!-- Maybe logged in. -->
			<div class="hidden lg:flex items-center gap-1">
				<template v-for="(item, idx) in menu" :key="`menu-item-${idx}`">
					<UButton
						v-if="item.type === 'link'"
						as="router-link"
						:to="item.to"
						:icon="item.icon"
						:color="item.color ?? 'neutral'"
						variant="ghost"
					/>
					<UButton v-else :icon="item.icon" :color="item.color ?? 'neutral'" variant="ghost" @click="item.callback" />
				</template>
				<UDropdownMenu v-if="timelineStore.rootRights?.can_upload" :items="addMenuSections">
					<UButton icon="lucide:plus" color="neutral" variant="ghost" />
				</UDropdownMenu>
			</div>
			<UDropdownMenu :items="mobileMenuSections" class="lg:hidden">
				<UButton icon="lucide:chevrons-down" color="neutral" variant="ghost" />
			</UDropdownMenu>
		</template>
	</UHeader>
</template>
<script setup lang="ts">
import ImportFromLink from "@/v8/components/modals/ImportFromLink.vue";
import ImportFromServer from "@/v8/components/modals/ImportFromServer.vue";
import DropBox from "@/v8/components/modals/DropBox.vue";
import { computed, ComputedRef } from "vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useContextMenuAlbumsAdd } from "@/v8/composables/contextMenus/contextMenuAlbumsAdd";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import BackLinkButton from "./BackLinkButton.vue";
import OpenLeftMenu from "./OpenLeftMenu.vue";
import { useFavouriteStore } from "@/stores/FavouriteState";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useTimelineStore } from "@/stores/TimelineState";
import { useUserStore } from "@/stores/UserState";
import { trans } from "laravel-vue-i18n";
import type { DropdownMenuItem } from "@nuxt/ui";
import type { AddMenuItem } from "@/v8/composables/contextMenus/contextMenuAlbumAdd";

const emits = defineEmits<{
	refresh: [];
	help: [];
}>();

const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const favourites = useFavouriteStore();
const timelineStore = useTimelineStore();
const userStore = useUserStore();

const { dropbox_api_key, is_favourite_enabled, is_registration_enabled, is_live_metrics_enabled, is_se_preview_enabled, is_person_album_enabled } =
	storeToRefs(lycheeStore);
const { is_login_open, is_upload_visible, is_create_album_visible, is_create_tag_album_visible, is_metrics_open } = storeToRefs(togglableStore);
const leftMenuStore = useLeftMenuStateStore();

const router = useRouter();

const {
	toggleCreateAlbum,
	toggleCreateTagAlbum,
	toggleCreatePersonAlbum,
	is_import_from_link_open,
	toggleImportFromLink,
	is_import_from_dropbox_open,
	toggleImportFromServer,
	is_import_from_server_open,
	toggleImportFromDropbox,
	toggleUpload,
	toggleCameraCapture,
} = useGalleryModals(togglableStore);

const is_owner = computed(() => timelineStore.rootRights?.can_import_from_server === true);
const { addMenu } = useContextMenuAlbumsAdd(
	{
		toggleUpload: toggleUpload,
		toggleCameraCapture: toggleCameraCapture,
		toggleCreateAlbum: toggleCreateAlbum,
		toggleImportFromLink: toggleImportFromLink,
		toggleImportFromDropbox: toggleImportFromDropbox,
		toggleCreateTagAlbum: toggleCreateTagAlbum,
		toggleCreatePersonAlbum: toggleCreatePersonAlbum,
		toggleImportFromServer: toggleImportFromServer,
	},
	dropbox_api_key,
	is_owner,
	is_person_album_enabled,
);

const addMenuSections = computed<DropdownMenuItem[][]>(() => {
	const sections: DropdownMenuItem[][] = [[]];
	for (const entry of addMenu.value as AddMenuItem[]) {
		if ("if" in entry && entry.if === false) {
			continue;
		}
		if ("is_divider" in entry) {
			sections.push([]);
			continue;
		}
		sections[sections.length - 1].push({
			label: trans(entry.label),
			icon: entry.icon,
			onSelect: entry.callback,
		});
	}
	return sections.filter((s) => s.length > 0);
});

function openHelp() {
	emits("help");
}

function openSearch() {
	router.push({ name: "search" });
}

defineShortcuts({
	n: () => timelineStore.rootRights?.can_upload && (is_create_album_visible.value = true),
	u: () => timelineStore.rootRights?.can_upload && (is_upload_visible.value = true),
	"/": () => timelineStore.rootConfig?.is_search_accessible && openSearch(),
	// on key stroke escape:
	// 1. lose focus
	// 2. close modals
	// 3. go back
	escape: {
		usingInput: true,
		handler: () => {
			// 1. lose focus
			if (document.activeElement instanceof HTMLElement) {
				document.activeElement.blur();
				return;
			}

			// 2. close modals
			if (is_login_open.value) {
				is_login_open.value = false;
				return;
			}

			if (is_upload_visible.value) {
				is_upload_visible.value = false;
				return;
			}
			if (is_create_album_visible.value) {
				is_create_album_visible.value = false;
				return;
			}
			if (is_create_tag_album_visible.value) {
				is_create_tag_album_visible.value = false;
				return;
			}

			leftMenuStore.left_menu_open = false;
		},
	},
});

type Link = {
	type: "link";
	to: { name: string };
};
type Callback = {
	type: "fn";
	callback: () => void;
};
type Item = {
	icon: string;
	if: boolean;
	color?: "primary" | "neutral";
};
type MenuRight = (Item & Link) | (Item & Callback);

const menu = computed(() =>
	[
		{
			to: { name: "favourites" },
			type: "link",
			icon: "lucide:heart",
			if: is_favourite_enabled.value && (favourites.photos?.length ?? 0) > 0,
			key: "favourites",
		},
		{
			icon: "lucide:search",
			type: "fn",
			callback: openSearch,
			if: timelineStore.rootConfig?.is_search_accessible,
			key: "search",
		},
		{
			icon: "lucide:bell",
			type: "fn",
			callback: () => (is_metrics_open.value = true),
			if: is_live_metrics_enabled.value && timelineStore.rootRights?.can_see_live_metrics,
			key: "metrics",
		},
		{
			icon: "lucide:bell",
			color: "primary",
			type: "fn",
			callback: () => (is_metrics_open.value = true),
			if: is_se_preview_enabled.value && timelineStore.rootRights?.can_see_live_metrics,
			key: "se_preview",
		},
		{
			icon: "lucide:circle-help",
			type: "fn",
			callback: openHelp,
			if: !isTouchDevice() && userStore.isLoggedIn && timelineStore.rootConfig?.show_keybinding_help_button && document.body.scrollWidth > 800,
			key: "help",
		},
	].filter((item) => item.if),
) as ComputedRef<MenuRight[]>;

// Mobile "more" dropdown: mirrors `menu` plus the add-menu (only entry that needs a submenu).
const mobileMenuSections = computed<DropdownMenuItem[][]>(() => {
	const items: DropdownMenuItem[] = menu.value.map((item) => ({
		label: "",
		icon: item.icon,
		to: item.type === "link" ? item.to : undefined,
		onSelect: item.type === "fn" ? item.callback : undefined,
	}));
	if (timelineStore.rootRights?.can_upload) {
		items.push({
			icon: "lucide:plus",
			label: trans("gallery.menus.add"),
			children: addMenuSections.value,
		} as DropdownMenuItem);
	}
	return [items];
});
</script>
