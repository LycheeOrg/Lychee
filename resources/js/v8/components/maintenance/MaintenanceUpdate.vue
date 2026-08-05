<template>
	<MaintenanceRow v-if="data && data.is_docker !== true">
		<template #title>{{ $t("maintenance.update.title") }}</template>
		{{ [data.channel_name, data.info, data.extra].filter(Boolean).join(" · ") }}
		<template #actions>
			<UButton variant="soft" v-if="canCheck" color="neutral" @click="check">{{ $t("maintenance.update.check-button") }}</UButton>
			<UButton variant="soft" v-if="canUpdate" color="primary" to="/Update" target="_blank" rel="noopener">
				{{ $t("maintenance.update.update-button") }}
			</UButton>
			<span v-if="!canCheck && !canUpdate && !loading" class="text-sm text-muted whitespace-nowrap">
				{{ $t("maintenance.update.no-pending-updates") }}
			</span>
		</template>
	</MaintenanceRow>
</template>

<script setup lang="ts">
import { ref } from "vue";
import MaintenanceRow from "@/v8/components/maintenance/MaintenanceRow.vue";
import MaintenanceService from "@/services/maintenance-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";

const data = ref<App.Http.Resources.Diagnostics.UpdateInfo | undefined>(undefined);
const canCheck = ref(true);
const canUpdate = ref(false);
const loading = ref(false);
const toast = useAppToast();

function load() {
	MaintenanceService.updateGet().then((response) => {
		data.value = response.data;
	});
}

function check() {
	if (data.value === undefined) {
		return;
	}
	loading.value = true;
	canCheck.value = false;

	MaintenanceService.updateCheck()
		.then((response) => {
			(data.value as App.Http.Resources.Diagnostics.UpdateInfo).extra = response.data.extra;
			canUpdate.value = response.data.can_update;
			loading.value = false;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			loading.value = false;
		});
}

load();
</script>
