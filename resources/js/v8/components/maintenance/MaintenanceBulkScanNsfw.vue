<template>
	<MaintenanceRow>
		<template #title>{{ $t("maintenance.bulk-scan-nsfw.title") }}</template>
		<span v-if="!loading">{{ $t("maintenance.bulk-scan-nsfw.description") }}</span>
		<LycheeLoadingIcon fast v-if="loading" class="inline-block text-2xl" />
		<template #actions>
			<UButton v-if="!loading" color="primary" variant="soft" @click="exec">
				{{ $t("maintenance.bulk-scan-nsfw.button") }}
			</UButton>
		</template>
	</MaintenanceRow>
</template>

<script setup lang="ts">
import { ref } from "vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import MaintenanceRow from "@/v8/components/maintenance/MaintenanceRow.vue";
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
