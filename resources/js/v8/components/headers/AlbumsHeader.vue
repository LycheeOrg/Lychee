<template>
	<UHeader
		v-if="albumsStore.rootConfig && albumsStore.rootRights"
		:class="{
			'bg-transparent': albumsStore.rootConfig.is_header_bar_transparent,
			'max-h-14': !is_full_screen,
			'max-h-0 overflow-hidden': is_full_screen,
		}"
		:toggle="false"
	>
		<template #left>
			<OpenLeftMenu />
		</template>

		<div class="absolute top-0 py-3 left-1/2 -translate-x-1/2 h-14 flex items-center">
			<template v-if="albumsStore.rootConfig.header_image_url === ''">
				<img
					v-if="lycheeStore.site_logo !== ''"
					:src="lycheeStore.site_logo"
					alt="logo"
					class="h-8 object-contain"
					@click="is_metrics_open = !is_metrics_open"
					id="header-site-logo-title"
				/>
				<template v-else>
					<span class="lg:hidden font-bold text-shadow-sm text-shadow-black">
						{{ $t("gallery.albums") }}
					</span>
					<span
						class="hidden lg:block font-bold text-shadow-sm text-shadow-black text-sm lg:text-base text-center w-full"
						@click="is_metrics_open = !is_metrics_open"
						>{{ props.title }}</span
					>
				</template>
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
			<div class="mr-12 lg:mr-0">
				<BackLinkButton v-if="userStore.isGuest && albumsStore.rootConfig" :config="albumsStore.rootConfig" />
			</div>
			<!-- Maybe logged in. -->
			<div class="hidden lg:flex items-center gap-1">
				<template v-for="(item, idx) in menu" :key="`menu-item-${idx}`">
					<UButton
						v-if="item.type === 'link'"
						as="router-link"
						:to="item.to"
						:icon="item.icon"
						:color="(item.severity as 'secondary' | 'danger' | 'primary') ?? 'neutral'"
						variant="ghost"
					/>
					<UButton
						v-else
						:icon="item.icon"
						:color="(item.severity as 'secondary' | 'danger' | 'primary') ?? 'neutral'"
						variant="ghost"
						@click="item.callback"
					/>
				</template>
				<UDropdownMenu v-if="albumsStore.rootRights?.can_upload" :items="addMenuSections">
					<UButton icon="lucide:plus" color="neutral" variant="ghost" />
				</UDropdownMenu>
			</div>
			<UDropdownMenu :items="mobileMenuSections" class="lg:hidden">
				<UButton icon="lucide:chevrons-down" color="neutral" variant="ghost" />
			</UDropdownMenu>
		</template>
	</UHeader>
	<div v-if="albumsStore.rootConfig?.header_image_url !== ''" class="relative w-full h-[50vh] -mt-14 z-0">
		<img :src="albumsStore.rootConfig?.header_image_url" class="object-cover h-full w-full" />
		<div class="absolute top-0 left-0 w-full h-full flex items-center justify-center px-20">
			<img
				v-if="lycheeStore.site_logo !== ''"
				:src="lycheeStore.site_logo"
				alt="logo"
				class="max-h-24 max-w-sm object-contain"
				id="header-site-logo"
			/>
			<h1
				v-else
				class="text-sm font-bold sm:text-lg md:text-3xl md:font-normal text-white uppercase text-center text-shadow-md text-shadow-black/25"
				id="header-site-text"
			>
				{{ props.title }}
			</h1>
		</div>
	</div>
</template>
<script setup lang="ts">
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
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useUserStore } from "@/stores/UserState";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { trans } from "laravel-vue-i18n";
import type { DropdownMenuItem } from "@nuxt/ui";
import type { AddMenuItem } from "@/v8/composables/contextMenus/contextMenuAlbumAdd";

const props = defineProps<{
	title: string;
}>();

const emits = defineEmits<{
	refresh: [];
	help: [];
}>();

const userStore = useUserStore();
const leftMenuStore = useLeftMenuStateStore();
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const favourites = useFavouriteStore();
const albumsStore = useAlbumsStore();
const orderManagementStore = useOrderManagementStore();

const { dropbox_api_key, is_favourite_enabled, is_se_preview_enabled, is_live_metrics_enabled, is_registration_enabled, is_person_album_enabled } =
	storeToRefs(lycheeStore);
const { is_login_open, is_upload_visible, is_create_album_visible, is_create_tag_album_visible, is_metrics_open, is_full_screen } =
	storeToRefs(togglableStore);

const router = useRouter();

const {
	toggleCreateAlbum,
	toggleCreateTagAlbum,
	toggleCreatePersonAlbum,
	toggleImportFromLink,
	toggleImportFromDropbox,
	toggleUpload,
	toggleImportFromServer,
	toggleCameraCapture,
} = useGalleryModals(togglableStore);

const is_owner = computed(() => albumsStore.rootRights?.can_import_from_server ?? false);

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

function toggleToGrid() {
	lycheeStore.album_view_mode = "grid";
}

function toggleToList() {
	lycheeStore.album_view_mode = "list";
}

defineShortcuts({
	n: () => albumsStore.rootRights?.can_upload && (is_create_album_visible.value = true),
	u: () => albumsStore.rootRights?.can_upload && (is_upload_visible.value = true),
	"/": () => albumsStore.rootConfig?.is_search_accessible && openSearch(),
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
	severity?: string;
};
type MenuRight = (Item & Link & { key: string }) | (Item & Callback & { key: string });

const menu = computed(() =>
	[
		{
			to: { name: "basket" },
			type: "link",
			icon: "lucide:shopping-cart",
			severity: orderManagementStore.order?.status === "processing" ? "danger" : "secondary",
			if: orderManagementStore.hasItems,
			key: "basket",
		},
		{
			to: { name: "favourites" },
			type: "link",
			icon: "lucide:heart",
			if: userStore.isLoggedIn && is_favourite_enabled.value && (favourites.photos?.length ?? 0) > 0,
			key: "favourites",
		},
		{
			icon: "lucide:layout-grid",
			type: "fn",
			callback: toggleToGrid,
			if: lycheeStore.album_view_mode === "list",
			key: "view_grid",
		},
		{
			icon: "lucide:list",
			type: "fn",
			callback: toggleToList,
			if: lycheeStore.album_view_mode === "grid",
			key: "view_list",
		},
		{
			icon: "lucide:search",
			type: "fn",
			callback: openSearch,
			if: albumsStore.rootConfig?.is_search_accessible,
			key: "search",
		},
		{
			icon: "lucide:bell",
			type: "fn",
			callback: () => (is_metrics_open.value = true),
			if: is_live_metrics_enabled.value && albumsStore.rootRights?.can_see_live_metrics,
			key: "metrics",
		},
		{
			icon: "lucide:bell",
			severity: "primary",
			type: "fn",
			callback: () => (is_metrics_open.value = true),
			if: is_se_preview_enabled.value && albumsStore.rootRights?.can_see_live_metrics,
			key: "se_preview",
		},
		{
			icon: "lucide:circle-help",
			type: "fn",
			callback: openHelp,
			if: !isTouchDevice() && userStore.isLoggedIn && albumsStore.rootConfig?.show_keybinding_help_button && document.body.scrollWidth > 800,
			key: "help",
		},
		{
			icon: "lucide:eye-off",
			type: "fn",
			callback: () => (lycheeStore.are_nsfw_visible = false),
			if: isTouchDevice() && albumsStore.hasHidden && lycheeStore.are_nsfw_visible,
			key: "hide_nsfw",
		},
		{
			icon: "lucide:eye",
			type: "fn",
			callback: () => (lycheeStore.are_nsfw_visible = true),
			if: isTouchDevice() && albumsStore.hasHidden && !lycheeStore.are_nsfw_visible,
			key: "show_nsfw",
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
	if (albumsStore.rootRights?.can_upload) {
		items.push({
			icon: "lucide:plus",
			label: trans("gallery.menus.add"),
			children: addMenuSections.value,
		} as DropdownMenuItem);
	}
	return [items];
});
</script>
