<template>
	<Card
		v-if="data !== undefined && data.missing_albums !== 0 && data.missing_photos !== 0"
		class="min-h-40 dark:bg-surface-800 shadow shadow-surface-950/30 rounded-lg relative"
		pt:body:class="min-h-40 h-full"
		pt:content:class="h-full flex justify-between flex-col"
	>
		<template #title>
			<div class="text-center">
				{{ $t("maintenance.statistics-check.title") }}
			</div>
		</template>
		<template #content>
			<ScrollPanel class="w-full h-40 text-sm text-muted-color">
				<div class="w-full text-left" v-if="!loading">
					{{ sprintf($t("maintenance.statistics-check.missing_albums"), data.missing_albums) }}<br />
					{{ sprintf($t("maintenance.statistics-check.missing_photos"), data.missing_photos) }}<br />
				</div>
				<ProgressSpinner v-if="loading" class="w-full"></ProgressSpinner>
			</ScrollPanel>
			<div class="flex gap-4 mt-1">
				<Button severity="primary" v-if="!loading" class="w-full font-bold border-none" @click="exec">
					{{ $t("maintenance.statistics-check.button") }}
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
import MaintenanceService from "@/services/maintenance-service";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";

const data = ref<App.Http.Resources.Diagnostics.StatisticsCheckResource | undefined>(undefined);
const loading = ref(false);
const toast = useToast();

function load() {
	loading.value = true;
	MaintenanceService.statisticsIntegrityCheckGet()
		.then((response) => {
			data.value = response.data;
			loading.value = false;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			loading.value = false;
		});
}

function exec() {
	loading.value = true;
	MaintenanceService.statisticsIntegrityCheckDo()
		.then((response) => {
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
			data.value = response.data;
			loading.value = false;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			loading.value = false;
		});
}

load();
</script>

<style lang="css" scoped>
.lychee-dark .p-card {
	--p-card-background: var(--p-surface-800);
}
</style>
