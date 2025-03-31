<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none" pt:mask:style="backdrop-filter: blur(2px)" @hide="closeCallback">
		<template #container="{ closeCallback }">
			<div class="flex flex-col relative max-w-md w-full text-sm rounded-md">
				<div class="flex flex-col gap-1 justify-center p-9">
					<template v-for="sv in props.photo.size_variants">
						<Button
							severity="contrast"
							v-if="sv?.locale && isDownloadable(sv.type)"
							class="w-full dark:border-surface-900"
							@click="download(sv.type)"
						>
							<i class="pi pi-cloud-download"></i> {{ sv?.locale }} - {{ sv?.width }}x{{ sv?.height }} ({{ sv?.filesize }})
						</Button>
					</template>
					<template v-if="props.photo.precomputed.is_livephoto">
						<Button severity="contrast" class="w-full dark:border-surface-900" @click="download(7)">
							<i class="pi pi-cloud-download"></i> {{ $t("gallery.live_video") }} - {{ props.photo.preformatted.resolution }}
						</Button>
					</template>
				</div>
				<div class="flex justify-center">
					<Button @click="closeCallback" severity="secondary" class="w-full font-bold border-none rounded-none rounded-bl-xl rounded-br-xl">
						{{ $t("dialogs.button.close") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import PhotoService from "@/services/photo-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import Dialog from "primevue/dialog";

const lycheeState = useLycheeStateStore();
const {
	is_thumb_download_enabled,
	is_thum2x_download_enabled,
	is_small_download_enabled,
	is_small2x_download_enabled,
	is_medium_download_enabled,
	is_medium2x_download_enabled,
} = storeToRefs(lycheeState);

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource;
}>();

const visible = defineModel("visible", { default: false });

function closeCallback() {
	visible.value = false;
}

// prettier-ignore
function svtoVariant(sv: number): App.Enum.DownloadVariantType {
	switch (sv) {
		case 0: return "ORIGINAL";
		case 1: return "MEDIUM2X";
		case 2: return "MEDIUM";
		case 3: return "SMALL2X";
		case 4: return "SMALL";
		case 5: return "THUMB2X";
		case 6: return "THUMB";
		default: return "LIVEPHOTOVIDEO";
	}
}

// prettier-ignore
function isDownloadable(sv: number): boolean {
	switch (sv) {
		case 0: return true;
		case 1: return is_medium2x_download_enabled.value;
		case 2: return is_medium_download_enabled.value;
		case 3: return is_small2x_download_enabled.value;
		case 4: return is_small_download_enabled.value;
		case 5: return is_thum2x_download_enabled.value;
		case 6: return is_thumb_download_enabled.value;
		default: return true;
	}
}

function download(sv: number) {
	PhotoService.download([props.photo.id], svtoVariant(sv));
}
</script>
