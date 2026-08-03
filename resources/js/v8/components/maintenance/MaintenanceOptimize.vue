<template>
	<MaintenanceRow>
		<template #title>{{ $t("maintenance.optimize.title") }}</template>
		<span v-if="data.length === 0 && !loading">{{ $t("maintenance.optimize.description") }}</span>
		<LycheeLoadingIcon fast v-if="loading && data.length === 0" class="inline-block text-2xl" />
		<span v-if="data.length > 0">{{ $t("toasts.success") }}</span>
		<template #actions>
			<UButton variant="soft" v-if="data.length === 0 && !loading" color="primary" @click="exec">
				{{ $t("maintenance.optimize.button") }}
			</UButton>
		</template>
	</MaintenanceRow>
	<pre v-if="data.length > 0" class="text-2xs max-h-40 overflow-y-auto mb-3">{{ data.join("\n") }}</pre>
</template>

<script setup lang="ts">
import { ref } from "vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import MaintenanceRow from "@/v8/components/maintenance/MaintenanceRow.vue";
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
