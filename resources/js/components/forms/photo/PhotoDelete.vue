<template>
	<div>
		<p class="p-9 text-center text-muted-color max-w-xl text-wrap">{{ confirmation }}</p>
		<Button class="text-danger-600 font-bold hover:text-white hover:bg-danger-700 w-full bg-transparent border-none" @click="execute">{{
			$t("lychee.DELETE")
		}}</Button>
	</div>
</template>
<script setup lang="ts">
import PhotoService from "@/services/photo-service";
import { useToast } from "primevue/usetoast";
import { sprintf } from "sprintf-js";
import { computed } from "vue";
import Button from "primevue/button";
import { trans } from "laravel-vue-i18n";
const toast = useToast();

const props = defineProps<{
	photo?: App.Http.Resources.Models.PhotoResource;
	photoIds?: string[];
}>();

const emit = defineEmits<{
	(e: "deleted"): void;
}>();

const confirmation = computed(() => {
	if (props.photo) {
		return sprintf(trans("lychee.PHOTO_DELETE_CONFIRMATION"), props.photo.title);
	}
	return sprintf(trans("lychee.PHOTO_DELETE_ALL"), props.photoIds?.length);
});

function execute() {
	let photoDeletedIds = [];
	if (props.photo) {
		photoDeletedIds.push(props.photo.id);
	} else {
		photoDeletedIds = props.photoIds as string[];
	}
	PhotoService.delete(photoDeletedIds).then(() => {
		toast.add({
			severity: "success",
			summary: "Photo deleted",
			life: 3000,
		});
		emit("deleted");
		// Todo emit that we moved things.
	});
}
</script>
