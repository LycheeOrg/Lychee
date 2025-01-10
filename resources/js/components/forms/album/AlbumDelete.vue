<template>
	<Card class="sm:p-4 xl:px-9 max-w-3xl">
		<template #content>
			<p class="mb-4 text-center">
				{{ sprintf($t("dialogs.delete_album.confirmation"), props.album.title) }}<br />
				<span class="text-warning-700"><i class="pi pi-exclamation-triangle mr-2" />{{ $t("dialogs.delete_album.warning") }}</span>
			</p>
			<Button class="text-danger-800 font-bold hover:text-white hover:bg-danger-800 w-full bg-transparent border-none" @click="execute">
				{{ $t("dialogs.delete_album.delete") }}
			</Button>
		</template>
	</Card>
</template>

<script setup lang="ts">
import { useRouter } from "vue-router";
import Button from "primevue/button";
import Card from "primevue/card";
import AlbumService from "@/services/album-service";
import { sprintf } from "sprintf-js";

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.SmartAlbumResource | App.Http.Resources.Models.TagAlbumResource;
	is_model_album: boolean;
}>();

const router = useRouter();

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
