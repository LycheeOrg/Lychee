<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div>
				<p class="p-9 text-center text-muted-color max-w-xl text-wrap">{{ confirmation }}</p>
				<div class="flex">
					<Button class="w-full" severity="secondary" @click="closeCallback">
						{{ $t("lychee.CANCEL") }}
					</Button>
					<!-- class="text-danger-600 font-bold hover:text-white hover:bg-danger-700 w-full bg-transparent border-none" -->
					<Button severity="danger" class="w-full" @click="execute">{{ $t("lychee.DELETE") }}</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import Dialog from "primevue/dialog";
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

const visible = defineModel("visible", { default: false });
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
