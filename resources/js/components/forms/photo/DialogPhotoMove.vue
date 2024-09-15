<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div v-if="titleMovedTo !== undefined">
				<p class="p-9 text-center text-muted-color">{{ confirmation }}</p>
				<div class="flex">
					<Button class="w-full" severity="secondary" @click="close">
						{{ $t("lychee.CANCEL") }}
					</Button>
					<Button
						class="text-primary-500 font-bold hover:text-white hover:bg-primary-400 w-full bg-transparent border-none"
						@click="execute"
						>{{ $t("lychee.MOVE") }}</Button
					>
				</div>
			</div>
			<div v-else>
				<div class="p-9">
					<span v-if="props.photo" class="font-bold">
						{{ sprintf("Move %s to:", props.photo.title) }}
					</span>
					<span v-else class="font-bold">
						{{ sprintf("Move %d photos to:", props.photoIds?.length) }}
					</span>
					<SearchTargetAlbum :album-id="props.albumId" @selected="selected" />
				</div>
				<Button class="w-full" severity="secondary" @click="closeCallback">
					{{ $t("lychee.CANCEL") }}
				</Button>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import PhotoService from "@/services/photo-service";
import { useToast } from "primevue/usetoast";
import { sprintf } from "sprintf-js";
import { computed, ref } from "vue";
import SearchTargetAlbum from "../album/SearchTargetAlbum.vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";

const props = defineProps<{
	photo?: App.Http.Resources.Models.PhotoResource;
	photoIds?: string[];
	albumId?: string;
}>();

const visible = defineModel("visible", { default: false });

const toast = useToast();
const titleMovedTo = ref(undefined as string | undefined);
const destination_id = ref(undefined as string | undefined | null);
const confirmation = computed(() => {
	if (props.photo) {
		return sprintf("Move %s to %s.", props.photo.title, titleMovedTo.value);
	}
	return sprintf("Move %d photos to %s.", props.photoIds?.length, titleMovedTo.value);
});

const emit = defineEmits<{
	(e: "moved"): void;
}>();

function selected(target: App.Http.Resources.Models.TargetAlbumResource) {
	titleMovedTo.value = target.original;
	destination_id.value = target.id;
}

function close() {
	titleMovedTo.value = undefined;
	destination_id.value = undefined;
	visible.value = false;
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
			summary: "Photo moved",
			life: 3000,
		});
		emit("moved");
		// Todo emit that we moved things.
	});
}
</script>
