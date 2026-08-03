<template>
	<MaintenanceRow v-if="data !== undefined && data.missing_albums !== 0 && data.missing_photos !== 0">
		<template #title>{{ $t("maintenance.statistics-check.title") }}</template>
		<span v-if="!loading"
			>{{ sprintf($t("maintenance.statistics-check.missing_albums"), data.missing_albums) }} ·
			{{ sprintf($t("maintenance.statistics-check.missing_photos"), data.missing_photos) }}</span
		>
		<LycheeLoadingIcon fast v-if="loading" class="inline-block text-2xl" />
		<template #actions>
			<UButton variant="soft" v-if="!loading" color="primary" class="font-bold" @click="exec">
				{{ $t("maintenance.statistics-check.button") }}
			</UButton>
		</template>
	</MaintenanceRow>
</template>

<script setup lang="ts">
import { ref } from "vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import MaintenanceRow from "@/v8/components/maintenance/MaintenanceRow.vue";
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
