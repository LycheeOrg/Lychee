<template>
	<Dialog v-model:visible="is_upload_visible" modal pt:root:class="border-none" :dismissable-mask="true">
		<template #container>
			<div v-if="setup" class="max-w-md w-full">
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
					/>
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
						:apply-watermark="applyWatermark"
						@upload:completed="uploadCompleted"
					/>
				</ScrollPanel>
				<div v-if="counts.files === 0" class="p-9 max-w-3xl w-full">
					<div
						v-show="isDropping"
						class="absolute flex items-center justify-center bg-primary-500 opacity-90"
						@dragover.prevent="isDropping = true"
						@dragleave.prevent="isDropping = false"
						@drop="upload"
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
					<input id="myFiles" type="file" multiple class="hidden" @change="upload" />
					<div v-if="setup?.can_watermark_optout" class="flex items-center justify-center gap-2 mt-4">
						<label for="watermark-toggle" class="cursor-pointer">{{ $t("dialogs.upload.apply_watermark") }}</label>
						<InputSwitch id="watermark-toggle" v-model="applyWatermark" :disabled="showCancel" />
					</div>
				</div>
			</div>
			<div v-else>
				{{ $t("dialogs.upload.loading") }}
			</div>
			<div class="flex justify-center">
				<Button
					v-if="showCancel"
					severity="secondary"
					class="w-full font-bold border-none border rounded-none ltr:rounded-bl-xl rtl:rounded-br-xl"
					@click="cancel"
				>
					{{ $t("dialogs.button.cancel") }}
				</Button>
				<Button
					v-if="!showResume"
					severity="secondary"
					class="w-full font-bold border-none border rounded-none ltr:rounded-br-xl rtl:rounded-bl-xl"
					:class="showCancel ? '' : 'ltr:rounded-bl-xl rtl:rounded-br-xl'"
					:disabled="showCancel"
					@click="close"
				>
					{{ $t("dialogs.button.close") }}
				</Button>
				<Button
					v-else
					severity="contrast"
					class="w-full font-bold border-none border rounded-none ltr:rounded-br-xl rtl:rounded-bl-xl"
					@click="() => uploadNext()"
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
import InputSwitch from "primevue/inputswitch";
import { computed, onMounted, onUnmounted, Ref, ref, watch } from "vue";
import UploadingLine from "@/components/forms/upload/UploadingLine.vue";
import ScrollPanel from "primevue/scrollpanel";
import UploadService from "@/services/upload-service";
import ProgressBar from "primevue/progressbar";
import AlbumService from "@/services/album-service";
import { useRoute } from "vue-router";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";

const togglableStore = useTogglablesStateStore();
const { is_upload_visible, list_upload_files } = storeToRefs(togglableStore);
const route = useRoute();

const setup = ref<App.Http.Resources.GalleryConfigs.UploadConfig | undefined>(undefined);
const albumId = ref(route.params.albumId ?? (null as string | null)) as Ref<string | null>;
const applyWatermark = ref(true);

const emits = defineEmits<{
	refresh: [];
}>();

const shouldScroll = ref(true);
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
		completed: list_upload_files.value.filter((f) => f.status === "done" || f.status === "error" || f.status === "warning").length,
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

function uploadNext(searchIndex = 0, max_processing_limit: number | undefined = undefined) {
	let offset = 0;
	let lastIdx = -1;
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
			lastIdx = i + offset;
		}
	}

	if (lastIdx !== -1) {
		document.getElementById(`upload${lastIdx}`)?.scrollIntoView({ behavior: "smooth", block: "center" });
	}
}

function uploadCompleted(index: number, status: "done" | "error" | "warning") {
	list_upload_files.value[index].status = status;

	uploadNext(index, 1);

	// Only refresh if all uploads are done.
	if (counts.value.completed === counts.value.files) {
		AlbumService.clearCache(albumId.value ?? "unsorted");
		emits("refresh");
	}
}

function cancel() {
	is_upload_visible.value = false;
	list_upload_files.value = [];
	applyWatermark.value = true;
	AlbumService.clearCache(albumId.value ?? "unsorted");
	emits("refresh");
}

function close() {
	list_upload_files.value = [];
	is_upload_visible.value = false;
	applyWatermark.value = true;
}

watch(
	() => is_upload_visible.value,
	() => {
		if (list_upload_files.value.length > 0) {
			uploadNext(0, setup.value?.upload_processing_limit);
		}
	},
);

watch(
	() => route.params.albumId,
	(newAlbumId, _oldAlbumId) => {
		albumId.value = newAlbumId as string | null;
		if (!is_upload_visible.value) {
			list_upload_files.value = [];
		}
	},
);

function disableAutoScroll() {
	shouldScroll.value = false;

	// Re-enable auto-scroll after 10 seconds of inactivity
	setTimeout(() => {
		shouldScroll.value = true;
	}, 10000);
}

onMounted(() => {
	load();
	addEventListener("scroll", disableAutoScroll);
});
onUnmounted(() => {
	removeEventListener("scroll", disableAutoScroll);
});
</script>
