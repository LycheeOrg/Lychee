<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div v-if="titleCopyTo !== undefined">
				<p class="p-9 text-center text-muted-color">{{ confirmation }}</p>
				<div class="flex">
					<Button severity="secondary" class="font-bold w-full border-none rounded-none rounded-bl-xl" @click="close">
						{{ $t("lychee.CANCEL") }}
					</Button>
					<Button severity="contrast" class="font-bold w-full border-none rounded-none rounded-br-xl" @click="execute">
						{{ "Copy" }}
					</Button>
				</div>
			</div>
			<div v-else>
				<div class="p-9">
					<span class="font-bold">
						{{ question }}
					</span>
					<SearchTargetAlbum :album-id="parentId" @selected="selected" />
				</div>
				<Button class="w-full font-bold rounded-none rounded-bl-xl rounded-br-xl border-none" severity="secondary" @click="closeCallback">
					{{ $t("lychee.CANCEL") }}
				</Button>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { sprintf } from "sprintf-js";
import SearchTargetAlbum from "@/components/forms/album/SearchTargetAlbum.vue";
import { useToast } from "primevue/usetoast";
import Button from "primevue/button";
import Dialog from "primevue/dialog";

const props = defineProps<{
	parentId: string | undefined;
	photo?: App.Http.Resources.Models.PhotoResource;
	photoIds?: string[];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const emits = defineEmits<{
	copy: [];
}>();

const toast = useToast();
const titleCopyTo = ref(undefined as string | undefined);
const destination_id = ref(undefined as string | undefined | null);

function selected(target: App.Http.Resources.Models.TargetAlbumResource) {
	titleCopyTo.value = target.original;
	destination_id.value = target.id;
}

function close() {
	titleCopyTo.value = undefined;
	destination_id.value = undefined;
	visible.value = false;
}

const question = computed(() => {
	if (props.photo) {
		return sprintf("Copy %s to:", props.photo?.title);
	}
	return sprintf("Copy %d photos to:", props.photoIds?.length);
});

const confirmation = computed(() => {
	if (props.photo) {
		return sprintf("Copy %s to %s.", props.photo.title, titleCopyTo.value);
	}
	return sprintf("Copy %d photos to %s.", props.photoIds?.length, titleCopyTo.value);
});

function execute() {
	if (destination_id.value === undefined) {
		return;
	}

	visible.value = false;
	let photoCopiedIds = [];
	if (props.photo) {
		photoCopiedIds.push(props.photo.id);
	} else {
		photoCopiedIds = props.photoIds as string[];
	}
	PhotoService.copy(destination_id.value, photoCopiedIds).then(() => {
		toast.add({
			severity: "success",
			summary: "Photo moved",
			life: 3000,
		});
		// Clear the cache for the current album and the destination album
		AlbumService.clearCache(destination_id.value);

		emits("copy");
		// Todo emit that we moved things.
	});
}
</script>
