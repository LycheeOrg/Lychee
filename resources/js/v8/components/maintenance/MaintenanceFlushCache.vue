<template>
	<UCard class="min-h-40 relative" :ui="{ body: 'h-full flex flex-col justify-between gap-4' }">
		<template #header>
			<div class="text-center">
				{{ $t("maintenance.flush-cache.title") }}
			</div>
		</template>
		<div class="w-full h-40 overflow-y-auto text-sm text-muted">
			<div v-if="!loading">{{ $t("maintenance.flush-cache.description") }}</div>
			<Spinner v-if="loading" class="w-full" />
		</div>
		<div class="flex gap-4 mt-1">
			<UButton v-if="!loading" color="warning" class="w-full justify-center" @click="exec">
				{{ $t("maintenance.flush-cache.button") }}
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

const loading = ref(false);
const toast = useAppToast();

function exec() {
	loading.value = true;
	MaintenanceService.flushDo()
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
			loading.value = false;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			loading.value = false;
		});
}
</script>
