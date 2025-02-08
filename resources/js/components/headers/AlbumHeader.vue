<template>
	<ImportFromLink v-if="canUpload" v-model:visible="isImportFromLinkOpen" :parent-id="props.album.id" @refresh="refresh" />
	<DropBox v-if="canUpload" v-model:visible="isImportFromDropboxOpen" :album-id="props.album.id" />
	<Toolbar class="w-full border-0 h-14" v-if="album">
		<template #start>
			<Button icon="pi pi-angle-left" class="mr-2 border-none" severity="secondary" text @click="goBack" />
		</template>

		<template #center>
			{{ album.title }}
		</template>

		<template #end>
			<Button
				v-tooltip.bottom="'Start slideshow'"
				icon="pi pi-play"
				class="border-none"
				severity="secondary"
				text
				@click="toggleSlideShow"
				v-if="props.album.photos.length > 0"
				label=""
			/>
			<router-link
				:to="{ name: 'frame-with-album', params: { albumid: props.album.id } }"
				v-if="props.config.is_mod_frame_enabled"
				class="hidden sm:block"
				v-tooltip="'Frame'"
			>
				<Button icon="pi pi-desktop" class="border-none" severity="secondary" text />
			</router-link>
			<router-link
				:to="{ name: 'map-with-album', params: { albumid: props.album.id } }"
				v-if="props.config.is_map_accessible && hasCoordinates"
				class="hidden sm:block"
			>
				<Button icon="pi pi-map" class="border-none" severity="secondary" text />
			</router-link>
			<Button
				icon="pi pi-search"
				class="border-none hidden sm:block"
				severity="secondary"
				text
				@click="openSearch"
				v-if="props.config.is_search_accessible"
			/>
			<Button icon="pi pi-plus" class="border-none" severity="secondary" text @click="openAddMenu" v-if="props.album.rights.can_upload" />
			<template v-if="props.album.rights.can_edit">
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
		</template>
	</Toolbar>
	<ContextMenu ref="addmenu" :model="addMenu" v-if="props.album.rights.can_upload">
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
	<input id="upload_track_file" type="file" name="fileElem" accept="application/x-gpx+xml" class="hidden" @change="uploadTrack" />
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import { computed } from "vue";
import { useRouter } from "vue-router";
import { onKeyStroke } from "@vueuse/core";
import ContextMenu from "primevue/contextmenu";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import ImportFromLink from "@/components/modals/ImportFromLink.vue";
import { useContextMenuAlbumAdd } from "@/composables/contextMenus/contextMenuAlbumAdd";
import Divider from "primevue/divider";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import AlbumService from "@/services/album-service";
import DropBox from "../modals/DropBox.vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";

const props = defineProps<{
	config: App.Http.Resources.GalleryConfigs.AlbumConfig;
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource;
	user: App.Http.Resources.Models.UserResource;
}>();

const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { dropbox_api_key } = storeToRefs(lycheeStore);
const { are_details_open, is_login_open, is_upload_visible, is_create_album_visible } = storeToRefs(togglableStore);

const hasCoordinates = computed(() => props.album.photos.find((photo) => photo.latitude !== null && photo.longitude !== null) !== undefined);

const { toggleCreateAlbum, isImportFromLinkOpen, toggleImportFromLink, isImportFromDropboxOpen, toggleImportFromDropbox, toggleUpload } =
	useGalleryModals(togglableStore);

const emits = defineEmits<{
	refresh: [];
	toggleSlideShow: [];
	toggleDetails: [];
}>();

function toggleUploadTrack() {
	document.getElementById("upload_track_file")?.click();
}

function toggleSlideShow() {
	emits("toggleSlideShow");
}

function toggleDetails() {
	are_details_open.value = !are_details_open.value;
	if (are_details_open.value) {
		emits("toggleDetails");
	}
}

function uploadTrack(e: Event) {
	const target: HTMLInputElement = e.target as HTMLInputElement;
	if (target.files === null) {
		return;
	}
	AlbumService.uploadTrack(props.album.id, target.files[0] as Blob);
}

function deleteTrack() {
	AlbumService.deleteTrack(props.album.id);
}

const { addmenu, addMenu, openAddMenu } = useContextMenuAlbumAdd(
	props.album,
	props.config,
	{
		toggleUpload,
		toggleCreateAlbum,
		toggleImportFromLink,
		toggleUploadTrack,
		deleteTrack,
		toggleImportFromDropbox,
	},
	dropbox_api_key,
);

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
	emits("refresh");
}

onKeyStroke("n", () => !shouldIgnoreKeystroke() && (is_create_album_visible.value = true));
onKeyStroke("u", () => !shouldIgnoreKeystroke() && (is_upload_visible.value = true));
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
