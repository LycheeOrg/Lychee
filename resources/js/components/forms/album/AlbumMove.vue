<template>
	<Card class="sm:p-4 xl:px-9 max-w-3xl" :pt:body:class="'p-0'" v-if="albumStore.album">
		<template #content>
			<div v-if="titleMovedTo !== undefined">
				<p class="mb-4 text-center text-muted-color">
					{{ sprintf($t("dialogs.move_album.confirm_single"), albumStore.album.title, titleMovedTo) }}
				</p>
				<Button class="text-primary-500 font-bold hover:text-white hover:bg-primary-400 w-full bg-transparent border-none" @click="execute">
					{{ $t("dialogs.move_album.move_single") }}
				</Button>
			</div>
			<div v-else-if="error_no_target === false">
				<span class="font-bold">{{ $t("dialogs.move_album.move_to") }}</span>
				<SearchTargetAlbum :album-ids="[albumStore.album.id]" @selected="selected" @no-target="error_no_target = true" />
			</div>
			<div v-else>
				<p class="text-center text-muted-color">{{ $t("dialogs.move_album.no_album_target") }}</p>
			</div>
		</template>
	</Card>
</template>
<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import Button from "primevue/button";
import Card from "primevue/card";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import SearchTargetAlbum from "@/components/forms/album/SearchTargetAlbum.vue";
import AlbumService from "@/services/album-service";
import { useToast } from "primevue/usetoast";
import { useAlbumStore } from "@/stores/AlbumState";

const toast = useToast();
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
