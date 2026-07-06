<template>
	<UCard class="min-h-40 relative" :ui="{ body: 'h-full flex flex-col justify-between gap-4' }">
		<template #header>
			<div class="text-center">
				{{ $t("maintenance.bulk-scan-nsfw.title") }}
			</div>
		</template>
		<div class="w-full h-40 overflow-y-auto text-sm text-muted">
			<div v-if="!loading" class="w-full ltr:text-left rtl:text-right">
				{{ $t("maintenance.bulk-scan-nsfw.description") }}
			</div>
			<Spinner v-if="loading" class="w-full" />
		</div>
		<div class="flex gap-4 mt-1">
			<UButton v-if="!loading" color="primary" class="w-full font-bold justify-center" @click="exec">
				{{ $t("maintenance.bulk-scan-nsfw.button") }}
			</UButton>
		</div>
	</UCard>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Spinner from "@/v8/components/Spinner.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import NsfwDetectionService from "@/services/nsfw-detection-service";

const loading = ref(false);
const toast = useAppToast();

function exec() {
	loading.value = true;
	NsfwDetectionService.bulkScan()
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("maintenance.bulk-scan-nsfw.success"), life: 3000 });
		})
		.catch((e) => {
			if (e.response.status !== 501 && e.response.data.message !== "Feature 'v8' is disabled") {
				toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			}
			loading.value = false;
		})
		.finally(() => {
			loading.value = false;
		});
}
</script>
