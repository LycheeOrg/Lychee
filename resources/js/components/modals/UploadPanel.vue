<template>
	<Dialog v-model:visible="is_upload_visible" modal pt:root:class="border-none" :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div v-if="setup">
				<div v-if="counts.files > 0" class="m-4 flex flex-wrap justify-center">
					<span v-if="counts.completed === counts.files" class="w-full text-center text-muted-color-emphasis font-bold">{{
						$t("dialogs.upload.completed")
					}}</span>
					<span v-else class="w-full text-center">{{ $t("dialogs.upload.uploaded") }} {{ counts.completed }} / {{ counts.files }}</span>
					<ProgressBar
						class="w-full"
						:value="Math.round((counts.completed * 100) / counts.files)"
						:show-value="false"
						:pt:value:class="'duration-300'"
						:class="counts.completed === counts.files ? 'successProgressBarSeverity' : ''"
					></ProgressBar>
				</div>
				<ScrollPanel v-if="counts.files > 0" class="w-96 h-48 m-4 p-1 mr-5" :pt:scrollbar:class="'opacity-100'">
					<UploadingLine
						v-for="(uploadable, index) in list_upload_files"
						:key="uploadable.file.name"
						:file="uploadable.file"
						:album-id="albumId"
						:status="uploadable.status"
						:index="index"
						:chunk-size="setup.upload_chunk_size"
						@upload:completed="uploadCompleted"
					></UploadingLine>
				</ScrollPanel>
				<div v-if="counts.files === 0" class="p-9 max-w-3xl w-full">
					<div
						class="absolute flex items-center justify-center bg-primary-500 opacity-90"
						v-on:dragover.prevent="isDropping = true"
						v-on:dragleave.prevent="isDropping = false"
						v-on:drop="upload"
						v-show="isDropping"
					>
						<span class="text-3xl">{{ $t("dialogs.upload.release") }}</span>
					</div>
					<label
						class="flex flex-col items-center justify-center hover:text-muted-color-emphasis dark:border-surface-900 dark:hover:bg-surface-900/10 dark:hover:border-surface-950 border shadow cursor-pointer h-1/2 rounded-2xl p-6"
						for="myFiles"
					>
						<h3 class="text-xl text-center">{{ $t("dialogs.upload.select") }}</h3>
						<em class="italic text-muted-color-emphasis hover:text-muted-color">{{ $t("dialogs.upload.drag") }}</em>
					</label>
					<input v-on:change="upload" type="file" id="myFiles" multiple class="hidden" />
				</div>
			</div>
			<div v-else>
				{{ $t("dialogs.upload.loading") }}
			</div>
			<div class="flex justify-center">
				<Button
					v-if="showCancel"
					@click="cancel"
					severity="secondary"
					class="w-full font-bold border-none border-1 rounded-none rounded-bl-xl"
				>
					{{ $t("dialogs.button.cancel") }}
				</Button>
				<Button
					v-if="!showResume"
					@click="close"
					severity="secondary"
					class="w-full font-bold border-none border-1 rounded-none rounded-br-xl"
					:class="showCancel ? '' : 'rounded-bl-xl'"
					:disabled="showCancel"
				>
					{{ $t("dialogs.button.close") }}
				</Button>
				<Button
					v-else
					@click="() => uploadNext()"
					severity="contrast"
					class="w-full font-bold border-none border-1 rounded-none rounded-br-xl"
				>
					{{ $t("dialogs.upload.resume") }}
				</Button>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import { computed, Ref, ref, watch } from "vue";
import UploadingLine from "../forms/upload/UploadingLine.vue";
import ScrollPanel from "primevue/scrollpanel";
import UploadService from "@/services/upload-service";
import ProgressBar from "primevue/progressbar";
import AlbumService from "@/services/album-service";
import { useRoute } from "vue-router";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";

export type Uploadable = {
	file: File;
	status: "uploading" | "waiting" | "done" | "error";
};

const togglableStore = useTogglablesStateStore();
const { is_upload_visible, list_upload_files } = storeToRefs(togglableStore);
const route = useRoute();

const setup = ref<App.Http.Resources.GalleryConfigs.UploadConfig | undefined>(undefined);
const albumId = ref(route.params.albumid ?? (null as string | null)) as Ref<string | null>;

const emits = defineEmits<{
	refresh: [];
}>();

const isDropping = ref(false);
const showCancel = computed(() => counts.value.files > 0 && counts.value.completed < counts.value.files);
const showResume = computed(() => counts.value.waiting > 0 && counts.value.uploading === 0);

function load() {
	UploadService.getSetUp().then((response) => {
		setup.value = response.data;
	});
}
const counts = computed(() => {
	return {
		files: list_upload_files.value.length,
		waiting: list_upload_files.value.filter((f) => f.status === "waiting").length,
		completed: list_upload_files.value.filter((f) => f.status === "done").length,
		uploading: list_upload_files.value.filter((f) => f.status === "uploading").length,
	};
});

function upload(event: Event) {
	// countCompleted.value = 0;
	const target = event.target as HTMLInputElement;
	if (target.files === null) {
		return;
	}

	for (let i = 0; i < target.files.length; i++) {
		list_upload_files.value.push({ file: target.files[i], status: "waiting" });
	}

	// Start uploading chunks.
	uploadNext(0);
}

function uploadNext(searchIndex = 0, max_processing_limit: number | undefined = undefined): boolean {
	let isUploading = false;

	let offset = 0;
	for (let i = searchIndex; i < list_upload_files.value.length; i++) {
		if (list_upload_files.value[i].status === "waiting") {
			offset = i;
			break;
		}
	}

	// Compute processing limit : min between the provided max and the number of waiting.
	const processing_limit = Math.min(max_processing_limit ?? setup.value?.upload_processing_limit ?? 1, counts.value.waiting);

	// Start uploading chunks.
	for (let i = 0; i < processing_limit; i++) {
		// only execute if we are waiting.
		if (list_upload_files.value[i + offset].status === "waiting") {
			list_upload_files.value[i + offset].status = "uploading";
			isUploading = true;
		}
	}

	return isUploading;
}

function uploadCompleted(index: number, status: "done" | "error") {
	list_upload_files.value[index].status = status;

	const isUploading = uploadNext(index, 1);

	if (isUploading === false) {
		AlbumService.clearCache(albumId.value ?? "unsorted");
		emits("refresh");
	}
}

function cancel() {
	is_upload_visible.value = false;
	list_upload_files.value = [];
	AlbumService.clearCache(albumId.value ?? "unsorted");
	emits("refresh");
}

function close() {
	list_upload_files.value = [];
	is_upload_visible.value = false;
}

load();

watch(
	() => is_upload_visible.value,
	() => {
		if (list_upload_files.value.length > 0) {
			uploadNext(0, setup.value?.upload_processing_limit);
		}
	},
);

watch(
	() => route.params.albumid,
	(newAlbumId, _oldAlbumId) => {
		albumId.value = newAlbumId as string | null;
		if (!is_upload_visible.value) {
			list_upload_files.value = [];
		}
	},
);
</script>
