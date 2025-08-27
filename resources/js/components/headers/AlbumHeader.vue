<template>
	<Toolbar
		v-if="album"
		class="w-full border-0 transition-all duration-100 ease-in-out"
		:class="{
			'max-h-14': !is_full_screen,
			'max-h-0': is_full_screen,
		}"
	>
		<template #start>
			<GoBack @go-back="emits('goBack')" />
		</template>

		<template #center>
			{{ album.title }}
		</template>

		<template #end>
			<router-link
				v-if="is_favourite_enabled && (favourites.photos?.length ?? 0) > 0"
				v-tooltip.bottom="'Favourites'"
				:to="{ name: 'favourites' }"
				class="hidden sm:block"
			>
				<Button icon="pi pi-heart" class="border-none" severity="secondary" text />
			</router-link>
			<Button
				v-if="props.config.is_search_accessible"
				icon="pi pi-search"
				class="border-none hidden sm:inline-flex"
				severity="secondary"
				text
				@click="emits('openSearch')"
			/>
			<Button v-if="props.album.rights.can_upload" icon="pi pi-plus" class="border-none" severity="secondary" text @click="openAddMenu" />
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
	<ContextMenu v-if="props.album.rights.can_upload" ref="addmenu" :model="addMenu">
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
import ContextMenu from "primevue/contextmenu";
import { useContextMenuAlbumAdd } from "@/composables/contextMenus/contextMenuAlbumAdd";
import Divider from "primevue/divider";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import AlbumService from "@/services/album-service";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useFavouriteStore } from "@/stores/FavouriteState";
import GoBack from "./GoBack.vue";
import { computed } from "vue";

const props = defineProps<{
	config: App.Http.Resources.GalleryConfigs.AlbumConfig;
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource;
	user: App.Http.Resources.Models.UserResource;
}>();

const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const favourites = useFavouriteStore();

const { dropbox_api_key, is_favourite_enabled } = storeToRefs(lycheeStore);
const { is_album_edit_open, is_full_screen } = storeToRefs(togglableStore);

const { toggleCreateAlbum, toggleImportFromLink, toggleImportFromDropbox, toggleUpload, toggleImportFromServer } = useGalleryModals(togglableStore);

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

const is_owner = computed(() => props.album.rights.can_import_from_server);

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
		toggleImportFromServer,
	},
	dropbox_api_key,
	is_owner,
);
</script>
