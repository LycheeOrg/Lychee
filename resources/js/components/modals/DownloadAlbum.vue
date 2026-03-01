<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none" pt:mask:style="backdrop-filter: blur(2px)" @hide="closeCallback">
		<template #container="{ closeCallback }">
			<div class="flex flex-col relative max-w-md w-full text-sm rounded-md">
				<div class="flex flex-col gap-1 justify-center p-9">
					<p class="text-muted-color text-center mb-2">
						{{ $t("gallery.download_album") }}
					</p>
					<Button v-if="is_raw_download_enabled" severity="contrast" class="w-full dark:border-surface-900" @click="download('RAW')">
						<i class="pi pi-cloud-download"></i> {{ $t("gallery.raw") }}
					</Button>
					<Button severity="contrast" class="w-full dark:border-surface-900" @click="download('ORIGINAL')">
						<i class="pi pi-cloud-download"></i> {{ $t("gallery.original") }}
					</Button>
					<Button
						v-if="is_medium2x_download_enabled"
						severity="contrast"
						class="w-full dark:border-surface-900"
						@click="download('MEDIUM2X')"
					>
						<i class="pi pi-cloud-download"></i> {{ $t("gallery.medium_hidpi") }}
					</Button>
					<Button v-if="is_medium_download_enabled" severity="contrast" class="w-full dark:border-surface-900" @click="download('MEDIUM')">
						<i class="pi pi-cloud-download"></i> {{ $t("gallery.medium") }}
					</Button>
					<Button
						v-if="is_small2x_download_enabled"
						severity="contrast"
						class="w-full dark:border-surface-900"
						@click="download('SMALL2X')"
					>
						<i class="pi pi-cloud-download"></i> {{ $t("gallery.small_hidpi") }}
					</Button>
					<Button v-if="is_small_download_enabled" severity="contrast" class="w-full dark:border-surface-900" @click="download('SMALL')">
						<i class="pi pi-cloud-download"></i> {{ $t("gallery.small") }}
					</Button>
					<Button v-if="is_thum2x_download_enabled" severity="contrast" class="w-full dark:border-surface-900" @click="download('THUMB2X')">
						<i class="pi pi-cloud-download"></i> {{ $t("gallery.thumb_hidpi") }}
					</Button>
					<Button v-if="is_thumb_download_enabled" severity="contrast" class="w-full dark:border-surface-900" @click="download('THUMB')">
						<i class="pi pi-cloud-download"></i> {{ $t("gallery.thumb") }}
					</Button>
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
import AlbumService from "@/services/album-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import Dialog from "primevue/dialog";

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

const visible = defineModel("visible", { default: false });

const props = defineProps<{
	albumIds: string[];
}>();

function closeCallback() {
	visible.value = false;
}

function download(variant: App.Enum.DownloadVariantType) {
	AlbumService.download(props.albumIds, variant);
	visible.value = false;
}
</script>
