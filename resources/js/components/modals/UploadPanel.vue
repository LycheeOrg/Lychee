<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none" :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div v-if="setup">
				<div v-if="files.length > 0" class="m-4 flex flex-wrap justify-center">
					<span class="w-full text-center">Completed: {{ countCompleted }} / {{ files.length }}</span>
					<ProgressBar
						:class="'w-full'"
						:value="Math.round((countCompleted * 100) / files.length)"
						:show-value="false"
						:pt:value:class="'duration-300'"
					></ProgressBar>
				</div>
				<ScrollPanel v-if="files.length > 0" class="w-96 h-48 m-4 p-1 mr-5" :pt:scrollbar:class="'opacity-100'">
					<UploadingLine
						v-for="(uploadable, index) in files"
						:key="uploadable.file.name"
						:file="uploadable.file"
						:album-id="albumId"
						:status="uploadable.status"
						:index="index"
						:chunk-size="setup.upload_chunk_size"
						@upload:completed="uploadCompleted"
					></UploadingLine>
				</ScrollPanel>
				<div v-if="files.length === 0" class="p-9 max-w-3xl w-full">
					<div
						class="absolute flex items-center justify-center bg-primary-500 opacity-90"
						v-on:dragover.prevent="isDropping = true"
						v-on:dragleave.prevent="isDropping = false"
						v-on:drop="upload"
						v-show="isDropping"
					>
						<span class="text-3xl">Release file to upload!</span>
					</div>
					<label
						class="flex flex-col items-center justify-center hover:text-muted-color-emphasis dark:border-surface-900 dark:hover:bg-surface-900/10 dark:hover:border-surface-950 border shadow cursor-pointer h-1/2 rounded-2xl p-6"
						for="myFiles"
					>
						<h3 class="text-xl text-center">Click here to select files to upload</h3>
						<em class="italic text-muted-color-emphasis hover:text-muted-color">(Or drag files to the page)</em>
					</label>
					<input v-on:change="upload" type="file" id="myFiles" multiple class="hidden" />
				</div>
			</div>
			<div v-else>
				{{ $t("lychee.LOADING") }}
			</div>
			<div class="flex justify-center">
				<Button
					v-if="showCancel"
					@click="closeCancel"
					severity="secondary"
					class="w-full font-bold border-none border-1 rounded-none rounded-bl-xl"
				>
					{{ $t("lychee.CANCEL") }}
				</Button>
				<Button
					@click="closeCancel"
					severity="secondary"
					class="w-full font-bold border-none border-1 rounded-none rounded-br-xl"
					:class="showCancel ? '' : 'rounded-bl-xl'"
					:disabled="showCancel"
				>
					{{ $t("lychee.CLOSE") }}
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

type Uploadable = {
	file: File;
	status: string;
};

const visible = defineModel("visible", { default: false }) as Ref<boolean>;

const props = defineProps<{ albumId: string | null }>();

const setup = ref(undefined as undefined | App.Http.Resources.GalleryConfigs.UploadConfig);
const albumId = ref(props.albumId);

const emits = defineEmits<{
	close: [];
}>();

const isDropping = ref(false);
const files = ref([] as Uploadable[]);
const showCancel = computed(() => files.value.length > 0 && countCompleted.value < files.value.length);

function load() {
	UploadService.getSetUp().then((response) => {
		setup.value = response.data;
	});
}
const countCompleted = ref(0);

function upload(event: Event) {
	countCompleted.value = 0;
	const target = event.target as HTMLInputElement;
	if (target.files === null) {
		return;
	}

	for (let i = 0; i < target.files.length; i++) {
		files.value.push({ file: target.files[i], status: "waiting" });
	}

	// Start uploading chunks.
	const processing_limit = Math.min(setup.value?.upload_processing_limit ?? 1, files.value.length);
	for (let i = 0; i < processing_limit; i++) {
		files.value[i].status = "uploading";
	}
}

function uploadCompleted(index: number) {
	countCompleted.value++;
	// document.getElementById("upload" + index)?.scrollIntoView();
	// Find the next one and start uploading.
	for (let i = index; i < files.value.length; i++) {
		if (files.value[i].status === "waiting") {
			files.value[i].status = "uploading";
			break;
		}
	}
}

function closeCancel() {
	visible.value = false;
	files.value = [];
	AlbumService.clearCache(props.albumId ?? "unsorted");
	emits("close");
}

load();

watch(
	() => props.albumId,
	(newAlbumId, _oldAlbumId) => {
		albumId.value = newAlbumId as string | null;
		files.value = [];
	},
);
</script>
