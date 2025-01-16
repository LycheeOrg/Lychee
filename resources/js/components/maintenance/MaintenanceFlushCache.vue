<template>
	<Card class="min-h-40 dark:bg-surface-800 shadow shadow-surface-950/30 rounded-lg relative">
		<template #title>
			<div class="text-center">
				{{ $t("maintenance.flush-cache.title") }}
			</div>
		</template>
		<template #content>
			<ScrollPanel class="w-full h-40 text-muted-color text-sm">
				<div v-if="!loading">{{ $t("maintenance.flush-cache.description") }}</div>
				<ProgressSpinner v-if="loading" class="w-full"></ProgressSpinner>
			</ScrollPanel>
			<div class="flex gap-4 mt-1">
				<Button v-if="!loading" severity="primary" class="w-full border-none" @click="exec">{{
					$t("maintenance.flush-cache.button")
				}}</Button>
			</div>
		</template>
	</Card>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Button from "primevue/button";
import Card from "primevue/card";
import ProgressSpinner from "primevue/progressspinner";
import ScrollPanel from "primevue/scrollpanel";
import MaintenanceService from "@/services/maintenance-service";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";

const loading = ref(false);
const toast = useToast();

function exec() {
	loading.value = true;
	MaintenanceService.flushDo()
		.then((response) => {
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
			loading.value = false;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			loading.value = false;
		});
}
</script>

<style lang="css" scoped>
.lychee-dark .p-card {
	--p-card-background: var(--p-surface-800);
}
</style>
