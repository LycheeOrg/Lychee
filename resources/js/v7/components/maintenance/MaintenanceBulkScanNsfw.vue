<template>
	<Card
		class="min-h-40 dark:bg-surface-800 shadow shadow-surface-950/30 rounded-lg relative"
		pt:body:class="min-h-40 h-full"
		pt:content:class="h-full flex justify-between flex-col"
	>
		<template #title>
			<div class="text-center">
				{{ $t("maintenance.bulk-scan-nsfw.title") }}
			</div>
		</template>
		<template #content>
			<ScrollPanel class="w-full h-40 text-sm text-muted-color">
				<div v-if="!loading" class="w-full ltr:text-left rtl:text-right">
					{{ $t("maintenance.bulk-scan-nsfw.description") }}
				</div>
				<ProgressSpinner v-if="loading" class="w-full" />
			</ScrollPanel>
			<div class="flex gap-4 mt-1">
				<Button v-if="!loading" severity="primary" class="w-full border-none" @click="exec">
					{{ $t("maintenance.bulk-scan-nsfw.button") }}
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
import NsfwDetectionService from "@/services/nsfw-detection-service";

const loading = ref(false);
const toast = useToast();

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

<style lang="css" scoped>
.lychee-dark .p-card {
	--p-card-background: var(--p-surface-800);
}
</style>
