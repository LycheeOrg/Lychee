<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<div v-if="titleMovedTo !== undefined">
				<p class="text-center text-muted">{{ confirmation }}</p>
			</div>
			<div v-else>
				<div v-if="error_no_target === false">
					<span class="font-bold">
						{{ question }}
					</span>
					<SearchTargetAlbum :album-ids="headIds" @selected="selected" @no-target="error_no_target = true" />
				</div>
				<div v-else>
					<p class="text-center text-muted">{{ $t("dialogs.move_album.no_album_target") }}</p>
				</div>
			</div>
		</template>
		<template #footer>
			<div v-if="titleMovedTo !== undefined" class="flex w-full gap-2">
				<UButton color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="close">
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="neutral" class="flex-1 justify-center font-bold" @click="execute">
					{{ $t("dialogs.button.move") }}
				</UButton>
			</div>
			<UButton
				v-else
				color="neutral"
				variant="soft"
				class="w-full justify-center font-bold"
				@click="
					() => {
						visible = false;
					}
				"
			>
				{{ $t("dialogs.button.cancel") }}
			</UButton>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import SearchTargetAlbum from "@/v8/components/forms/album/SearchTargetAlbum.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useRouter } from "vue-router";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { usePhotoStore } from "@/stores/PhotoState";

const props = defineProps<{
	album?: App.Http.Resources.Models.ThumbAlbumResource;
	albumIds?: string[];
	photo?: App.Http.Resources.Models.PhotoResource;
	photoIds?: string[];
}>();

const router = useRouter();
const { getParentId } = usePhotoRoute(router);
const visible = defineModel<boolean>("open", { default: false });
const photoStore = usePhotoStore();

const emits = defineEmits<{
	moved: [];
}>();

const toast = useAppToast();
const titleMovedTo = ref<string | undefined>(undefined);
const destination_id = ref<string | undefined | null>(undefined);
const error_no_target = ref(false);

function selected(target: App.Http.Resources.Models.TargetAlbumResource) {
	titleMovedTo.value = target.original;
	destination_id.value = target.id;
}

function close() {
	titleMovedTo.value = undefined;
	destination_id.value = undefined;
	visible.value = false;
}

const headIds = computed(() => {
	if (props.photo || (props.photoIds && props.photoIds?.length > 0)) {
		return undefined;
	}

	return props.albumIds;
});

const question = computed(() => {
	if (props.photoIds && props.photoIds?.length > 1) {
		return sprintf(trans("dialogs.move_photo.move_multiple"), props.photoIds?.length);
	}
	if (props.albumIds && props.albumIds?.length > 1) {
		return sprintf(trans("dialogs.move_album.move_to_multiple"), props.albumIds?.length);
	}
	if (props.photo) {
		return sprintf(trans("dialogs.move_photo.move_single"), props.photo.title);
	}
	return sprintf(trans("dialogs.move_album.move_to_single"), props.album?.title);
});

const confirmation = computed(() => {
	if (photoStore.isLoaded || props.photo || (props.photoIds && props.photoIds?.length > 0)) {
		return moveConfirmationPhoto();
	}
	return moveConfirmationAlbum();
});

function moveConfirmationPhoto() {
	if (photoStore.isLoaded) {
		return sprintf(trans("dialogs.move_photo.confirm"), photoStore!.photo!.title, titleMovedTo.value);
	}
	if (props.photo) {
		return sprintf(trans("dialogs.move_photo.confirm"), props.photo.title, titleMovedTo.value);
	}
	return sprintf(trans("dialogs.move_photo.confirm_multiple"), props.photoIds?.length, titleMovedTo.value);
}

function moveConfirmationAlbum() {
	if (props.album) {
		return sprintf(trans("dialogs.move_album.confirm_single"), props.album.title, titleMovedTo.value);
	}
	return sprintf(trans("dialogs.move_album.confirm_multiple"), titleMovedTo.value);
}

function execute() {
	visible.value = false;
	if (photoStore.isLoaded || props.photo || (props.photoIds && props.photoIds?.length > 0)) {
		return executeMovePhoto();
	}
	executeMoveAlbum();
}

function executeMoveAlbum() {
	if (destination_id.value === undefined) {
		return;
	}
	let albumMovedIds = [];
	if (props.album) {
		albumMovedIds.push(props.album.id);
	} else {
		albumMovedIds = props.albumIds as string[];
	}

	AlbumService.move(destination_id.value, albumMovedIds).then(() => {
		AlbumService.clearCache(destination_id.value);
		toast.add({
			severity: "success",
			summary: sprintf(trans("dialogs.move_album.moved_details"), titleMovedTo.value),
			life: 3000,
		});
		AlbumService.clearCache(destination_id.value);
		for (const id in albumMovedIds) {
			AlbumService.clearCache(id);
		}
		if (getParentId() === undefined) {
			AlbumService.clearAlbums();
		} else {
			AlbumService.clearCache(getParentId());
		}
		close();
		emits("moved");
	});
}

function executeMovePhoto() {
	if (destination_id.value === undefined) {
		return;
	}
	let photoMovedIds = [];
	if (photoStore.isLoaded) {
		photoMovedIds.push(photoStore!.photo!.id);
	} else if (props.photo) {
		photoMovedIds.push(props.photo.id);
	} else {
		photoMovedIds = props.photoIds as string[];
	}
	PhotoService.move({ from_id: getParentId() ?? null, album_id: destination_id.value, photo_ids: photoMovedIds }).then(() => {
		toast.add({
			severity: "success",
			summary: sprintf(trans("dialogs.move_photo.moved"), titleMovedTo.value),
			life: 3000,
		});
		// Clear the cache for the current album and the destination album
		AlbumService.clearCache(getParentId());
		AlbumService.clearCache(destination_id.value);

		// RESET !
		close();
		emits("moved");
	});
}
</script>
