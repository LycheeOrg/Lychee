<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div v-if="titleMovedTo !== undefined">
				<p class="p-9 text-center text-muted-color">{{ confirmation }}</p>
				<div class="flex">
					<Button severity="secondary" class="font-bold w-full border-none rounded-none rounded-bl-xl" @click="close">
						{{ $t("lychee.CANCEL") }}
					</Button>
					<Button severity="contrast" class="font-bold w-full border-none rounded-none rounded-br-xl" @click="execute">
						{{ $t("lychee.MOVE") }}
					</Button>
				</div>
			</div>
			<div v-else>
				<div class="p-9">
					<span class="font-bold">
						{{ question }}
					</span>
					<SearchTargetAlbum :album-id="parentId" @selected="selected" />
				</div>
				<Button class="w-full font-bold rounded-none rounded-bl-xl rounded-br-xl border-none" severity="secondary" @click="closeCallback">
					{{ $t("lychee.CANCEL") }}
				</Button>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import SearchTargetAlbum from "@/components/forms/album/SearchTargetAlbum.vue";
import { useToast } from "primevue/usetoast";
import Button from "primevue/button";
import Dialog from "primevue/dialog";

const props = defineProps<{
	parentId: string | undefined;
	album?: App.Http.Resources.Models.ThumbAlbumResource;
	albumIds?: string[];
	photo?: App.Http.Resources.Models.PhotoResource;
	photoIds?: string[];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const emits = defineEmits<{
	moved: [];
}>();

const toast = useToast();
const titleMovedTo = ref(undefined as string | undefined);
const destination_id = ref(undefined as string | undefined | null);

function selected(target: App.Http.Resources.Models.TargetAlbumResource) {
	titleMovedTo.value = target.original;
	destination_id.value = target.id;
}

function close() {
	titleMovedTo.value = undefined;
	destination_id.value = undefined;
	visible.value = false;
}

const question = computed(() => {
	if (props.photoIds && props.photoIds?.length > 1) {
		return sprintf("Move %d photos to:", props.photoIds?.length);
	}
	if (props.albumIds && props.albumIds?.length > 1) {
		return sprintf("Move %d albums to:", props.albumIds?.length);
	}
	if (props.photo) {
		return sprintf("Move %s to:", props.photo.title);
	}
	return sprintf("Move %s to:", props.album?.title);
});

const confirmation = computed(() => {
	if (props.photo || (props.photoIds && props.photoIds?.length > 0)) {
		return moveConfirmationPhoto();
	}
	return moveConfirmationAlbum();
});

function moveConfirmationPhoto() {
	if (props.photo) {
		return sprintf("Move %s to %s.", props.photo.title, titleMovedTo.value);
	}
	return sprintf("Move %d photos to %s.", props.photoIds?.length, titleMovedTo.value);
}

function moveConfirmationAlbum() {
	if (props.album) {
		return sprintf(trans("lychee.ALBUM_MOVE"), props.album.title, titleMovedTo.value);
	}
	return sprintf(trans("lychee.ALBUMS_MOVE"), titleMovedTo.value);
}

function execute() {
	visible.value = false;
	if (props.photo || (props.photoIds && props.photoIds?.length > 0)) {
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
			summary: "Album(s) moved to " + titleMovedTo.value,
			life: 3000,
		});
		AlbumService.clearCache(destination_id.value);
		for (let id in albumMovedIds) {
			AlbumService.clearCache(id);
		}
		if (props.parentId === undefined) {
			AlbumService.clearAlbums();
		} else {
			AlbumService.clearCache(props.parentId);
		}
		emits("moved");
	});
}

function executeMovePhoto() {
	if (destination_id.value === undefined) {
		return;
	}
	let photoMovedIds = [];
	if (props.photo) {
		photoMovedIds.push(props.photo.id);
	} else {
		photoMovedIds = props.photoIds as string[];
	}
	PhotoService.move(destination_id.value, photoMovedIds).then(() => {
		toast.add({
			severity: "success",
			summary: "Photo moved",
			life: 3000,
		});
		// Clear the cache for the current album and the destination album
		AlbumService.clearCache(props.parentId);
		AlbumService.clearCache(destination_id.value);

		emits("moved");
		// Todo emit that we moved things.
	});
}
</script>
