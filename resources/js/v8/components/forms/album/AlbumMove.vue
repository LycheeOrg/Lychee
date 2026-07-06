<template>
	<UCard v-if="albumStore.album" class="sm:p-4 xl:px-9 max-w-3xl">
		<div v-if="titleMovedTo !== undefined">
			<p class="mb-4 text-center text-muted">
				{{ sprintf($t("dialogs.move_album.confirm_single"), albumStore.album.title, titleMovedTo) }}
			</p>
			<UButton color="primary" variant="ghost" class="font-bold w-full justify-center" @click="execute">
				{{ $t("dialogs.move_album.move_single") }}
			</UButton>
		</div>
		<div v-else-if="error_no_target === false">
			<span class="font-bold">{{ $t("dialogs.move_album.move_to") }}</span>
			<SearchTargetAlbum :album-ids="[albumStore.album.id]" @selected="selected" @no-target="error_no_target = true" />
		</div>
		<div v-else>
			<p class="text-center text-muted">{{ $t("dialogs.move_album.no_album_target") }}</p>
		</div>
	</UCard>
</template>
<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import SearchTargetAlbum from "@/v8/components/forms/album/SearchTargetAlbum.vue";
import AlbumService from "@/services/album-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useAlbumStore } from "@/stores/AlbumState";

const toast = useAppToast();
const router = useRouter();
const albumStore = useAlbumStore();
const titleMovedTo = ref<string | undefined>(undefined);
const destination_id = ref<string | undefined | null>(undefined);
const error_no_target = ref(false);

function selected(target: App.Http.Resources.Models.TargetAlbumResource) {
	titleMovedTo.value = target.original;
	destination_id.value = target.id;
}

function execute() {
	if (albumStore.album === undefined) {
		return;
	}
	if (destination_id.value === undefined) {
		return;
	}
	const albumId = albumStore.album.id;
	const title = albumStore.album.title;
	const parentId = albumStore.modelAlbum?.parent_id;
	AlbumService.move(destination_id.value, [albumId]).then(() => {
		AlbumService.clearCache(destination_id.value);
		AlbumService.clearCache(parentId);
		albumStore.reset();
		toast.add({
			severity: "success",
			summary: trans("dialogs.move_album.moved_single"),
			detail: sprintf(trans("dialogs.move_album.moved_single_details"), title, titleMovedTo.value),
			life: 3000,
		});
		router.push(`/gallery/${albumId}`);
	});
}
</script>
