<template>
	<div class="max-w-3xl">
		<p class="mb-4 text-center">
			{{ confirm }}<br />
			<span class="text-warning">
				<UIcon name="lucide:triangle-alert" class="ltr:mr-2 rtl:ml-2" />{{ $t("dialogs.delete_album.warning") }}
			</span>
		</p>
		<UFormField :label="typeToConfirmLabel" class="mb-4">
			<UInput v-model="confirmText" :placeholder="albumStore.album?.title" class="w-full" />
		</UFormField>
		<UButton color="error" variant="ghost" class="font-bold w-full justify-center" :disabled="!isConfirmed" @click="execute">
			{{ $t("dialogs.delete_album.delete") }}
		</UButton>
	</div>
</template>

<script setup lang="ts">
import { useRouter } from "vue-router";
import AlbumService from "@/services/album-service";
import { sprintf } from "sprintf-js";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotosStore } from "@/stores/PhotosState";
import { computed, ref } from "vue";
import { trans } from "laravel-vue-i18n";

const albumStore = useAlbumStore();
const photosStore = usePhotosStore();

const router = useRouter();

const emits = defineEmits<{
	deleted: [];
}>();

const confirm = computed(() => {
	if (albumStore.modelAlbum) {
		return sprintf(trans("dialogs.delete_album.confirmation"), albumStore.album?.title);
	}
	return sprintf(trans("dialogs.delete_album.confirmation_tag"), albumStore.album?.title);
});

const confirmText = ref("");
const typeToConfirmLabel = computed(() => sprintf(trans("dialogs.delete_album.type_to_confirm"), albumStore.album?.title));
const isConfirmed = computed(() => confirmText.value !== "" && confirmText.value === albumStore.album?.title);

function execute() {
	if (albumStore.album === undefined || !isConfirmed.value) {
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
