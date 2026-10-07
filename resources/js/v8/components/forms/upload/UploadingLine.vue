<template>
	<div :id="`upload${index}`" class="w-full flex items-center gap-x-3">
		<div ref="thumbBox" class="w-12 h-8 shrink-0 rounded overflow-hidden bg-elevated flex items-center justify-center">
			<img v-if="thumbUrl" :src="thumbUrl" alt="" class="size-full object-cover" />
			<UIcon v-else :name="placeholderIcon" class="size-5 text-muted" />
		</div>
		<div class="min-w-0 flex-1 flex flex-col">
			<div class="flex gap-x-4 justify-between relative" :class="errorFlexClass">
				<span class="text-ellipsis min-w-0 w-full overflow-hidden text-nowrap text-muted">
					<span v-if="albumTitle" class="text-xs text-muted mr-1">{{ albumTitle }} /</span>{{ file.name }}
				</span>
				<span v-if="progress < 100 && progress > 0" :class="statusClass">{{ progress }}%</span>
				<span :class="statusClass">{{ statusMessage }}</span>
			</div>
			<span class="text-center w-full hidden group-hover:block text-error cursor-pointer" @click="controller.abort()">
				{{ $t("dialogs.button.cancel") }}
			</span>
			<UProgress :model-value="progressBar" :color="progressColor" />
		</div>
	</div>
</template>
<script setup lang="ts">
import UploadService, { UploadData } from "@/services/upload-service";
import { AxiosError, type AxiosProgressEvent } from "axios";
import { trans } from "laravel-vue-i18n";
import { computed, onUnmounted, ref, useTemplateRef, watch } from "vue";
import { useIntersectionObserver } from "@vueuse/core";
import { createUploadThumbnail, enqueueThumbnail, shouldDecode, uploadPlaceholderIcon } from "@/v8/utils/uploadThumbnail";

type UploadingLineProps = {
	albumId: string | null;
	albumTitle?: string;
	file: File;
	chunkSize: number;
	status: "uploading" | "waiting" | "done" | "error" | "warning";
	index: number;
	applyWatermark: boolean;
	message: string | undefined;
	scrollRoot?: HTMLElement | null;
};

const props = withDefaults(defineProps<UploadingLineProps>(), {
	chunkSize: 1024,
	message: undefined,
	scrollRoot: null,
});

const emits = defineEmits<{
	"upload:completed": [index: number, status: "done" | "warning" | "error", message: string | undefined];
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

// Miniature (Feature 077): built once the row nears the visible part of the list.
const thumbBox = useTemplateRef<HTMLDivElement>("thumbBox");
const thumbUrl = ref<string | undefined>(undefined);
const placeholderIcon = ref(uploadPlaceholderIcon(file.value.type));
let cancelThumbnail = () => {};
let unmounted = false;

const { stop: stopObserving } = useIntersectionObserver(
	thumbBox,
	([entry]) => {
		if (!entry?.isIntersecting) {
			return;
		}
		stopObserving();
		if (shouldDecode(file.value.type)) {
			cancelThumbnail = enqueueThumbnail(() => createUploadThumbnail(file.value).then(onThumbnailReady, onThumbnailFailed));
		}
	},
	// The list scrolls inside its own box: observe against it so the margin applies there.
	{ root: () => props.scrollRoot, rootMargin: "200px" },
);

function onThumbnailReady(url: string) {
	if (unmounted) {
		URL.revokeObjectURL(url);
		return;
	}
	thumbUrl.value = url;
}

function onThumbnailFailed() {
	placeholderIcon.value = "lucide:image";
}

onUnmounted(() => {
	unmounted = true;
	cancelThumbnail();
	if (thumbUrl.value !== undefined) {
		URL.revokeObjectURL(thumbUrl.value);
	}
});

// prettier-ignore
const statusMessage = computed(() => {
	switch (status.value) {
		case "uploading": return trans("dialogs.upload.uploading");
		case "done":      return trans("dialogs.upload.finished");
		case "warning":   return props.message ?? errorMessage.value ?? trans("dialogs.upload.failed_error");
		case "error":     return props.message ?? errorMessage.value ?? trans("dialogs.upload.failed_error");
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
		case "uploading": return "text-primary text-right pr-1  text-xs";
		case "done":      return "text-green-600 text-right pr-1  text-xs";
		case "warning":   return "text-warning text-right pr-1  text-xs";
		case "error":     return "text-error text-right pr-1  text-xs";
		default:          return "text-warning text-right pr-1  text-xs";
	}
});

const progressBar = computed(() => (status.value === "done" ? 100 : progress.value));
// prettier-ignore
const progressColor = computed<"success" | "warning" | "error" | "primary">(() => {
	switch (status.value) {
		case "done":  return "success";
		case "warning": return "warning";
		case "error": return "error";
		default:      return "primary";
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
				emits("upload:completed", props.index, "done", undefined);
			} else {
				chunkStart.value += props.chunkSize;
				process();
			}
		})
		.catch(handleError);
}

function handleError(error: AxiosError<{ message: string }>) {
	if (!error.response) {
		progress.value = 100;
		status.value = "error";
		errorMessage.value = error.message;
		emits("upload:completed", props.index, "error", errorMessage.value);
		return;
	}

	switch (error.response.status) {
		case 409:
			errorMessage.value = error.response.data.message;
			progress.value = 100;
			status.value = "warning";
			emits("upload:completed", props.index, "warning", errorMessage.value);
			return; // duplicate found
		case 413:
			errorMessage.value = error.response.data.message;
			progress.value = 100;
			status.value = "error";
			emits("upload:completed", props.index, "error", errorMessage.value);
			return;
		case 422:
			errorMessage.value = error.response.data.message;
			progress.value = 100;
			status.value = "error";
			emits("upload:completed", props.index, "error", errorMessage.value);
			return;
		case 500:
			errorMessage.value = "Something went wrong, check the logs.";
			if (error.response.data.message.includes("Failed to open stream: Permission denied")) {
				errorMessage.value = "Failed to open stream: Permission denied";
			}
			progress.value = 100;
			status.value = "error";
			emits("upload:completed", props.index, "error", errorMessage.value);
			return;
		case 504:
			errorMessage.value = "The server took too long to respond.";
			progress.value = 100;
			status.value = "warning";
			emits("upload:completed", props.index, "warning", errorMessage.value);
			return;
		default:
			progress.value = 100;
			status.value = "error";
			emits("upload:completed", props.index, "error", undefined);
			return;
	}
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
