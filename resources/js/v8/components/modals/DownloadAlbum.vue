<template>
	<UModal v-model:open="open">
		<template #body>
			<div class="flex flex-col relative w-full text-sm rounded-md">
				<div v-if="is_downloading" class="flex flex-col gap-1 justify-center">
					<p class="text-muted text-center mb-2">
						{{ $t("gallery.download_album") }}
					</p>
					<p class="text-center">{{ $t("gallery.downloading_part", { current: String(current_chunk), total: String(total_chunks) }) }}</p>
				</div>
				<div v-else class="flex flex-col gap-1 justify-center">
					<p class="text-muted text-center mb-2">
						{{ $t("gallery.download_album") }}
					</p>
					<UButton
						v-if="is_raw_download_enabled"
						color="neutral"
						class="w-full justify-center"
						icon="prime:cloud-download"
						@click="download('RAW')"
					>
						{{ $t("gallery.raw") }}
					</UButton>
					<UButton color="neutral" class="w-full justify-center" icon="prime:cloud-download" @click="download('ORIGINAL')">
						{{ $t("gallery.original") }}
					</UButton>
					<UButton
						v-if="is_medium2x_download_enabled"
						color="neutral"
						class="w-full justify-center"
						icon="prime:cloud-download"
						@click="download('MEDIUM2X')"
					>
						{{ $t("gallery.medium_hidpi") }}
					</UButton>
					<UButton
						v-if="is_medium_download_enabled"
						color="neutral"
						class="w-full justify-center"
						icon="prime:cloud-download"
						@click="download('MEDIUM')"
					>
						{{ $t("gallery.medium") }}
					</UButton>
					<UButton
						v-if="is_small2x_download_enabled"
						color="neutral"
						class="w-full justify-center"
						icon="prime:cloud-download"
						@click="download('SMALL2X')"
					>
						{{ $t("gallery.small_hidpi") }}
					</UButton>
					<UButton
						v-if="is_small_download_enabled"
						color="neutral"
						class="w-full justify-center"
						icon="prime:cloud-download"
						@click="download('SMALL')"
					>
						{{ $t("gallery.small") }}
					</UButton>
					<UButton
						v-if="is_thum2x_download_enabled"
						color="neutral"
						class="w-full justify-center"
						icon="prime:cloud-download"
						@click="download('THUMB2X')"
					>
						{{ $t("gallery.thumb_hidpi") }}
					</UButton>
					<UButton
						v-if="is_thumb_download_enabled"
						color="neutral"
						class="w-full justify-center"
						icon="prime:cloud-download"
						@click="download('THUMB')"
					>
						{{ $t("gallery.thumb") }}
					</UButton>
				</div>
			</div>
		</template>
		<template #footer>
			<UButton color="neutral" variant="ghost" class="w-full font-bold justify-center" @click="closeCallback">
				{{ $t("dialogs.button.close") }}
			</UButton>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import PhotoService from "@/services/photo-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { trans } from "laravel-vue-i18n";
import { storeToRefs } from "pinia";
import { useAppToast } from "@/v8/composables/useAppToast";
import { computed, ref } from "vue";

const lycheeState = useLycheeStateStore();
const {
	is_raw_download_enabled,
	is_thumb_download_enabled,
	is_thum2x_download_enabled,
	is_small_download_enabled,
	is_small2x_download_enabled,
	is_medium_download_enabled,
	is_medium2x_download_enabled,
	is_download_archive_chunked,
} = storeToRefs(lycheeState);

const toast = useAppToast();

const open = defineModel<boolean>("open", { default: false });

const props = defineProps<{
	albumIds?: string[];
	photoIds?: string[];
	fromId?: string | null;
}>();

const is_photo_mode = computed(() => (props.photoIds?.length ?? 0) > 0);

const is_downloading = ref(false);
const current_chunk = ref(0);
const total_chunks = ref(0);

function closeCallback() {
	open.value = false;
	is_downloading.value = false;
}

function downloadChunked(variant: App.Enum.DownloadVariantType) {
	is_downloading.value = true;
	current_chunk.value = 0;
	total_chunks.value = 0;

	const countPromise = is_photo_mode.value
		? PhotoService.getChunkCount(props.photoIds!, props.fromId ?? null, variant)
		: AlbumService.getChunkCount(props.albumIds!, variant);

	countPromise
		.then(function (response) {
			total_chunks.value = response.data.total_chunks;

			function downloadNext(chunk: number): Promise<void> {
				if (!is_downloading.value || chunk > total_chunks.value) {
					is_downloading.value = false;
					open.value = false;
					return Promise.resolve();
				}
				current_chunk.value = chunk;
				const chunkPromise = is_photo_mode.value
					? PhotoService.downloadChunk(props.photoIds!, props.fromId ?? null, variant, chunk)
					: AlbumService.downloadChunk(props.albumIds!, variant, chunk);
				return chunkPromise.then(function () {
					return downloadNext(chunk + 1);
				});
			}

			return downloadNext(1);
		})
		.catch(function (err: unknown) {
			is_downloading.value = false;
			toast.add({ severity: "error", summary: trans("gallery.download_error"), detail: String(err), life: 5000 });
		});
}

function download(variant: App.Enum.DownloadVariantType) {
	if (is_download_archive_chunked.value) {
		downloadChunked(variant);
	} else if (is_photo_mode.value) {
		PhotoService.download(props.photoIds!, props.fromId ?? undefined, variant);
		open.value = false;
	} else {
		AlbumService.download(props.albumIds!, variant);
		open.value = false;
	}
}
</script>
