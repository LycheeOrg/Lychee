<template>
	<Dialog
		v-model:visible="visible"
		modal
		:pt="{
			root: 'border-none',
			mask: {
				// style: 'backdrop-filter: blur(2px)',
			},
		}"
		@hide="closeCallback"
	>
		<template #container="{ closeCallback }">
			<ScrollPanel v-if="files.length > 0" class="w-96 h-48 m-4 p-1 mr-5">
				<UploadingLine
					v-for="(uploadable, index) in files"
					:key="uploadable.file.name"
					:file="uploadable.file"
					:album-id="null"
					:status="uploadable.status"
					:index="index"
					@upload:completed="uploadCompleted"
				></UploadingLine>
			</ScrollPanel>
			<div v-if="files.length === 0" class="p-9 max-w-3xl w-full">
				<div
					class="absolute flex items-center justify-center bg-primary-500 opacity-90"
					v-on:dragover.prevent="isDropping = true"
					v-on:dragleave.prevent="isDropping = false"
					v-show="isDropping"
				>
					<span class="text-3xl">Release file to upload!</span>
				</div>
				<label
					class="flex flex-col items-center justify-center hover:bg-surface-500 border border-bg-500 shadow cursor-pointer h-1/2 rounded-2xl p-6"
					for="myFiles"
				>
					<h3 class="text-xl text-center">Click here to select files to upload</h3>
					<em class="italic text-muted-color-emphasis">(Or drag files to the page)</em>
				</label>
				<input v-on:change="upload" type="file" id="myFiles" multiple class="hidden" />
			</div>
			<div class="flex justify-center">
				<Button @click="closeCallback" text autofocus class="p-3 w-full font-bold border-1 border-white-alpha-30 hover:bg-white-alpha-10">
					{{ $t("lychee.CLOSE") }}
				</Button>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import { ref, watch } from "vue";
import UploadingLine from "../forms/upload/UploadingLine.vue";
import ScrollPanel from "primevue/scrollpanel";

type Uploadable = {
	file: File;
	status: string;
};
const props = withDefaults(
	defineProps<{
		visible: boolean;
	}>(),
	{
		visible: false,
	},
);

const visible = ref(props.visible);

const emit = defineEmits(["close"]);

const isDropping = ref(false);
const files = ref([] as Uploadable[]);

function upload(event: Event) {
	const target = event.target as HTMLInputElement;
	if (target.files === null) {
		return;
	}

	for (let i = 0; i < target.files.length; i++) {
		files.value.push({ file: target.files[i], status: "waiting" });
	}
	files.value[0].status = "uploading";
}

function uploadCompleted(index: number) {
	// Find the next one and start uploading.
	for (let i = index; i < files.value.length; i++) {
		if (files.value[i].status === "waiting") {
			files.value[i].status = "uploading";
			break;
		}
	}
}

// function finish(): void {
// 	if (!hasErrorOccurred.value) {
// 		console.log("Success!");
// 		// wire.dispatch("reloadPage");
// 		// wire.close();
// 	} else {
// 		// console.log("Well something went wrong...");
// 	}
// }

// 			/**
// 			 * Begin processing number "fileIdx".
// 			 *
// 			 * @param fileIdx file index to process.
// 			 * @param wire    Livewire object.
// 			 * @param alpine  Alpine object.
// 			 */
// function process(fileIdx: number): void {
// 	outstandingResponsesCount.value = outstandingResponsesCount.value + 1;
// 	uploadChunk(fileIdx);
// }

// function complete(): void {
// 	outstandingResponsesCount.value = outstandingResponsesCount.value - 1;

// 	// Start the next one if possible.
// 	if (outstandingResponsesCount.value < upload_processing_limit.value && latestFileIdx.value + 1 < fileList.value.length) {
// 		console.log("next file!");
// 		latestFileIdx.value = latestFileIdx.value + 1;
// 		process(latestFileIdx.value);
// 	} else if (outstandingResponsesCount.value === 0 && latestFileIdx.value + 1 === fileList.value.length) {
// 		console.log("Finish");
// 		finish();
// 	} else {
// 		console.log("Curent threads: " + outstandingResponsesCount.value);
// 		console.log("Current index: " + latestFileIdx.value);
// 		console.log("Number of files: " + fileList.value.length);
// 		console.log("waiting...");
// 	}
// }

// /**
//  * Processes the upload and response for a single file.
//  *
//  * Note that up to `livewireUploadChunk` "instances" of
//  * this method can be "alive" simultaneously.
//  * The parameter `fileIdx` is limited by `latestFileIdx`.
//  */
// function uploadChunk(fileIdx: number): void {
// 	// End of chunk is start + chunkSize OR file size, whichever is greater
// 	const chunkEnd = Math.min(chnkStarts.value[fileIdx] + chunkSize.value, fileList.value[fileIdx].size);
// 	const chunk = fileList.value[fileIdx].slice(chnkStarts.value[fileIdx], chunkEnd);
// 	numChunks.value[fileIdx] = Math.ceil(fileList.value[fileIdx].size / chunkSize.value);

// 	wire.upload(
// 		"uploads." + fileIdx + ".fileChunk",
// 		chunk,
// 		(success) => {
// 			alpine.chnkStarts[fileIdx] = Math.min(alpine.chnkStarts[fileIdx] + alpine.chunkSize, alpine.fileList[fileIdx].size);

// 			if (alpine.chnkStarts[fileIdx] < alpine.fileList[fileIdx].size) {
// 				setTimeout(alpine.livewireUploadChunk, 5, fileIdx, wire, alpine);
// 			} else {
// 				alpine.complete(wire, alpine);
// 			}
// 		},
// 		() => {
// 			alpine.hasErrorOccurred = true;
// 			alpine.complete(wire, alpine);
// 			wire.set("uploads." + fileIdx + ".stage", "error");
// 		},
// 		(event: UploadEvent) => {
// 			const numUploaded = alpine.chnkStarts[fileIdx] / alpine.chunkSize;
// 			alpine.progress[fileIdx] =
// 				(numUploaded / alpine.numChunks[fileIdx]) * 100 + event.detail.progress / alpine.numChunks[fileIdx];
// 		},
// 	);
// }

// function start(): void {
// 	for (let index = 0; index < Math.min(upload_processing_limit.value, fileList.value.length); index++) {
// 		process(index);
// 		latestFileIdx.value = index;
// 	}
// }
function closeCallback() {
	visible.value = false;
	files.value = [];
	emit("close");
}

watch(
	() => props.visible,
	(value) => {
		visible.value = value;
		files.value = [];
	},
);
</script>
