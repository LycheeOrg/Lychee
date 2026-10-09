<template>
	<UModal v-model:open="is_upload_visible" :dismissible="!showCancel" :close="!showCancel">
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
				<div v-if="counts.files > 0" ref="listBox" class="w-full h-72 overflow-y-auto pr-3">
					<div class="relative w-full" :style="{ height: `${totalSize}px` }">
						<div
							v-for="row in virtualRows"
							:key="row.file.uid"
							:ref="measureRow"
							:data-index="row.index"
							class="absolute top-0 inset-x-0"
							:style="{ transform: `translateY(${row.start}px)` }"
						>
							<UploadingLine
								:uid="row.file.uid"
								:file="row.file.file"
								:album-id="row.file.album_id ?? albumId"
								:album-title="row.file.albumTitle"
								:status="row.file.status"
								:message="row.file.message"
								:index="row.index"
								:chunk-size="setup.upload_chunk_size"
								:apply-watermark="applyWatermark"
								:scroll-root="listBox"
								@upload:completed="uploadCompleted"
							/>
						</div>
					</div>
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
						class="flex flex-col w-full items-center justify-center hover:text-highlighted border-default hover:bg-elevated/50 hover:border-accented border shadow cursor-pointer h-1/2 rounded-2xl p-6"
						for="myFiles"
					>
						<h3 class="text-xl text-center">{{ $t("dialogs.upload.select") }}</h3>
						<em class="italic text-highlighted hover:text-muted">{{ $t("dialogs.upload.drag") }}</em>
					</label>
					<input id="myFiles" type="file" multiple class="hidden" @change="upload" />
					<div v-if="setup?.can_watermark_optout" class="flex items-center justify-center">
						<USwitch v-model="applyWatermark" :disabled="showCancel" :label="$t('dialogs.upload.apply_watermark')" />
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
				<UButton variant="solid" v-else color="neutral" class="flex-1 justify-center font-bold" @click="() => uploadNext()">
					{{ isFreshQueue ? $t("dialogs.upload.start") : $t("dialogs.upload.resume") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { type ComponentPublicInstance, computed, onMounted, onUnmounted, Ref, ref, useTemplateRef, watch } from "vue";
import { defaultRangeExtractor, type Range, useVirtualizer } from "@tanstack/vue-virtual";
import UploadingLine from "@/v8/components/forms/upload/UploadingLine.vue";
import AlbumService from "@/services/album-service";
import { useRandomId } from "@/composables/useRandomId";
import { useRoute } from "vue-router";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { hasEnded, uploadingIndexes, withPinnedIndexes } from "@/v8/utils/uploadList";
import { releaseUploadThumbnails } from "@/v8/utils/uploadThumbnail";

/** Height of a row whose name and status fit on one line (the h-8 miniature box); actual heights are measured. */
const ROW_ESTIMATE_PX = 32;

const togglableStore = useTogglablesStateStore();
const { is_upload_visible, list_upload_files, upload_config: setup } = storeToRefs(togglableStore);
const generateId = useRandomId();
const route = useRoute();

const listBox = useTemplateRef<HTMLDivElement>("listBox");
const albumId = ref(route.params.albumId ?? (null as string | null)) as Ref<string | null>;
const applyWatermark = ref(true);

const emits = defineEmits<{
	refresh: [];
}>();

const shouldScroll = ref(true);
const isDropping = ref(false);
const showCancel = computed(() => counts.value.files > 0 && counts.value.completed < counts.value.files);
const showResume = computed(() => counts.value.waiting > 0 && counts.value.uploading === 0);
const isFreshQueue = computed(() => counts.value.completed === 0 && counts.value.uploading === 0);

const counts = computed(() => {
	return {
		files: list_upload_files.value.length,
		waiting: list_upload_files.value.filter((f) => f.status === "waiting").length,
		completed: list_upload_files.value.filter((f) => hasEnded(f.status)).length,
		uploading: list_upload_files.value.filter((f) => f.status === "uploading").length,
		errors: list_upload_files.value.filter((f) => f.status === "error").length,
		warnings: list_upload_files.value.filter((f) => f.status === "warning").length,
	};
});

// Only rows near the visible part of the list are mounted (Feature 087). Each row runs its
// own upload and reports through an event an unmounted row cannot emit, so running rows
// are added to the range and stay mounted wherever the list is scrolled.
const virtualizer = useVirtualizer(
	computed(() => {
		const pinned = uploadingIndexes(list_upload_files.value);
		return {
			count: list_upload_files.value.length,
			getScrollElement: () => listBox.value,
			estimateSize: () => ROW_ESTIMATE_PX,
			overscan: 6,
			gap: 4,
			paddingStart: 16,
			paddingEnd: 16,
			getItemKey: (index: number) => list_upload_files.value[index].uid,
			rangeExtractor: (range: Range) => withPinnedIndexes(defaultRangeExtractor(range), pinned),
		};
	}),
);

const totalSize = computed(() => virtualizer.value.getTotalSize());
const virtualRows = computed(() =>
	virtualizer.value.getVirtualItems().map((item) => ({ index: item.index, start: item.start, file: list_upload_files.value[item.index] })),
);

function measureRow(el: Element | ComponentPublicInstance | null) {
	virtualizer.value.measureElement(el as Element | null);
}

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
	// Top up to the limit based on how many are already uploading, rather than assuming a
	// cold start — files queued asynchronously (e.g. folder drop) trickle in one at a time,
	// so this may be called repeatedly while some uploads are already in flight.
	const limit = max_processing_limit ?? setup.value?.upload_processing_limit ?? 1;
	const availableSlots = limit - counts.value.uploading;
	if (availableSlots <= 0) {
		return;
	}

	let started = 0;
	let lastIdx = -1;
	for (let i = searchIndex; i < list_upload_files.value.length && started < availableSlots; i++) {
		if (list_upload_files.value[i].status === "waiting") {
			list_upload_files.value[i].status = "uploading";
			lastIdx = i;
			started++;
		}
	}

	if (lastIdx !== -1) {
		virtualizer.value.scrollToIndex(lastIdx, { align: "center", behavior: "smooth" });
	}
}

function uploadCompleted(index: number, status: "done" | "error" | "warning", message: string | undefined) {
	list_upload_files.value[index].status = status;
	list_upload_files.value[index].message = message;

	uploadNext(index, setup.value?.upload_processing_limit);

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

function clearList() {
	list_upload_files.value = [];
	releaseUploadThumbnails();
}

function cancel() {
	is_upload_visible.value = false;
	clearList();
	applyWatermark.value = true;
	AlbumService.clearCache(albumId.value ?? "unsorted");
	emits("refresh");
}

function close() {
	clearList();
	is_upload_visible.value = false;
	applyWatermark.value = true;
}

// Auto-start as soon as files are queued, whichever path queued them (window drop,
// in-modal drop, file picker, paste, folder drop) and regardless of whether the
// modal was already open — the modal's own visibility isn't a reliable start signal.
watch(
	() => list_upload_files.value.length,
	() => {
		if (counts.value.waiting > 0) {
			uploadNext(0, setup.value?.upload_processing_limit);
		}
	},
);

watch(
	() => route.params.albumId,
	(newAlbumId, _oldAlbumId) => {
		albumId.value = newAlbumId as string | null;
		if (!is_upload_visible.value) {
			clearList();
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
