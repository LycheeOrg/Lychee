<template>
	<UploadPanel v-if="canUpload" v-model:visible="isUploadOpen" @close="isUploadOpen = false" :album-id="props.album.id" />
	<ImportFromLink v-if="canUpload" v-model:visible="isImportFromLinkOpen" :parent-id="props.album.id" @refresh="refresh" />
	<AlbumCreateDialog
		v-if="canUpload && config.is_model_album"
		v-model:visible="isCreateAlbumOpen"
		v-model:parent-id="props.album.id"
		@close="isCreateAlbumOpen = false"
	/>
	<Toolbar class="w-full border-0 h-14" v-if="album">
		<template #start>
			<Button icon="pi pi-angle-left" class="mr-2 border-none" severity="secondary" text @click="goBack" />
			<!-- <Button v-if="user?.id" @click="openLeftMenu" icon="pi pi-bars" class="mr-2" severity="info" text /> -->
			<!-- <Button v-if="initdata?.user" @click="logout" icon="pi pi-sign-out" class="mr-2" severity="info" text /> -->
		</template>

		<template #center>
			{{ album.title }}
		</template>

		<template #end>
			<router-link :to="{ name: 'frame-with-album', params: { albumid: props.album.id } }" v-if="props.config.is_mod_frame_enabled">
				<Button icon="pi pi-desktop" class="border-none" severity="secondary" text />
			</router-link>
			<router-link
				:to="{ name: 'map-with-album', params: { albumid: props.album.id } }"
				v-if="props.config.is_map_accessible && hasCoordinates"
			>
				<Button icon="pi pi-map" class="border-none" severity="secondary" text />
			</router-link>
			<Button icon="pi pi-search" class="border-none" severity="secondary" text @click="openSearch" v-if="props.config.is_search_accessible" />
			<Button icon="pi pi-plus" class="border-none" severity="secondary" text @click="openAddMenu" v-if="album.rights.can_upload" />
			<template v-if="album.rights.can_edit">
				<Button v-if="!are_details_open" icon="pi pi-angle-down" severity="secondary" text class="mr-2 border-none" @click="toggleDetails" />
				<Button
					v-if="are_details_open"
					icon="pi pi-angle-up"
					severity="secondary"
					class="mr-2 text-primary-400 border-none"
					text
					@click="toggleDetails"
				/>
			</template>
			<!-- <SplitButton label="Save" :model="items"></SplitButton> -->
		</template>
	</Toolbar>
	<ContextMenu ref="addmenu" :model="addMenu" v-if="album.rights.can_upload">
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
import LoginModal from "@/components/modals/LoginModal.vue";
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import UploadPanel from "@/components/modals/UploadPanel.vue";
import { onKeyStroke } from "@vueuse/core";
import AlbumCreateDialog from "@/components/forms/album/AlbumCreateDialog.vue";
import ContextMenu from "primevue/contextmenu";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import ImportFromLink from "@/components/modals/ImportFromLink.vue";
import { useContextMenuAlbumAdd } from "@/composables/contextMenus/contextMenuAlbumAdd";
import Divider from "primevue/divider";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const props = defineProps<{
	config: App.Http.Resources.GalleryConfigs.AlbumConfig;
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource;
	user: App.Http.Resources.Models.UserResource;
}>();

const toggleDetails = () => (are_details_open.value = !are_details_open.value);
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { are_details_open, is_login_open } = storeToRefs(lycheeStore);

const hasCoordinates = computed(() => props.album.photos.find((photo) => photo.latitude !== null && photo.longitude !== null) !== undefined);

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

const emit = defineEmits<{
	(e: "refresh"): void;
	//   (e: 'update', value: string): void
}>();

const { addmenu, addMenu, openAddMenu } = useContextMenuAlbumAdd({ toggleUpload, toggleCreateAlbum, toggleImportFromLink });

const router = useRouter();
const canUpload = computed(() => props.user.id !== null && props.album.rights.can_upload === true);

function goBack() {
	are_details_open.value = false;

	if (props.config.is_model_album === true && (props.album as App.Http.Resources.Models.AlbumResource | null)?.parent_id !== null) {
		router.push({ name: "album", params: { albumid: (props.album as App.Http.Resources.Models.AlbumResource | null)?.parent_id } });
	} else {
		router.push({ name: "gallery" });
	}
}

function openSearch() {
	router.push({ name: "search-with-album", params: { albumid: props.album.id } });
}

// bubble up.
function refresh() {
	emit("refresh");
}

onKeyStroke("n", () => !shouldIgnoreKeystroke() && (isCreateAlbumOpen.value = true));
onKeyStroke("u", () => !shouldIgnoreKeystroke() && (isUploadOpen.value = true));
onKeyStroke("i", () => !shouldIgnoreKeystroke() && toggleDetails());
onKeyStroke("l", () => !shouldIgnoreKeystroke() && props.user.id === null && (is_login_open.value = true));
onKeyStroke("/", () => !shouldIgnoreKeystroke() && props.config.is_search_accessible && openSearch());
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

	if (are_details_open.value) {
		toggleDetails();
		return;
	}

	goBack();
});
</script>
