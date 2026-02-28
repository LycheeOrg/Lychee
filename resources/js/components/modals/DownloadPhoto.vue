<template>
	<Dialog
		v-if="photoStore.photo"
		v-model:visible="visible"
		modal
		pt:root:class="border-none"
		pt:mask:style="backdrop-filter: blur(2px)"
		@hide="closeCallback"
	>
		<template #container="{ closeCallback }">
			<div class="flex flex-col relative max-w-md w-full text-sm rounded-md">
				<div class="flex flex-col gap-1 justify-center p-9">
					<template v-for="(sv, svid) in photoStore.photo.size_variants" :key="`sv-${svid}`">
						<Button
							v-if="sv?.locale && isDownloadable(sv.type)"
							severity="contrast"
							class="w-full dark:border-surface-900"
							@click="download(sv.type)"
						>
							<i class="pi pi-cloud-download"></i> {{ sv?.locale }} - {{ sv?.width }}x{{ sv?.height }} ({{ sv?.filesize }})
						</Button>
					</template>
					<template v-if="photoStore.photo.precomputed.is_livephoto">
						<Button severity="contrast" class="w-full dark:border-surface-900" @click="downloadVariant('LIVEPHOTOVIDEO')">
							<i class="pi pi-cloud-download"></i> {{ $t("gallery.live_video") }} - {{ photoStore.photo.preformatted.resolution }}
						</Button>
					</template>
				</div>
				<div class="flex justify-center">
					<Button severity="secondary" class="w-full font-bold border-none rounded-none rounded-b-xl" @click="closeCallback">
						{{ $t("dialogs.button.close") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import PhotoService from "@/services/photo-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { usePhotoStore } from "@/stores/PhotoState";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
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
} = storeToRefs(lycheeState);

const photoStore = usePhotoStore();

const visible = defineModel("visible", { default: false });

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

function download(sv: number) {
	if (photoStore.photo === undefined) {
		return;
	}
	PhotoService.download([photoStore.photo.id], getParentId(), svtoVariant(sv));
}

function downloadVariant(variant: App.Enum.DownloadVariantType) {
	if (photoStore.photo === undefined) {
		return;
	}
	PhotoService.download([photoStore.photo.id], getParentId(), variant);
}
</script>
