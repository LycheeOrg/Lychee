<template>
	<LoginModal v-if="props.user.id === null" v-model:visible="isLoginOpen" @logged-in="refresh" />
	<UploadPanel v-if="canUpload" v-model:visible="isUploadOpen" @close="isUploadOpen = false" :album-id="props.album.id" />
	<ImportFromLink v-if="canUpload" v-model:visible="isImportLinkOpen" :parent-id="props.album.id" @refresh="refresh" />
	<AlbumCreateDialog
		v-if="canUpload && config.is_model_album"
		v-model:visible="isCreateAlbumOpen"
		v-model:parent-id="props.album.id"
		@close="isCreateAlbumOpen = false"
	/>
	<Toolbar class="w-full border-0" v-if="album">
		<template #start>
			<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="goBack" />
			<!-- <Button v-if="user?.id" @click="openLeftMenu" icon="pi pi-bars" class="mr-2" severity="secondary" text /> -->
			<!-- <Button v-if="initdata?.user" @click="logout" icon="pi pi-sign-out" class="mr-2" severity="secondary" text /> -->
		</template>

		<template #center>
			{{ album.title }}
		</template>

		<template #end>
			<!-- <IconField>
				<InputIcon>
					<i class="pi pi-search" />
				</InputIcon>
				<InputText placeholder="Search" />
			</IconField> -->
			<Button icon="pi pi-plus" severity="secondary" @click="openAddMenu" />
			<template v-if="album.rights.can_edit">
				<Button v-if="!areDetailsOpen" icon="pi pi-angle-down" severity="secondary" class="mr-2" text @click="toggleDetails" />
				<Button v-if="areDetailsOpen" icon="pi pi-angle-up" severity="secondary" class="mr-2 text-primary-400" text @click="toggleDetails" />
			</template>
			<!-- <SplitButton label="Save" :model="items"></SplitButton> -->
		</template>
	</Toolbar>
	<ContextMenu ref="addmenu" :model="addMenu">
		<template #item="{ item, props }">
			<a v-ripple v-bind="props.action" @click="item.callback">
				<span :class="item.icon" />
				<span class="ml-2">{{ $t(item.label) }}</span>
			</a>
		</template>
	</ContextMenu>
</template>
<script setup lang="ts">
import LoginModal from "@/components/modals/LoginModal.vue";
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import { Ref, computed, ref } from "vue";
import { useRouter } from "vue-router";
import UploadPanel from "@/components/modals/UploadPanel.vue";
import { onKeyStroke } from "@vueuse/core";
import AlbumCreateDialog from "@/components/forms/album/AlbumCreateDialog.vue";
import ContextMenu from "primevue/contextmenu";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import ImportFromLink from "@/components/modals/ImportFromLink.vue";

const props = defineProps<{
	config: App.Http.Resources.GalleryConfigs.AlbumConfig;
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource;
	user: App.Http.Resources.Models.UserResource;
}>();

const areDetailsOpen = defineModel("areDetailsOpen", { default: false });
const isLoginOpen = ref(false);
const emit = defineEmits<{
	(e: "refresh"): void;
	//   (e: 'update', value: string): void
}>();

const addmenu = ref();
const addMenu = ref([
	{
		label: "lychee.UPLOAD_PHOTO",
		icon: "pi pi-upload",
		callback: () => (isUploadOpen.value = true),
	},
	{
		label: "lychee.IMPORT_LINK",
		icon: "pi pi-link",
		callback: () => (isImportLinkOpen.value = true),
	},
	// {
	// 	label: "lychee.IMPORT_DROPBOX",
	// 	icon: "pi pi-box",
	// 	callback: () => {},
	// },
	{
		label: "lychee.NEW_ALBUM",
		icon: "pi pi-folder",
		callback: () => (isCreateAlbumOpen.value = true),
	},
]);

const isUploadOpen = ref(false);
const isCreateAlbumOpen = ref(false);
const isImportLinkOpen = ref(false);
const router = useRouter();
const user = ref(undefined) as Ref<undefined | App.Http.Resources.Models.UserResource>;
const canUpload = computed(() => user.value?.id !== null && props.album.rights.can_upload === true);

onKeyStroke("n", () => !shouldIgnoreKeystroke() && (isCreateAlbumOpen.value = true));
onKeyStroke("u", () => !shouldIgnoreKeystroke() && (isUploadOpen.value = true));
onKeyStroke("i", () => !shouldIgnoreKeystroke() && toggleDetails());

function goBack() {
	areDetailsOpen.value = false;

	if (props.config.is_model_album === true && (props.album as App.Http.Resources.Models.AlbumResource | null)?.parent_id !== null) {
		router.push({ name: "album", params: { albumid: (props.album as App.Http.Resources.Models.AlbumResource | null)?.parent_id } });
	} else {
		router.push({ name: "gallery" });
	}
}

function openAddMenu(event: Event) {
	addmenu.value.show(event);
}

function toggleDetails() {
	areDetailsOpen.value = !areDetailsOpen.value;
}

// bubble up.
function refresh() {
	emit("refresh");
}

// on key stroke escape:
// 1. lose focus
// 2. close modals
// 3. go back
onKeyStroke("Escape", () => {
	// 1. lose focus
	if (shouldIgnoreKeystroke() && document.activeElement instanceof HTMLElement) {
		document.activeElement.blur();
		return;
	}

	if (areDetailsOpen.value) {
		toggleDetails();
		return;
	}

	goBack();
});
</script>
