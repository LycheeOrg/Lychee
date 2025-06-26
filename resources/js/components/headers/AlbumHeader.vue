<template>
	<ImportFromLink v-if="canUpload" v-model:visible="is_import_from_link_open" @refresh="emits('refresh')" />
	<DropBox v-if="canUpload" v-model:visible="is_import_from_dropbox_open" :album-id="props.album.id" />
	<Toolbar
		class="w-full border-0 transition-all duration-100 ease-in-out"
		:class="{
			'max-h-14': !is_full_screen,
			'max-h-0': is_full_screen,
		}"
		v-if="album"
	>
		<template #start>
			<GoBack @go-back="emits('goBack')" />
		</template>

		<template #center>
			{{ album.title }}
		</template>

		<template #end>
			<router-link
				:to="{ name: 'favourites' }"
				v-if="is_favourite_enabled && (favourites.photos?.length ?? 0) > 0"
				class="hidden sm:block"
				v-tooltip.bottom="'Favourites'"
			>
				<Button icon="pi pi-heart" class="border-none" severity="secondary" text />
			</router-link>
			<Button
				icon="pi pi-search"
				class="border-none hidden sm:block"
				severity="secondary"
				text
				@click="emits('openSearch')"
				v-if="props.config.is_search_accessible"
			/>
			<Button icon="pi pi-plus" class="border-none" severity="secondary" text @click="openAddMenu" v-if="props.album.rights.can_upload" />
			<template v-if="props.album.rights.can_edit">
				<Button
					:icon="is_album_edit_open ? 'pi pi-angle-up' : 'pi pi-angle-down'"
					severity="secondary"
					:class="{ 'ltr:mr-2 rtl:ml-2 border-none': true, 'text-primary-400': is_album_edit_open }"
					text
					@click="emits('toggleEdit')"
				/>
			</template>
		</template>
	</Toolbar>
	<ContextMenu ref="addmenu" :model="addMenu" v-if="props.album.rights.can_upload">
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
	<input id="upload_track_file" type="file" name="fileElem" accept="application/x-gpx+xml" class="hidden" @change="uploadTrack" />
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import { computed } from "vue";
import ContextMenu from "primevue/contextmenu";
import ImportFromLink from "@/components/modals/ImportFromLink.vue";
import { useContextMenuAlbumAdd } from "@/composables/contextMenus/contextMenuAlbumAdd";
import Divider from "primevue/divider";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import AlbumService from "@/services/album-service";
import DropBox from "../modals/DropBox.vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useFavouriteStore } from "@/stores/FavouriteState";
import { useLtRorRtL } from "@/utils/Helpers";
import GoBack from "./GoBack.vue";

const props = defineProps<{
	config: App.Http.Resources.GalleryConfigs.AlbumConfig;
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource;
	user: App.Http.Resources.Models.UserResource;
}>();

const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const favourites = useFavouriteStore();
const { isLTR } = useLtRorRtL();

const { dropbox_api_key, is_favourite_enabled } = storeToRefs(lycheeStore);
const { is_album_edit_open, is_full_screen } = storeToRefs(togglableStore);

const { toggleCreateAlbum, is_import_from_link_open, toggleImportFromLink, is_import_from_dropbox_open, toggleImportFromDropbox, toggleUpload } =
	useGalleryModals(togglableStore);

const emits = defineEmits<{
	refresh: [];
	toggleEdit: [];
	goBack: [];
	openSearch: [];
}>();

function toggleUploadTrack() {
	document.getElementById("upload_track_file")?.click();
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

const canUpload = computed(() => props.user.id !== null && props.album.rights.can_upload === true);
</script>
