<template>
	<div class="w-full flex flex-col">
		<div class="flex gap-4 justify-between">
			<span class="text-ellipsis min-w-0 w-full overflow-hidden text-nowrap">{{ file.name }}</span>
			<span :class="statusClass + ' text-right'">{{ status }}</span>
		</div>
		<ProgressBar :value="progress"></ProgressBar>
	</div>
</template>
<script setup lang="ts">
import UploadService, { UploadData } from "@/services/upload-service";
import { AxiosProgressEvent } from "axios";
import ProgressBar from "primevue/progressbar";
import { computed, ref, watch } from "vue";

const props = withDefaults(
	defineProps<{
		albumId: string | null;
		file: File;
		chunkSize: number;
		status: string;
		index: number;
	}>(),
	{
		chunkSize: 1024 * 1024,
	},
);

const emit = defineEmits(["upload:completed"]);

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

const statusClass = computed(() => {
	switch (status.value) {
		case "uploading":
			return "text-sky-500";
		case "done":
			return "text-green-500";
		case "error":
			return "text-red-500";
		default:
			return "text-orange-500";
	}
});

function process() {
	meta.value.chunk_number = meta.value.chunk_number + 1;
	const chunkEnd = Math.min(chunkStart.value + props.chunkSize, size.value);
	const chunk = file.value.slice(chunkStart.value, chunkEnd);
	console.log(chunkStart.value, chunkEnd, size.value);
	console.log(chunk);
	const data: UploadData = {
		album_id: props.albumId,
		file: chunk,
		file_last_modified_time: file.value.lastModified,
		meta: meta.value,
		onUploadProgress: (progressEvent: AxiosProgressEvent) => {
			const percent = Math.round((progressEvent.loaded / (progressEvent.total ?? 1)) * 100);

			progress.value = chunkStart.value / size.value + percent / (chunkEnd - chunkStart.value);
		},
	};
	console.log(data);

	UploadService.upload(data).then((response) => {
		if (response.data.chunk_number === response.data.total_chunks) {
			status.value = "done";
			emit("upload:completed", props.index);
		} else {
			chunkStart.value += props.chunkSize;
			process();
		}
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
