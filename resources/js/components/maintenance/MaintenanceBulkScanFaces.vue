<template>
	<Card
		class="min-h-40 dark:bg-surface-800 shadow shadow-surface-950/30 rounded-lg relative"
		pt:body:class="min-h-40 h-full"
		pt:content:class="h-full flex justify-between flex-col"
	>
		<template #title>
			<div class="text-center">
				{{ $t("people.scan_faces") }}
			</div>
		</template>
		<template #content>
			<ScrollPanel class="w-full h-40 text-sm text-muted-color">
				<div class="w-full text-center">{{ $t("maintenance.bulk-scan-faces.description") }}</div>
				<ProgressSpinner v-if="loading" class="w-full" />
			</ScrollPanel>
			<div class="flex gap-4 mt-1">
				<Button v-if="!loading" severity="primary" class="w-full font-bold border-none" @click="exec">
					{{ $t("people.scan_faces") }}
				</Button>
			</div>
		</template>
	</Card>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Button from "primevue/button";
import Card from "primevue/card";
import { useToast } from "primevue/usetoast";
import ProgressSpinner from "primevue/progressspinner";
import ScrollPanel from "primevue/scrollpanel";
import { trans } from "laravel-vue-i18n";
import FaceDetectionService from "@/services/face-detection-service";

const loading = ref(false);
const toast = useToast();

function exec() {
	loading.value = true;
	FaceDetectionService.bulkScan()
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.scan_success"), life: 3000 });
			loading.value = false;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
			loading.value = false;
		});
}
</script>

<style lang="css" scoped>
.lychee-dark .p-card {
	--p-card-background: var(--p-surface-800);
}
</style>
