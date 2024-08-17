<template>
	<Dialog
		v-model:visible="visible"
		modal
		:pt="{
			root: 'border-none',
		}"
		@hide="closeCallback"
	>
		<template #container="{ closeCallback }">
			<div v-if="setup">
				<ScrollPanel v-if="files.length > 0" class="w-96 h-48 m-4 p-1 mr-5">
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
			</div>
			<div v-else>
				{{ $t("lychee.LOADING") }}
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
import UploadService from "@/services/upload-service";

type Uploadable = {
	file: File;
	status: string;
};

const props = withDefaults(
	defineProps<{
		visible: boolean;
		albumId: string | null;
	}>(),
	{
		visible: false,
	},
);

const setup = ref(undefined as undefined | App.Http.Resources.GalleryConfigs.UploadConfig);
const visible = ref(props.visible);
const albumId = ref(props.albumId);

const emit = defineEmits(["close"]);

const isDropping = ref(false);
const files = ref([] as Uploadable[]);

function load() {
	UploadService.getSetUp().then((response) => {
		setup.value = response.data;
	});
}

function upload(event: Event) {
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
	// Find the next one and start uploading.
	for (let i = index; i < files.value.length; i++) {
		if (files.value[i].status === "waiting") {
			files.value[i].status = "uploading";
			break;
		}
	}
}

function closeCallback() {
	visible.value = false;
	files.value = [];
	emit("close");
}

load();

watch(
	() => [props.visible, props.albumId],
	([newVisible, newAlbumId], [_oldVisible, _oldAlbumId]) => {
		visible.value = newVisible as boolean;
		albumId.value = newAlbumId as string | null;
		files.value = [];
	},
);
</script>
