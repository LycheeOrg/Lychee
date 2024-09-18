<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div>
				<p class="p-9 text-center text-muted-color max-w-xl text-wrap">{{ confirmation }}</p>
				<div class="flex">
					<Button severity="secondary" class="w-full border-none rounded-none rounded-bl-xl font-bold" @click="closeCallback">
						{{ $t("lychee.CANCEL") }}
					</Button>
					<Button severity="danger" class="w-full border-none rounded-none rounded-br-xl font-bold" @click="execute">{{
						$t("lychee.DELETE")
					}}</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { computed } from "vue";
import Button from "primevue/button";
import AlbumService from "@/services/album-service";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import Dialog from "primevue/dialog";

const props = defineProps<{
	parentId: string | undefined;
	album?: App.Http.Resources.Models.ThumbAlbumResource;
	albumIds: string[];
}>();

const visible = defineModel<boolean>("visible", { default: false });
const emit = defineEmits<{
	(e: "deleted"): void;
}>();

const confirmation = computed(() => {
	if (props.album) {
		return sprintf(trans("lychee.DELETE_ALBUM_CONFIRMATION"), props.album.title);
	}
	return sprintf(trans("lychee.DELETE_ALBUMS_CONFIRMATION"), props.albumIds.length);
});

function execute() {
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
		emit("deleted");
	});
}
</script>
