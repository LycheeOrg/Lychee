<template>
	<Card class="sm:p-4 xl:px-9 max-w-3xl">
		<template #content>
			<p class="mb-4 text-center">{{ title }}</p>
			<Button class="text-danger-800 font-bold hover:text-white hover:bg-danger-800 w-full bg-transparent border-none" @click="execute">
				{{ $t("lychee.DELETE_ALBUM_QUESTION") }}
			</Button>
		</template>
	</Card>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useRouter } from "vue-router";
import Button from "primevue/button";
import Card from "primevue/card";
import AlbumService from "@/services/album-service";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.SmartAlbumResource | App.Http.Resources.Models.TagAlbumResource;
	is_model_album: boolean;
}>();

const router = useRouter();
const title = computed(() => sprintf(trans("lychee.DELETE_ALBUM_CONFIRMATION"), props.album.title));

const emits = defineEmits<{
	deleted: [];
}>();

function execute() {
	AlbumService.delete([props.album.id]).then(() => {
		emits("deleted");
		if (props.is_model_album) {
			const album = props.album as App.Http.Resources.Models.AlbumResource;
			AlbumService.clearCache(album.parent_id);
			album.parent_id ? router.push(`/gallery/${album.parent_id}`) : router.push("/gallery");
		} else {
			AlbumService.clearAlbums();
			router.push("/gallery");
		}
	});
}
</script>
