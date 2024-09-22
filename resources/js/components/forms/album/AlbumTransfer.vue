<template>
	<Card class="sm:p-4 xl:px-9 max-w-3xl w-full">
		<template #content>
			<div v-if="newOwner !== undefined">
				<p class="w-full mb-4 text-center text-muted-color">{{ confirmation }}<br />{{ "This action can’t be undone!" }}</p>
			</div>
			<div v-else class="text-center w-full">
				<span class="font-bold">{{ "Transfer ownership of album to" }}</span>
				<SearchTargetUser :album="album" @selected="selected" />
			</div>
			<Button
				class="text-danger-800 font-bold hover:text-white hover:bg-danger-800 w-full bg-transparent border-none"
				:disabled="newOwner === undefined"
				@click="execute"
			>
				{{ "Transfer ownership of album and photos" }}
			</Button>
		</template>
	</Card>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import Button from "primevue/button";
import Card from "primevue/card";
import AlbumService from "@/services/album-service";
import { sprintf } from "sprintf-js";
import SearchTargetUser from "@/components/forms/album/SearchTargetUser.vue";
import { useToast } from "primevue/usetoast";
import Album from "@/views/gallery-panels/Album.vue";

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource;
}>();

const toast = useToast();
const router = useRouter();
const newOwner = ref(undefined as undefined | App.Http.Resources.Models.LightUserResource);
const confirmation = computed(() =>
	sprintf(
		"Are you sure you want to transfer to %s the ownership of album “%s” and all the photos in contains? You access to this album and will be lost.",
		newOwner.value?.username,
		props.album.title,
	),
);

function execute() {
	if (newOwner.value === undefined) {
		return;
	}
	AlbumService.transfer(props.album.id, newOwner.value.id).then(() => {
		// Check if user can administrate. Otherwise redirect to gallery
		// if (props.is_model_album) {
		// 	const album = props.album as App.Http.Resources.Models.AlbumResource;
		// 	album.parent_id ? router.push(`/gallery/${album.parent_id}`) : router.push("/gallery");
		// } else {
		router.push("/gallery");
		// @ts-expect-error
		AlbumService.clearCache(props.album?.parent_id);
		// }
	});
}

function selected(target: App.Http.Resources.Models.LightUserResource) {
	newOwner.value = target;
}
</script>
