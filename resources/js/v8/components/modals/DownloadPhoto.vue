<template>
	<UModal v-if="photoStore.photo" v-model:open="visible" :dismissible="true">
		<template #body>
			<div class="flex flex-col gap-1 justify-center">
				<template v-for="(sv, svid) in photoStore.photo.size_variants" :key="`sv-${svid}`">
					<UButton v-if="sv?.locale && isDownloadable(sv.type)" color="neutral" class="w-full justify-center" @click="download(sv.type)">
						<UIcon name="prime:cloud-download" /> {{ sv?.locale }} - {{ sv?.width }}x{{ sv?.height }} ({{ sv?.filesize }})
					</UButton>
				</template>
				<template v-if="photoStore.photo.precomputed.is_livephoto">
					<UButton color="neutral" class="w-full justify-center" @click="downloadVariant('LIVEPHOTOVIDEO')">
						<UIcon name="prime:cloud-download" /> {{ $t("gallery.live_video") }} - {{ photoStore.photo.preformatted.resolution }}
					</UButton>
				</template>
			</div>
		</template>
		<template #footer>
			<UButton color="neutral" variant="soft" class="w-full justify-center font-bold" @click="closeCallback">
				{{ $t("dialogs.button.close") }}
			</UButton>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import PhotoService from "@/services/photo-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { usePhotoStore } from "@/stores/PhotoState";
import { trans } from "laravel-vue-i18n";
import { storeToRefs } from "pinia";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useRouter } from "vue-router";

const router = useRouter();
const { getParentId } = usePhotoRoute(router);

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
const photoStore = usePhotoStore();

const visible = defineModel<boolean>("open", { default: false });

function closeCallback() {
	visible.value = false;
}

// prettier-ignore
function svtoVariant(sv: number): App.Enum.DownloadVariantType {
	switch (sv) {
		case 0: return "RAW";
		case 1: return "ORIGINAL";
		case 2: return "MEDIUM2X";
		case 3: return "MEDIUM";
		case 4: return "SMALL2X";
		case 5: return "SMALL";
		case 6: return "THUMB2X";
		case 7: return "THUMB";
		default: return "LIVEPHOTOVIDEO";
	}
}

// prettier-ignore
function isDownloadable(sv: number): boolean {
	switch (sv) {
		case 0: return is_raw_download_enabled.value;
		case 1: return true;
		case 2: return is_medium2x_download_enabled.value;
		case 3: return is_medium_download_enabled.value;
		case 4: return is_small2x_download_enabled.value;
		case 5: return is_small_download_enabled.value;
		case 6: return is_thum2x_download_enabled.value;
		case 7: return is_thumb_download_enabled.value;
		case 8: return false; // Placeholder = string => not downloadable.
		default: return true;
	}
}

function downloadVariantChunked(photo_ids: string[], parent_id: string | null, variant: App.Enum.DownloadVariantType) {
	PhotoService.getChunkCount(photo_ids, parent_id, variant)
		.then(function (response) {
			const chunks = response.data.total_chunks;
			function downloadNext(chunk: number): Promise<void> {
				if (chunk > chunks) {
					return Promise.resolve();
				}
				return PhotoService.downloadChunk(photo_ids, parent_id, variant, chunk).then(function () {
					return downloadNext(chunk + 1);
				});
			}
			return downloadNext(1);
		})
		.catch(function (err: unknown) {
			toast.add({ severity: "error", summary: trans("gallery.download_error"), detail: String(err), life: 5000 });
		});
}

function download(sv: number) {
	if (photoStore.photo === undefined) {
		return;
	}
	const photo_ids = [photoStore.photo.id];
	const parent_id = getParentId() ?? null;
	const variant = svtoVariant(sv);
	if (is_download_archive_chunked.value) {
		downloadVariantChunked(photo_ids, parent_id, variant);
	} else {
		PhotoService.download(photo_ids, parent_id ?? undefined, variant);
	}
}

function downloadVariant(variant: App.Enum.DownloadVariantType) {
	if (photoStore.photo === undefined) {
		return;
	}
	const photo_ids = [photoStore.photo.id];
	const parent_id = getParentId() ?? null;
	if (is_download_archive_chunked.value) {
		downloadVariantChunked(photo_ids, parent_id, variant);
	} else {
		PhotoService.download(photo_ids, parent_id ?? undefined, variant);
	}
}
</script>
