<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div>
				<p class="p-9 text-center text-muted-color max-w-xl text-wrap">
					{{ confirmation }}<br />
					<span class="text-warning-700"><i class="pi pi-exclamation-triangle mr-2" />{{ $t("dialogs.delete_album.warning") }}</span>
				</p>
				<div class="flex">
					<Button severity="secondary" class="w-full border-none rounded-none rounded-bl-xl font-bold" @click="closeCallback">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button severity="danger" class="w-full border-none rounded-none rounded-br-xl font-bold" @click="execute">
						{{ $t("dialogs.button.delete") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { computed } from "vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
const toast = useToast();

const props = defineProps<{
	parentId: string | undefined;
	photo?: App.Http.Resources.Models.PhotoResource;
	photoIds?: string[];
	album?: App.Http.Resources.Models.ThumbAlbumResource;
	albumIds?: string[];
}>();

const visible = defineModel<boolean>("visible", { default: false });
const emits = defineEmits<{
	deleted: [];
}>();

const confirmation = computed(() => {
	if (props.photo || (props.photoIds && props.photoIds?.length > 0)) {
		return deleteConfirmationPhoto();
	}

	return deleteConfirmationAlbum();
});

function deleteConfirmationPhoto() {
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

	if (props.photo || (props.photoIds && props.photoIds?.length > 0)) {
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
		if (props.parentId === undefined) {
			AlbumService.clearAlbums();
		} else {
			AlbumService.clearCache(props.parentId);
		}
		emits("deleted");
	});
}

function executeDeletePhoto() {
	let photoDeletedIds = [];
	if (props.photo) {
		photoDeletedIds.push(props.photo.id);
	} else {
		photoDeletedIds = props.photoIds as string[];
	}
	PhotoService.delete(photoDeletedIds).then(() => {
		toast.add({
			severity: "success",
			summary: trans("dialogs.photo_delete.deleted"),
			life: 3000,
		});
		AlbumService.clearCache(props.parentId);
		emits("deleted");
	});
}
</script>
