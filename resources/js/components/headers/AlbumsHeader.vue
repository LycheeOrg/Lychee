<template>
	<LoginModal v-if="user.id === null" v-model:visible="isLoginOpen" @logged-in="refresh" />
	<UploadPanel v-if="canUpload" v-model:visible="isUploadOpen" :album-id="null" @close="refresh" />
	<ImportFromServer v-if="canUpload" v-model:visible="isImportFromServerOpen" />
	<ImportFromLink v-if="canUpload" v-model:visible="isImportFromLinkOpen" :parent-id="null" />
	<AlbumCreateDialog v-if="canUpload" v-model:visible="isCreateAlbumOpen" :parent-id="null" />
	<AlbumCreateTagDialog v-if="canUpload" v-model:visible="isCreateTagAlbumOpen" />
	<Toolbar class="w-full border-0">
		<template #start>
			<BackLinkButton v-if="user.id === null && !isLoginLeft" :config="props.config" />
			<Button v-if="user.id === null && isLoginLeft" icon="pi pi-sign-in" class="mr-2" severity="secondary" text @click="isLoginOpen = true" />
			<Button v-if="user.id" @click="openLeftMenu" icon="pi pi-bars" class="mr-2" severity="secondary" text />
			<!-- <Button v-if="initdata?.user" @click="logout" icon="pi pi-sign-out" class="mr-2 border-none" severity="info" text /> -->
		</template>

		<template #center>
			{{ title }}
		</template>

		<template #end>
			<!-- <IconField>
				<InputIcon>
					<i class="pi pi-search" />
				</InputIcon>
				<InputText placeholder="Search" />
			</IconField> -->
			<BackLinkButton v-if="user.id === null && isLoginLeft" :config="props.config" />
			<!-- <SplitButton label="Save" :model="items"></SplitButton> -->
			<Button
				v-if="user.id !== null && props.config.show_keybinding_help_button"
				icon="pi pi-question-circle"
				severity="secondary"
				text
				@click="openHelp"
			/>
			<Button v-if="props.rights.can_upload" icon="pi pi-plus" severity="secondary" text @click="openAddMenu" />
			<Button v-if="user.id === null && !isLoginLeft" icon="pi pi-sign-in" class="mr-2" severity="secondary" text @click="isLoginOpen = true" />
		</template>
	</Toolbar>
	<ContextMenu v-if="props.rights.can_upload" ref="addmenu" :model="addMenu">
		<template #item="{ item, props }">
			<Divider v-if="item.is_divider" />
			<a v-else v-ripple v-bind="props.action" @click="item.callback">
				<span :class="item.icon" />
				<span class="ml-2">{{ $t(item.label) }}</span>
			</a>
		</template>
	</ContextMenu>
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import UploadPanel from "@/components/modals/UploadPanel.vue";
import ContextMenu from "primevue/contextmenu";
import ImportFromServer from "@/components/modals/ImportFromServer.vue";
import AlbumCreateDialog from "@/components/forms/album/AlbumCreateDialog.vue";
import AlbumCreateTagDialog from "@/components/forms/album/AlbumCreateTagDialog.vue";
import { computed, ref } from "vue";
import { onKeyStroke } from "@vueuse/core";
import { useLycheeStateStore } from "@/stores/LycheeState";
import LoginModal from "@/components/modals/LoginModal.vue";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import ImportFromLink from "@/components/modals/ImportFromLink.vue";
import { storeToRefs } from "pinia";
import BackLinkButton from "./BackLinkButton.vue";
import { useContextMenuAlbumsAdd } from "@/composables/contextMenus/contextMenuAlbumsAdd";
import Divider from "primevue/divider";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";

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
}>();

const emit = defineEmits<{
	(e: "refresh"): void;
	(e: "help"): void;
	//   (e: 'update', value: string): void
}>();

// 'UPLOAD_PHOTO' => 'Upload Photo',
// 	'IMPORT_LINK' => 'Import from Link',
// 	'IMPORT_DROPBOX' => 'Import from Dropbox',
// 	'IMPORT_SERVER' => 'Import from Server',
// 	'NEW_ALBUM' => 'New Album',
// 	'NEW_TAG_ALBUM' => 'New Tag Album',
// 	'UPLOAD_TRACK' => 'Upload track',
// 	'DELETE_TRACK' => 'Delete track',
const lycheeStore = useLycheeStateStore();
const { left_menu_open } = storeToRefs(lycheeStore);
const openLeftMenu = () => (left_menu_open.value = !left_menu_open.value);

const {
	isCreateAlbumOpen,
	toggleCreateAlbum,
	isDeleteVisible,
	toggleDelete,
	isMergeAlbumVisible,
	toggleMergeAlbum,
	isMoveVisible,
	toggleMove,
	isRenameVisible,
	toggleRename,
	isShareAlbumVisible,
	toggleShareAlbum,
	isImportFromLinkOpen,
	toggleImportFromLink,
	isUploadOpen,
	toggleUpload,
} = useGalleryModals();

const { addmenu, addMenu, isImportFromServerOpen, isCreateTagAlbumOpen } = useContextMenuAlbumsAdd({
	toggleUpload: toggleUpload,
	toggleCreateAlbum: toggleCreateAlbum,
	toggleImportFromLink: toggleImportFromLink,
});

const isLoginOpen = defineModel("isLoginOpen", { type: Boolean, default: false });

const canUpload = computed(() => props.user.id !== null);
const title = ref("Albums");
const isLoginLeft = computed(() => props.config.login_button_position === "left");

function openAddMenu(event: Event) {
	addmenu.value.show(event);
}

function openHelp() {
	emit("help");
}

onKeyStroke("n", () => !shouldIgnoreKeystroke() && props.rights.can_upload && (isCreateAlbumOpen.value = true));
onKeyStroke("u", () => !shouldIgnoreKeystroke() && props.rights.can_upload && (isUploadOpen.value = true));
onKeyStroke("l", () => !shouldIgnoreKeystroke() && props.user.id === null && (isLoginOpen.value = true));

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
	if (isLoginOpen.value) {
		isLoginOpen.value = false;
		return;
	}

	if (isUploadOpen.value) {
		isUploadOpen.value = false;
		return;
	}
	if (isCreateAlbumOpen.value) {
		isCreateAlbumOpen.value = false;
		return;
	}
	if (isCreateTagAlbumOpen.value) {
		isCreateTagAlbumOpen.value = false;
		return;
	}
	if (isImportFromServerOpen.value) {
		isImportFromServerOpen.value = false;
		return;
	}

	lycheeStore.left_menu_open = false;
});

// bubble up.
function refresh() {
	emit("refresh");
}
</script>
