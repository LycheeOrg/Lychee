<template>
	<ImportFromLink v-if="canUpload" v-model:visible="isImportFromLinkOpen" :parent-id="null" />
	<DropBox v-if="canUpload" v-model:visible="isImportFromDropboxOpen" :album-id="null" />
	<Toolbar
		class="w-full border-0 h-14"
		:pt:root:class="'flex-nowrap relative'"
		:pt:center:class="'absolute top-0 py-3 left-1/2 -translate-x-1/2 h-14'"
	>
		<template #start>
			<!-- Not logged in. -->
			<BackLinkButton v-if="props.user.id === null && !isLoginLeft" :config="props.config" />
			<Button
				v-if="props.user.id === null && isLoginLeft"
				icon="pi pi-sign-in"
				class="border-none"
				severity="secondary"
				text
				@click="togglableStore.toggleLogin()"
			/>
			<!-- Logged in. -->
			<OpenLeftMenu v-if="user.id" />
		</template>

		<template #center>
			<span class="sm:hidden font-bold">
				{{ $t("gallery.albums") }}
			</span>
			<span class="hidden sm:block font-bold text-sm lg:text-base text-center w-full">{{ props.title }}</span>
		</template>

		<template #end>
			<!-- Maybe logged in. -->
			<div :class="menu.length > 1 ? 'hidden sm:block' : ''">
				<template v-for="item in menu">
					<template v-if="item.type === 'link'">
						<Button as="router-link" :to="item.to" :icon="item.icon" class="border-none" severity="secondary" text />
					</template>
					<template v-else>
						<Button @click="item.callback" :icon="item.icon" class="border-none" severity="secondary" text />
					</template>
				</template>
				<!-- Not logged in. -->
				<BackLinkButton v-if="props.user.id === null && isLoginLeft" :config="props.config" />
			</div>
			<SpeedDial
				:model="menu"
				v-if="menu.length > 1"
				direction="down"
				class="top-0 mr-4 absolute right-0 sm:hidden"
				:buttonProps="{ severity: 'help', rounded: true }"
			>
				<template #button="{ toggleCallback }">
					<Button text severity="secondary" class="border-none h-14" @click="toggleCallback" icon="pi pi-angle-double-down" />
				</template>
				<template #item="{ item, toggleCallback }">
					<template v-if="item.type === 'link'">
						<Button as="router-link" :to="item.to" :icon="item.icon" class="shadow-md shadow-black/25" severity="warn" rounded />
					</template>
					<template v-else>
						<Button @click="item.callback" :icon="item.icon" class="shadow-md shadow-black/25" severity="warn" rounded />
					</template>
				</template>
			</SpeedDial>
		</template>
	</Toolbar>
	<ContextMenu v-if="props.rights.can_upload" ref="addmenu" :model="addMenu">
		<template #item="{ item, props }">
			<Divider v-if="item.is_divider" />
			<a v-else v-ripple v-bind="props.action" @click="item.callback">
				<span :class="item.icon" />
				<span class="ml-2">
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
import { computed, ComputedRef, ref } from "vue";
import { onKeyStroke } from "@vueuse/core";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { isTouchDevice, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useContextMenuAlbumsAdd } from "@/composables/contextMenus/contextMenuAlbumsAdd";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import DropBox from "../modals/DropBox.vue";
import BackLinkButton from "./BackLinkButton.vue";
import OpenLeftMenu from "./OpenLeftMenu.vue";

const props = defineProps<{
	user: App.Http.Resources.Models.UserResource;
	title: string;
	rights: App.Http.Resources.Rights.RootAlbumRightsResource;
	config: {
		is_map_accessible: boolean;
		is_mod_frame_enabled: boolean;
		is_search_accessible: boolean;
		show_keybinding_help_button: boolean;
		back_button_enabled: boolean;
		back_button_text: string;
		back_button_url: string;
		login_button_position: string;
	};
	hasHidden: boolean;
}>();

const emits = defineEmits<{
	refresh: [];
	help: [];
}>();

//  'UPLOAD_PHOTO' => 'Upload Photo',
// 	'IMPORT_LINK' => 'Import from Link',
// 	'IMPORT_DROPBOX' => 'Import from Dropbox',
// 	'IMPORT_SERVER' => 'Import from Server',
// 	'NEW_ALBUM' => 'New Album',
// 	'NEW_TAG_ALBUM' => 'New Tag Album',
// 	'UPLOAD_TRACK' => 'Upload track',
// 	'DELETE_TRACK' => 'Delete track',
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();

const { dropbox_api_key } = storeToRefs(lycheeStore);
const { is_login_open, is_upload_visible, is_create_album_visible, is_create_tag_album_visible } = storeToRefs(togglableStore);

const router = useRouter();

const {
	toggleCreateAlbum,
	toggleCreateTagAlbum,
	isImportFromLinkOpen,
	toggleImportFromLink,
	isImportFromDropboxOpen,
	toggleImportFromDropbox,
	toggleUpload,
} = useGalleryModals(togglableStore);

const { addmenu, addMenu } = useContextMenuAlbumsAdd(
	{
		toggleUpload: toggleUpload,
		toggleCreateAlbum: toggleCreateAlbum,
		toggleImportFromLink: toggleImportFromLink,
		toggleImportFromDropbox: toggleImportFromDropbox,
		toggleCreateTagAlbum: toggleCreateTagAlbum,
	},
	dropbox_api_key,
);

const canUpload = computed(() => props.user.id !== null);
const isLoginLeft = computed(() => props.config.login_button_position === "left");

function openAddMenu(event: Event) {
	addmenu.value.show(event);
}

function openHelp() {
	emits("help");
}

function openSearch() {
	router.push({ name: "search" });
}

onKeyStroke("n", () => !shouldIgnoreKeystroke() && props.rights.can_upload && (is_create_album_visible.value = true));
onKeyStroke("u", () => !shouldIgnoreKeystroke() && props.rights.can_upload && (is_upload_visible.value = true));
onKeyStroke("/", () => !shouldIgnoreKeystroke() && props.config.is_search_accessible && openSearch());

// on key stroke escape:
// 1. lose focus
// 2. close modals
// 3. go back
onKeyStroke("escape", () => {
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

	togglableStore.left_menu_open = false;
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
			to: { name: "frame" },
			type: "link",
			icon: "pi pi-desktop",
			if: props.config.is_mod_frame_enabled,
		},
		{
			to: { name: "map" },
			type: "link",
			icon: "pi pi-map",
			if: props.config.is_map_accessible,
		},
		{
			icon: "pi pi-search",
			type: "fn",
			callback: openSearch,
			if: props.config.is_search_accessible,
		},
		{
			icon: "pi pi-sign-in",
			type: "fn",
			callback: togglableStore.toggleLogin,
			if: props.user.id === null && !isLoginLeft.value,
		},
		{
			icon: "pi pi-question-circle",
			type: "fn",
			callback: openHelp,
			if: !isTouchDevice() && props.user.id !== null && props.config.show_keybinding_help_button && document.body.scrollWidth > 800,
		},
		{
			icon: "pi pi-plus",
			type: "fn",
			callback: openAddMenu,
			if: props.rights.can_upload,
		},
		{
			icon: "pi pi-eye-slash",
			type: "fn",
			callback: () => (lycheeStore.are_nsfw_visible = false),
			if: isTouchDevice() && props.hasHidden && lycheeStore.are_nsfw_visible,
		},
		{
			icon: "pi pi-eye",
			type: "fn",
			callback: () => (lycheeStore.are_nsfw_visible = true),
			if: isTouchDevice() && props.hasHidden && !lycheeStore.are_nsfw_visible,
		},
	].filter((item) => item.if),
) as ComputedRef<MenuRight[]>;

// bubble up.
function refresh() {
	emits("refresh");
}
</script>
