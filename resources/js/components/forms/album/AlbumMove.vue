<template>
	<Card class="sm:p-4 xl:px-9 max-w-3xl">
		<template #content>
			<div v-if="titleMovedTo !== undefined">
				<p class="mb-4 text-center text-muted-color">{{ confirmation }}</p>
				<Button class="text-primary-500 font-bold hover:text-white hover:bg-primary-400 w-full bg-transparent border-none" @click="execute">
					{{ $t("lychee.MOVE_ALBUM") }}
				</Button>
			</div>
			<div v-else-if="error_no_target === false">
				<span class="font-bold">{{ "Move to" }}</span>
				<SearchTargetAlbum :album-id="props.album.id" @selected="selected" @no-target="error_no_target = true" />
			</div>
			<div v-else>
				<p class="text-center text-muted-color">{{ "No album to move to" }}</p>
			</div>
		</template>
	</Card>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import Button from "primevue/button";
import Card from "primevue/card";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import SearchTargetAlbum from "@/components/forms/album/SearchTargetAlbum.vue";
import AlbumService from "@/services/album-service";
import { useToast } from "primevue/usetoast";

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource;
}>();

const toast = useToast();
const router = useRouter();
const titleMovedTo = ref(undefined as string | undefined);
const destination_id = ref(undefined as string | undefined | null);
const confirmation = computed(() => sprintf(trans("lychee.ALBUM_MOVE"), props.album.title, titleMovedTo.value));
const error_no_target = ref(false);

function selected(target: App.Http.Resources.Models.TargetAlbumResource) {
	titleMovedTo.value = target.original;
	destination_id.value = target.id;
}

function execute() {
	if (destination_id.value === undefined) {
		return;
	}
	AlbumService.move(destination_id.value, [props.album.id]).then(() => {
		AlbumService.clearCache(destination_id.value);
		AlbumService.clearCache(props.album.parent_id);
		toast.add({
			severity: "success",
			summary: "Album moved!",
			detail: props.album.title + " moved to " + titleMovedTo.value,
			life: 3000,
		});
		router.push(`/gallery/${props.album.id}`);
	});
}
</script>
