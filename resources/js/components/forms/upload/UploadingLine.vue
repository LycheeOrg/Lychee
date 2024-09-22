<template>
	<div class="w-full flex flex-col">
		<div class="flex gap-x-4 justify-between relative" :class="errorFlexClass">
			<span class="text-ellipsis min-w-0 w-full overflow-hidden text-nowrap">{{ file.name }}</span>
			<span :class="statusClass" v-if="progress < 100 && progress > 0">{{ progress }}%</span>
			<span :class="statusClass">{{ statusMessage }}</span>
		</div>
		<span class="text-center w-full hidden group-hover:block text-danger-700 cursor-pointer" @click="controller.abort()">{{
			$t("lychee.CANCEL")
		}}</span>
		<ProgressBar :class="progressClass" :value="progress" :show-value="false"></ProgressBar>
	</div>
</template>
<script setup lang="ts">
import UploadService, { UploadData } from "@/services/upload-service";
import { AxiosProgressEvent } from "axios";
import { trans } from "laravel-vue-i18n";
import ProgressBar from "primevue/progressbar";
import { computed, ref, watch } from "vue";

//  'UPLOAD_UPLOADING' => 'Uploading',
// 	'UPLOAD_FINISHED' => 'Finished',
// 	'UPLOAD_PROCESSING' => 'Processing',
// 	'UPLOAD_FAILED' => 'Failed',
// 	'UPLOAD_FAILED_ERROR' => 'Upload failed. The server returned an error!',
// 	'UPLOAD_FAILED_WARNING' => 'Upload failed. The server returned a warning!',
// 	'UPLOAD_CANCELLED' => 'Cancelled',
// 	'UPLOAD_SKIPPED' => 'Skipped',
// 	'UPLOAD_UPDATED' => 'Updated',
// 	'UPLOAD_GENERAL' => 'General',

const props = withDefaults(
	defineProps<{
		albumId: string | null;
		file: File;
		chunkSize: number;
		status: string;
		index: number;
	}>(),
	{
		chunkSize: 1024,
	},
);

const emits = defineEmits<{
	(e: "upload:completed", index: number): void;
}>();

const status = ref(props.status);
const file = ref(props.file);
const progress = ref(0);
const chunkStart = ref(0);
const size = ref(file.value.size);
const meta = ref({
	file_name: file.value.name,
	extension: null,
	uuid_name: null,
	stage: "uploading",
	chunk_number: 0,
	total_chunks: Math.ceil(size.value / props.chunkSize),
} as App.Http.Resources.Editable.UploadMetaResource);
const controller = ref(new AbortController());

const statusMessage = computed(() => {
	switch (status.value) {
		case "uploading":
			return trans("lychee.UPLOAD_UPLOADING");
		case "done":
			return trans("lychee.UPLOAD_FINISHED");
		case "error":
			return trans("lychee.UPLOAD_FAILED_ERROR");
		default:
			return "";
	}
});

const errorFlexClass = computed(() => {
	switch (status.value) {
		case "error":
			return "flex-wrap";
		default:
			return "";
	}
});

const statusClass = computed(() => {
	switch (status.value) {
		case "uploading":
			return "text-sky-500 text-right pr-1";
		case "done":
			return "text-create-600 text-right pr-1";
		case "error":
			return "text-danger-700 text-right pr-1";
		default:
			return "text-warning-600 text-right pr-1";
	}
});

const progressClass = computed(() => {
	switch (status.value) {
		case "done":
			return "success";
		case "error":
			return "error";
		default:
			return "";
	}
});

function process() {
	meta.value.chunk_number = meta.value.chunk_number + 1;
	const chunkEnd = Math.min(chunkStart.value + props.chunkSize, size.value);
	const chunk = file.value.slice(chunkStart.value, chunkEnd);
	const data: UploadData = {
		album_id: props.albumId,
		file: chunk,
		file_last_modified_time: file.value.lastModified,
		meta: meta.value,
		onUploadProgress: (progressEvent: AxiosProgressEvent) => {
			const percent = progressEvent.loaded / (progressEvent.total ?? 1);
			progress.value = Math.round(((chunkStart.value + percent * (chunkEnd - chunkStart.value)) / size.value) * 100);
		},
	};

	UploadService.upload(data, controller.value)
		.then((response) => {
			meta.value = response.data;
			if (response.data.chunk_number === response.data.total_chunks) {
				progress.value = 100;
				status.value = "done";
				emits("upload:completed", props.index);
			} else {
				chunkStart.value += props.chunkSize;
				process();
			}
		})
		.catch((error) => {
			progress.value = 100;
			status.value = "error";
			emits("upload:completed", props.index);
		});
}

if (status.value === "uploading") {
	process();
}

watch(
	() => props.status,
	(newStatus, oldStatus) => {
		if (oldStatus === "waiting" && newStatus === "uploading") {
			process();
		}
	},
);
</script>

<style lang="css" scoped>
.error {
	--p-progressbar-value-background: var(--danger-dark);
}
.success {
	--p-progressbar-value-background: var(--create);
}
</style>
