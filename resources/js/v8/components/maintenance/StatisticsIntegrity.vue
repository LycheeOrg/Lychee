<template>
	<UCard
		v-if="data !== undefined && data.missing_albums !== 0 && data.missing_photos !== 0"
		class="min-h-40 relative bg-muted/50"
		:ui="{ body: 'h-full flex flex-col justify-between gap-4' }"
	>
		<template #header>
			<div class="text-center">
				{{ $t("maintenance.statistics-check.title") }}
			</div>
		</template>
		<div class="w-full h-40 overflow-y-auto text-sm text-muted">
			<div v-if="!loading" class="w-full text-left">
				{{ sprintf($t("maintenance.statistics-check.missing_albums"), data.missing_albums) }}<br />
				{{ sprintf($t("maintenance.statistics-check.missing_photos"), data.missing_photos) }}<br />
			</div>
			<Spinner v-if="loading" class="w-full" />
		</div>
		<template #footer>
			<UButton v-if="!loading" color="primary" class="w-full font-bold justify-center" @click="exec">
				{{ $t("maintenance.statistics-check.button") }}
			</UButton>
		</template>
	</UCard>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Spinner from "@/v8/components/Spinner.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import MaintenanceService from "@/services/maintenance-service";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";

const data = ref<App.Http.Resources.Diagnostics.StatisticsCheckResource | undefined>(undefined);
const loading = ref(false);
const toast = useAppToast();

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
