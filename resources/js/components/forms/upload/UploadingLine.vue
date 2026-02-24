<template>
	<div :id="`upload${index}`" class="w-full flex flex-col">
		<div class="flex gap-x-4 justify-between relative" :class="errorFlexClass">
			<span class="text-ellipsis min-w-0 w-full overflow-hidden text-nowrap text-muted-color">{{ file.name }}</span>
			<span v-if="progress < 100 && progress > 0" :class="statusClass">{{ progress }}%</span>
			<span :class="statusClass">{{ statusMessage }}</span>
		</div>
		<span class="text-center w-full hidden group-hover:block text-danger-700 cursor-pointer" @click="controller.abort()">
			{{ $t("dialogs.button.cancel") }}
		</span>
		<ProgressBar :class="progressClass" :value="progressBar" :show-value="false" :pt:value:class="'duration-300'"></ProgressBar>
	</div>
</template>
<script setup lang="ts">
import UploadService, { UploadData } from "@/services/upload-service";
import { type AxiosProgressEvent } from "axios";
import { trans } from "laravel-vue-i18n";
import ProgressBar from "primevue/progressbar";
import { computed, ref, watch } from "vue";

const props = withDefaults(
	defineProps<{
		albumId: string | null;
		file: File;
		chunkSize: number;
		status: "uploading" | "waiting" | "done" | "error" | "warning";
		index: number;
		applyWatermark: boolean;
	}>(),
	{
		chunkSize: 1024,
	},
);

const emits = defineEmits<{
	"upload:completed": [index: number, status: "done" | "warning" | "error"];
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
const errorMessage = ref<string | undefined>(undefined);

// prettier-ignore
const statusMessage = computed(() => {
	switch (status.value) {
		case "uploading": return trans("dialogs.upload.uploading");
		case "done":      return trans("dialogs.upload.finished");
		case "warning":   return errorMessage.value ?? trans("dialogs.upload.failed_error");
		case "error":     return errorMessage.value ?? trans("dialogs.upload.failed_error");
		default:          return "";
	}
});

// prettier-ignore
const errorFlexClass = computed(() => {
	switch (status.value) {
		case "error": return "flex-wrap";
		case "warning": return "flex-wrap";
		default:      return "";
	}
});

// prettier-ignore
const statusClass = computed(() => {
	switch (status.value) {
		case "uploading": return "text-sky-500 text-right pr-1";
		case "done":      return "text-create-600 text-right pr-1";
		case "warning":   return "text-warning-600 text-right pr-1";
		case "error":     return "text-danger-700 text-right pr-1";
		default:          return "text-warning-600 text-right pr-1";
	}
});

const progressBar = computed(() => (status.value === "done" ? 100 : progress.value));
// prettier-ignore
const progressClass = computed(() => {
	switch (status.value) {
		case "done":  return "successProgressBarSeverity";
		case "warning": return "warningProgressBarSeverity";
		case "error": return "errorProgressBarSeverity";
		default:      return "";
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
		apply_watermark: props.applyWatermark,
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
				emits("upload:completed", props.index, "done");
			} else {
				chunkStart.value += props.chunkSize;
				process();
			}
		})
		.catch((error) => {
			// prettier-ignore
			switch (error.response.status) {
				case 409: errorMessage.value = error.response.data.message;
					progress.value = 100;
					status.value = "warning";
					emits("upload:completed", props.index, "warning");
					break; // duplicate found
				case 413: errorMessage.value = error.response.data.message; break;
				case 422: errorMessage.value = error.response.data.message; break;
				case 500: errorMessage.value = "Something went wrong, check the logs.";
					if (error.response.data.message.includes("Failed to open stream: Permission denied")) {
						errorMessage.value = "Failed to open stream: Permission denied";
					}
					break;
				case 504: errorMessage.value = "The server took too long to respond.";
					progress.value = 100;
					status.value = "warning";
					emits("upload:completed", props.index, "warning");
					return;
				default: break;
			}
			progress.value = 100;
			status.value = "error";
			emits("upload:completed", props.index, "error");
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

<style lang="css">
.errorProgressBarSeverity {
	--p-progressbar-value-background: var(--danger-dark);
}
.successProgressBarSeverity {
	--p-progressbar-value-background: var(--create);
}
.warningProgressBarSeverity {
	--p-progressbar-value-background: var(--warning);
}
</style>
