<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<p class="text-center text-muted max-w-xl text-wrap">
				{{ confirmation }}<br />
				<span class="text-warning flex items-center justify-center gap-1 mt-1">
					<UIcon name="prime:exclamation-triangle" />{{ $t("dialogs.delete_album.warning") }}
				</span>
			</p>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton
					color="neutral"
					variant="soft"
					class="flex-1 justify-center font-bold"
					@click="
						() => {
							visible = false;
						}
					"
				>
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="error" class="flex-1 justify-center font-bold" @click="execute">
					{{ $t("dialogs.button.delete") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { computed } from "vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useRouter } from "vue-router";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { usePhotoStore } from "@/stores/PhotoState";

const toast = useAppToast();
const props = defineProps<{
	photo?: App.Http.Resources.Models.PhotoResource;
	photoIds?: string[];
	album?: App.Http.Resources.Models.ThumbAlbumResource;
	albumIds?: string[];
}>();

const router = useRouter();
const { getParentId } = usePhotoRoute(router);
const photoStore = usePhotoStore();

const visible = defineModel<boolean>("open", { default: false });
const emits = defineEmits<{
	deleted: [];
}>();

const confirmation = computed(() => {
	if (photoStore.isLoaded || props.photo || (props.photoIds && props.photoIds?.length > 0)) {
		return deleteConfirmationPhoto();
	}

	return deleteConfirmationAlbum();
});

function deleteConfirmationPhoto() {
	if (photoStore.isLoaded) {
		return sprintf(trans("dialogs.photo_delete.confirm"), photoStore!.photo!.title);
	}
	if (props.photo) {
		return sprintf(trans("dialogs.photo_delete.confirm"), props.photo.title);
	}
	return sprintf(trans("dialogs.photo_delete.confirm_multiple"), props.photoIds?.length);
}

function deleteConfirmationAlbum() {
	if (props.album) {
		return sprintf(trans("dialogs.delete_album.confirmation"), props.album.title);
	}
	return sprintf(trans("dialogs.delete_album.confirmation_multiple"), props.albumIds?.length);
}

function execute() {
	visible.value = false;

	if (photoStore.isLoaded || props.photo || (props.photoIds && props.photoIds?.length > 0)) {
		return executeDeletePhoto();
	}

	return executeDeleteAlbum();
}

function executeDeleteAlbum() {
	let albumDeletedIds = [];
	if (props.album) {
		albumDeletedIds.push(props.album.id);
	} else {
		albumDeletedIds = props.albumIds as string[];
	}

	AlbumService.delete(albumDeletedIds).then(() => {
		if (getParentId() === undefined) {
			AlbumService.clearAlbums();
		} else {
			AlbumService.clearCache(getParentId());
		}
		emits("deleted");
	});
}

function executeDeletePhoto() {
	let photoDeletedIds = [];
	if (photoStore.isLoaded) {
		photoDeletedIds.push(photoStore!.photo!.id);
	} else if (props.photo) {
		photoDeletedIds.push(props.photo.id);
	} else {
		photoDeletedIds = props.photoIds as string[];
	}
	PhotoService.delete(photoDeletedIds, getParentId() ?? "unsorted").then(() => {
		toast.add({
			severity: "success",
			summary: trans("dialogs.photo_delete.deleted"),
			life: 3000,
		});
		AlbumService.clearCache(getParentId());
		emits("deleted");
	});
}
</script>
