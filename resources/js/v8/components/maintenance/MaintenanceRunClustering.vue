<template>
	<UCard v-if="data !== undefined && data !== 0" class="min-h-40 relative bg-muted/50">
		<template #header>
			<div class="text-center font-bold">
				{{ $t("people.run_clustering") }}
			</div>
		</template>
		<div class="w-full h-40 overflow-y-auto text-sm text-muted">
			<div v-if="!loading" class="w-full text-center">{{ $t("maintenance.run-clustering.description") }}</div>
			<Spinner v-if="loading" class="w-full" />
		</div>
		<template #footer>
			<UButton v-if="data !== 0 && !loading" color="primary" class="w-full justify-center" @click="exec">
				{{ $t("people.run_clustering") }}
			</UButton>
		</template>
	</UCard>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Spinner from "@/v8/components/Spinner.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import MaintenanceService from "@/services/maintenance-service";

const data = ref<number | undefined>(undefined);
const loading = ref(false);
const toast = useAppToast();

function load() {
	loading.value = true;
	MaintenanceService.runFaceClusteringCheck().then((response) => {
		data.value = response.data;
		loading.value = false;
	});
}

function exec() {
	loading.value = true;
	MaintenanceService.runFaceClusteringDo()
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("maintenance.run-clustering.success"), life: 3000 });
			loading.value = false;
		})
		.catch((e) => {
			if (e.response.status !== 501 && e.response.data.message !== "Feature 'v8' is disabled") {
				toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			}
			loading.value = false;
		})
		.finally(load);
}

load();
</script>
