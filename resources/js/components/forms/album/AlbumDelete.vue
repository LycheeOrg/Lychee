<template>
	<Card class="sm:p-4 xl:px-9 max-w-3xl">
		<template #content>
			<p class="mb-4 text-center">
				{{ sprintf($t("dialogs.delete_album.confirmation"), albumStore.album?.title) }}<br />
				<span class="text-warning-700">
					<i class="pi pi-exclamation-triangle ltr:mr-2 rtl:ml-2" />{{ $t("dialogs.delete_album.warning") }}
				</span>
			</p>
			<Button class="text-danger-800 font-bold hover:text-white hover:bg-danger-800 w-full bg-transparent border-none" @click="execute">
				{{ $t("dialogs.delete_album.delete") }}
			</Button>
		</template>
	</Card>
</template>

<script setup lang="ts">
import { useRouter } from "vue-router";
import Button from "primevue/button";
import Card from "primevue/card";
import AlbumService from "@/services/album-service";
import { sprintf } from "sprintf-js";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotosStore } from "@/stores/PhotosState";

const albumStore = useAlbumStore();
const photosStore = usePhotosStore();

const router = useRouter();

const emits = defineEmits<{
	deleted: [];
}>();

function execute() {
	if (albumStore.album === undefined) {
		return;
	}

	AlbumService.delete([albumStore.album.id]).then(() => {
		emits("deleted");
		const isModelAlbum = albumStore.config?.is_model_album ?? false;
		const modelAlbum = albumStore.modelAlbum;
		albumStore.reset();
		photosStore.reset();

		if (isModelAlbum) {
			AlbumService.clearCache(modelAlbum?.parent_id);
			if (modelAlbum?.parent_id) {
				router.push(`/gallery/${modelAlbum?.parent_id}`);
			} else {
				router.push("/gallery");
			}
		} else {
			AlbumService.clearAlbums();
			router.push("/gallery");
		}
	});
}
</script>
