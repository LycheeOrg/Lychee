<template>
	<UModal v-model:open="is_upload_visible" :dismissible="true">
		<template #body>
			<div v-if="setup" class="w-full flex flex-col">
				<div v-if="counts.files > 0" class="flex flex-wrap justify-center w-full">
					<template v-if="counts.completed === counts.files">
						<span v-if="counts.errors > 0" class="w-full text-center text-error font-bold">{{
							$t("dialogs.upload.completed_with_errors", { errors: String(counts.errors) })
						}}</span>
						<span v-else-if="counts.warnings > 0" class="w-full text-center text-warning font-bold">{{
							$t("dialogs.upload.completed_with_warnings", { warnings: String(counts.warnings) })
						}}</span>
						<span v-else class="w-full text-center text-highlighted font-bold">{{ $t("dialogs.upload.completed") }}</span>
					</template>
					<span v-else class="w-full text-center">{{ $t("dialogs.upload.uploaded") }} {{ counts.completed }} / {{ counts.files }}</span>
					<UProgress
						class="w-full"
						:model-value="Math.round((counts.completed * 100) / counts.files)"
						:color="
							counts.completed === counts.files
								? counts.errors > 0
									? 'error'
									: counts.warnings > 0
										? 'warning'
										: 'success'
								: 'primary'
						"
					/>
				</div>
				<div v-if="counts.files > 0" class="w-full h-48 overflow-y-auto py-4 pr-3">
					<UploadingLine
						v-for="(uploadable, index) in list_upload_files"
						:key="uploadable.uid"
						:file="uploadable.file"
						:album-id="uploadable.album_id ?? albumId"
						:album-title="uploadable.albumTitle"
						:status="uploadable.status"
						:message="uploadable.message"
						:index="index"
						:chunk-size="setup.upload_chunk_size"
						:apply-watermark="applyWatermark"
						@upload:completed="uploadCompleted"
					/>
				</div>
				<div v-if="counts.files === 0" class="w-full flex flex-col items-center gap-4">
					<div
						v-show="isDropping"
						class="absolute flex items-center justify-center bg-primary-500 opacity-90 w-full"
						@dragover.prevent="isDropping = true"
						@dragleave.prevent="isDropping = false"
						@drop="upload"
					>
						<span class="text-3xl">{{ $t("dialogs.upload.release") }}</span>
					</div>
					<label
						class="flex flex-col w-full items-center justify-center hover:text-highlighted dark:border-neutral-900 dark:hover:bg-neutral-900/10 dark:hover:border-neutral-950 border shadow cursor-pointer h-1/2 rounded-2xl p-6"
						for="myFiles"
					>
						<h3 class="text-xl text-center">{{ $t("dialogs.upload.select") }}</h3>
						<em class="italic text-highlighted hover:text-muted">{{ $t("dialogs.upload.drag") }}</em>
					</label>
					<input id="myFiles" type="file" multiple class="hidden" @change="upload" />
					<div v-if="setup?.can_watermark_optout" class="flex items-center justify-center gap-2">
						<label for="watermark-toggle" class="cursor-pointer">{{ $t("dialogs.upload.apply_watermark") }}</label>
						<USwitch id="watermark-toggle" v-model="applyWatermark" :disabled="showCancel" />
					</div>
				</div>
			</div>
			<div v-else>
				{{ $t("dialogs.upload.loading") }}
			</div>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton v-if="showCancel" color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="cancel">
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton
					v-if="!showResume"
					color="neutral"
					variant="soft"
					class="flex-1 justify-center font-bold"
					:disabled="showCancel"
					@click="close"
				>
					{{ $t("dialogs.button.close") }}
				</UButton>
				<UButton v-else color="neutral" class="flex-1 justify-center font-bold" @click="() => uploadNext()">
					{{ $t("dialogs.upload.resume") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { computed, onMounted, onUnmounted, Ref, ref, watch } from "vue";
import UploadingLine from "@/v8/components/forms/upload/UploadingLine.vue";
import AlbumService from "@/services/album-service";
import { useRandomId } from "@/composables/useRandomId";
import { useRoute } from "vue-router";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";

const togglableStore = useTogglablesStateStore();
const { is_upload_visible, list_upload_files, upload_config: setup } = storeToRefs(togglableStore);
const generateId = useRandomId();
const route = useRoute();

const albumId = ref(route.params.albumId ?? (null as string | null)) as Ref<string | null>;
const applyWatermark = ref(true);

const emits = defineEmits<{
	refresh: [];
}>();

const shouldScroll = ref(true);
const isDropping = ref(false);
const showCancel = computed(() => counts.value.files > 0 && counts.value.completed < counts.value.files);
const showResume = computed(() => counts.value.waiting > 0 && counts.value.uploading === 0);

const counts = computed(() => {
	return {
		files: list_upload_files.value.length,
		waiting: list_upload_files.value.filter((f) => f.status === "waiting").length,
		completed: list_upload_files.value.filter((f) => f.status === "done" || f.status === "error" || f.status === "warning").length,
		uploading: list_upload_files.value.filter((f) => f.status === "uploading").length,
		errors: list_upload_files.value.filter((f) => f.status === "error").length,
		warnings: list_upload_files.value.filter((f) => f.status === "warning").length,
	};
});

function upload(event: Event) {
	// countCompleted.value = 0;
	const target = event.target as HTMLInputElement;
	if (target.files === null) {
		return;
	}

	for (let i = 0; i < target.files.length; i++) {
		list_upload_files.value.push({ uid: generateId(), file: target.files[i], status: "waiting" });
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

function uploadCompleted(index: number, status: "done" | "error" | "warning", message: string | undefined) {
	list_upload_files.value[index].status = status;
	list_upload_files.value[index].message = message;

	uploadNext(index, 1);

	// Only refresh if all uploads are done.
	if (counts.value.completed === counts.value.files) {
		// Clear cache for the route-level album and any per-file album_id overrides (folder-drop targets).
		const albumIds = new Set<string>([albumId.value ?? "unsorted"]);
		list_upload_files.value.forEach((f) => {
			if (f.album_id) albumIds.add(f.album_id);
		});
		albumIds.forEach((id) => AlbumService.clearCache(id));
		emits("refresh");

		if (setup.value?.close_upload_on_success && counts.value.errors === 0 && counts.value.warnings === 0) {
			close();
		}
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
	addEventListener("scroll", disableAutoScroll);
});
onUnmounted(() => {
	removeEventListener("scroll", disableAutoScroll);
});
</script>
