<template>
	<LoginModal v-if="user.id === null" v-model:visible="isLoginOpen" @logged-in="refresh" />
	<UploadPanel v-if="canUpload" v-model:visible="isUploadOpen" :album-id="null" @close="refresh" />
	<ImportFromServer v-if="canUpload" v-model:visible="isImportFromServerOpen" />
	<ImportFromLink v-if="canUpload" v-model:visible="isImportFromLinkOpen" :parent-id="null" />
	<AlbumCreateDialog v-if="canUpload" v-model:visible="isCreateAlbumOpen" :parent-id="null" />
	<AlbumCreateTagDialog v-if="canUpload" v-model:visible="isCreateTagAlbumOpen" />
	<Toolbar class="w-full border-0">
		<template #start>
			<Button v-if="user.id === null" icon="pi pi-sign-in" class="mr-2" severity="secondary" text @click="() => (isLoginOpen = true)" />
			<Button v-if="user.id" @click="openLeftMenu" icon="pi pi-bars" class="mr-2" severity="secondary" text />
			<!-- <Button v-if="initdata?.user" @click="logout" icon="pi pi-sign-out" class="mr-2" severity="secondary" text /> -->
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
			<!-- <SplitButton label="Save" :model="items"></SplitButton> -->
			<Button v-if="props.rights.can_upload" icon="pi pi-plus" severity="secondary" @click="openAddMenu" />
		</template>
	</Toolbar>
	<ContextMenu v-if="props.rights.can_upload" ref="addmenu" :model="addMenu">
		<template #item="{ item, props }">
			<a v-ripple v-bind="props.action" @click="item.callback">
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
import { useUploadOpen } from "@/composables/uploadOpen";
import { useCreateAlbumOpen } from "@/composables/createAlbumOpen";
import { useImportFromLinkOpen } from "@/composables/importFromLinkOpen";

const props = defineProps<{
	user: App.Http.Resources.Models.UserResource;
	title: string;
	rights: App.Http.Resources.Rights.RootAlbumRightsResource;
}>();

const emit = defineEmits<{
	(e: "refresh"): void;
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

const { isUploadOpen, toggleUpload } = useUploadOpen(false);
const { isCreateAlbumOpen, toggleCreateAlbum } = useCreateAlbumOpen(false);
const { isImportFromLinkOpen, toggleImportFromLink } = useImportFromLinkOpen(false);

const addmenu = ref();
const addMenu = ref([
	{
		label: "lychee.UPLOAD_PHOTO",
		icon: "pi pi-upload",
		callback: toggleUpload,
	},
	{
		label: "lychee.IMPORT_LINK",
		icon: "pi pi-link",
		callback: toggleImportFromLink,
	},
	// {
	// 	label: "lychee.IMPORT_DROPBOX",
	// 	icon: "pi pi-box",
	// 	callback: () => {},
	// },
	{
		label: "lychee.IMPORT_SERVER",
		icon: "pi pi-server",
		callback: () => (isImportFromServerOpen.value = true),
	},
	{
		label: "lychee.NEW_ALBUM",
		icon: "pi pi-folder",
		callback: toggleCreateAlbum,
	},
	{
		label: "lychee.NEW_TAG_ALBUM",
		icon: "pi pi-tags",
		callback: () => (isCreateTagAlbumOpen.value = true),
	},
]);
const isLoginOpen = defineModel("isLoginOpen", { type: Boolean, default: false });

const isCreateTagAlbumOpen = ref(false);
const isImportFromServerOpen = ref(false);
const canUpload = computed(() => props.user.id !== null);
const title = ref("Albums");

function openLeftMenu() {
	lycheeStore.toggleLeftMenu();
}

function openAddMenu(event: Event) {
	addmenu.value.show(event);
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

	lycheeStore.toggleLeftMenu();
});

// bubble up.
function refresh() {
	emit("refresh");
}
</script>
