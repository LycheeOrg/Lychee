<template>
	<UCard class="min-h-40 relative" :ui="{ body: 'h-full flex flex-col justify-between gap-4' }">
		<template #header>
			<div class="text-center">
				{{ $t("maintenance.optimize.title") }}
			</div>
		</template>
		<div class="w-full h-40 overflow-y-auto text-sm text-muted">
			<div v-if="data.length === 0 && !loading">{{ $t("maintenance.optimize.description") }}</div>
			<Spinner v-if="loading && data.length === 0" class="w-full" />
			<pre v-if="data.length > 0" class="text-2xs m-4">{{ data.join("\n") }}</pre>
		</div>
		<div class="flex gap-4 mt-1">
			<UButton v-if="data.length === 0 && !loading" color="warning" class="w-full justify-center" @click="exec">
				{{ $t("maintenance.optimize.button") }}
			</UButton>
		</div>
	</UCard>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Spinner from "@/v8/components/Spinner.vue";
import MaintenanceService from "@/services/maintenance-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";

const data = ref<string[]>([]);
const loading = ref(false);
const toast = useAppToast();

function exec() {
	loading.value = true;
	MaintenanceService.optimizeDo()
		.then((response) => {
			data.value = response.data;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			loading.value = false;
		});
}
</script>
