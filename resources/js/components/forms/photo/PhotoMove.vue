<template>
	<div v-if="titleMovedTo !== undefined">
		<p class="p-9 text-center text-muted-color">{{ confirmation }}</p>
		<Button class="text-primary-500 font-bold hover:text-white hover:bg-primary-400 w-full bg-transparent border-none" @click="execute">{{
			$t("lychee.MOVE")
		}}</Button>
	</div>
	<div v-else class="p-9">
		<span v-if="props.photo" class="font-bold">
			{{ sprintf("Move %s to:", props.photo.title) }}
		</span>
		<span v-else class="font-bold">
			{{ sprintf("Move %d photos to:", props.photoIds?.length) }}
		</span>
		<SearchTargetAlbum :album-id="props.albumId" @selected="selected" />
	</div>
</template>
<script setup lang="ts">
import PhotoService from "@/services/photo-service";
import { useToast } from "primevue/usetoast";
import { sprintf } from "sprintf-js";
import { computed, ref } from "vue";
import SearchTargetAlbum from "../album/SearchTargetAlbum.vue";
import Button from "primevue/button";

const props = defineProps<{
	photo?: App.Http.Resources.Models.PhotoResource;
	photoIds?: string[];
	albumId?: string;
}>();

const toast = useToast();
const titleMovedTo = ref(undefined as string | undefined);
const destination_id = ref(undefined as string | undefined | null);
const confirmation = computed(() => {
	if (props.photo) {
		return sprintf("Move %s to %s.", props.photo.title, titleMovedTo.value);
	}
	return sprintf("Move %d photos to %s.", props.photoIds?.length, titleMovedTo.value);
});

function selected(target: App.Http.Resources.Models.TargetAlbumResource) {
	titleMovedTo.value = target.original;
	destination_id.value = target.id;
}

function execute() {
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
			life: 3000,
		});
		// Todo emit that we moved things.
	});
}
</script>
