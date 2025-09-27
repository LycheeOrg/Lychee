<template>
	<ImportFromLink v-if="timelineStore.rootRights?.can_upload" v-model:visible="is_import_from_link_open" />
	<ImportFromServer v-if="timelineStore.rootRights?.can_import_from_server" v-model:visible="is_import_from_server_open" />
	<DropBox v-if="timelineStore.rootRights?.can_upload" v-model:visible="is_import_from_dropbox_open" />
	<Toolbar
		class="w-full border-0 h-14 z-10 rounded-none"
		:pt:root:class="'flex-nowrap relative'"
		:pt:center:class="'absolute top-0 py-3 left-1/2 -translate-x-1/2 h-14'"
	>
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			<span class="sm:hidden font-bold">
				{{ $t("gallery.timeline.title") }}
			</span>
			<span class="hidden lg:block font-bold text-sm lg:text-base text-center w-full" @click="is_metrics_open = !is_metrics_open">
				{{ lycheeStore.title }}
			</span>
		</template>

		<template #end>
			<template v-if="userStore.isGuest">
				<Button
					as="router-link"
					:to="{ name: 'login' }"
					severity="secondary"
					text
					:class="{
						'py-2 px-4 rounded-xl hidden xl:block': true,
						'dark:hover:text-surface-100': true,
						'hover:text-surface-800': true,
					}"
				>
					{{ $t("dialogs.login.signin") }}
				</Button>
				<Button
					v-if="is_registration_enabled"
					as="router-link"
					:to="{ name: 'register' }"
					severity="secondary"
					text
					:class="{
						'py-2 px-4 rounded-xl mr-12 block lg:mr-0': true,
						'dark:hover:text-surface-100 dark:border-surface-400 dark:hover:border-surface-100': true,
						'hover:text-surface-800 border-surface-500 hover:border-surface-800': true,
					}"
				>
					{{ $t("profile.register.signup") }}
				</Button>
			</template>
			<!-- Maybe logged in. -->
			<div class="hidden lg:block">
				<template v-for="(item, idx) in menu" :key="`menu-item-${idx}`">
					<template v-if="item.type === 'link'">
						<!-- @vue-ignore -->
						<Button as="router-link" :to="item.to" :icon="item.icon" class="border-none" severity="secondary" text />
					</template>
					<template v-else>
						<Button :icon="item.icon" class="border-none" severity="secondary" text @click="item.callback" />
					</template>
				</template>
				<!-- Not logged in. -->
				<BackLinkButton v-if="userStore.isGuest && timelineStore.rootConfig" :config="timelineStore.rootConfig" />
			</div>
			<SpeedDial
				:model="menu"
				direction="down"
				class="top-0 ltr:mr-4 rtl:ml-4 absolute ltr:right-0 rtl:left-0 lg:hidden"
				:button-props="{ severity: 'help', rounded: true }"
			>
				<template #button="{ toggleCallback }">
					<Button text severity="secondary" class="border-none h-14" icon="pi pi-angle-double-down" @click="toggleCallback" />
				</template>
				<template #item="{ item }">
					<template v-if="item.type === 'link'">
						<Button as="router-link" :to="item.to" :icon="item.icon" class="shadow-md shadow-black/25" severity="warn" rounded />
					</template>
					<template v-else>
						<Button :icon="item.icon" class="shadow-md shadow-black/25" severity="warn" rounded @click="item.callback" />
					</template>
				</template>
			</SpeedDial>
		</template>
	</Toolbar>
	<ContextMenu v-if="timelineStore.rootRights?.can_upload" ref="addmenu" :model="addMenu">
		<template #item="{ item, props }">
			<Divider v-if="item.is_divider" />
			<a v-else v-ripple v-bind="props.action" @click="item.callback">
				<span :class="item.icon" />
				<span class="ltr:ml-2 rtl:mr-2">
					<!-- @vue-ignore -->
					{{ $t(item.label) }}
				</span>
			</a>
		</template>
	</ContextMenu>
</template>
<script setup lang="ts">
import Button from "primevue/button";
import SpeedDial from "primevue/speeddial";
import Toolbar from "primevue/toolbar";
import ContextMenu from "primevue/contextmenu";
import Divider from "primevue/divider";
import ImportFromLink from "@/components/modals/ImportFromLink.vue";
import { computed, ComputedRef } from "vue";
import { onKeyStroke } from "@vueuse/core";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { isTouchDevice, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useContextMenuAlbumsAdd } from "@/composables/contextMenus/contextMenuAlbumsAdd";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import DropBox from "@/components/modals/DropBox.vue";
import BackLinkButton from "./BackLinkButton.vue";
import OpenLeftMenu from "./OpenLeftMenu.vue";
import { useFavouriteStore } from "@/stores/FavouriteState";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import ImportFromServer from "@/components/modals/ImportFromServer.vue";
import { useTimelineStore } from "@/stores/timelineState";
import { useUserStore } from "@/stores/UserState";

const emits = defineEmits<{
	refresh: [];
	help: [];
}>();

const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const favourites = useFavouriteStore();
const timelineStore = useTimelineStore();
const userStore = useUserStore();

const { dropbox_api_key, is_favourite_enabled, is_registration_enabled, is_live_metrics_enabled, is_se_preview_enabled } = storeToRefs(lycheeStore);
const { is_login_open, is_upload_visible, is_create_album_visible, is_create_tag_album_visible, is_metrics_open } = storeToRefs(togglableStore);
const leftMenuStore = useLeftMenuStateStore();

const router = useRouter();

const {
	toggleCreateAlbum,
	toggleCreateTagAlbum,
	is_import_from_link_open,
	toggleImportFromLink,
	is_import_from_dropbox_open,
	toggleImportFromServer,
	is_import_from_server_open,
	toggleImportFromDropbox,
	toggleUpload,
} = useGalleryModals(togglableStore);

const is_owner = computed(() => timelineStore.rootRights?.can_import_from_server === true);
const { addmenu, addMenu } = useContextMenuAlbumsAdd(
	{
		toggleUpload: toggleUpload,
		toggleCreateAlbum: toggleCreateAlbum,
		toggleImportFromLink: toggleImportFromLink,
		toggleImportFromDropbox: toggleImportFromDropbox,
		toggleCreateTagAlbum: toggleCreateTagAlbum,
		toggleImportFromServer: toggleImportFromServer,
	},
	dropbox_api_key,
	is_owner,
);

function openAddMenu(event: Event) {
	addmenu.value.show(event);
}

function openHelp() {
	emits("help");
}

function openSearch() {
	router.push({ name: "search" });
}

onKeyStroke("n", () => !shouldIgnoreKeystroke() && timelineStore.rootRights?.can_upload && (is_create_album_visible.value = true));
onKeyStroke("u", () => !shouldIgnoreKeystroke() && timelineStore.rootRights?.can_upload && (is_upload_visible.value = true));
onKeyStroke("/", () => !shouldIgnoreKeystroke() && timelineStore.rootConfig?.is_search_accessible && openSearch());

// on key stroke escape:
// 1. lose focus
// 2. close modals
// 3. go back
onKeyStroke("Escape", () => {
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
};
type MenuRight = (Item & Link) | (Item & Callback);

const menu = computed(() =>
	[
		{
			to: { name: "favourites" },
			type: "link",
			icon: "pi pi-heart",
			if: is_favourite_enabled.value && (favourites.photos?.length ?? 0) > 0,
			key: "favourites",
		},
		{
			icon: "pi pi-search",
			type: "fn",
			callback: openSearch,
			if: timelineStore.rootConfig?.is_search_accessible,
			key: "search",
		},
		{
			icon: "pi pi-bell",
			type: "fn",
			callback: () => (is_metrics_open.value = true),
			if: is_live_metrics_enabled.value && timelineStore.rootRights?.can_see_live_metrics,
			key: "metrics",
		},
		{
			icon: "pi pi-bell text-primary-emphasis",
			type: "fn",
			callback: () => (is_metrics_open.value = true),
			if: is_se_preview_enabled.value && timelineStore.rootRights?.can_see_live_metrics,
			key: "se_preview",
		},
		{
			icon: "pi pi-question-circle",
			type: "fn",
			callback: openHelp,
			if: !isTouchDevice() && userStore.isLoggedIn && timelineStore.rootConfig?.show_keybinding_help_button && document.body.scrollWidth > 800,
			key: "help",
		},
		{
			icon: "pi pi-plus",
			type: "fn",
			callback: openAddMenu,
			if: timelineStore.rootRights?.can_upload,
			key: "add_menu",
		},
	].filter((item) => item.if),
) as ComputedRef<MenuRight[]>;
</script>
