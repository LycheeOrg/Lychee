<template>
	<UCard v-if="data && data.is_docker !== true" class="min-h-40 relative" :ui="{ body: 'h-full flex flex-col justify-between gap-4' }">
		<template #header>
			<div class="text-center">
				{{ $t("maintenance.update.title") }}
			</div>
		</template>
		<div class="w-full h-40 overflow-y-auto text-center text-muted text-sm">
			{{ data.channel_name }}<br />
			{{ data.info }}<br />
			{{ data.extra }}
		</div>
		<template #footer>
			<UButton v-if="canCheck" color="warning" class="w-full justify-center" @click="check">{{ $t("maintenance.update.check-button") }}</UButton>
			<UButton v-if="canUpdate" color="primary" class="w-full justify-center" to="/Update" target="_blank" rel="noopener">
				{{ $t("maintenance.update.update-button") }}
			</UButton>
			<div v-if="!canCheck && !canUpdate && !loading" class="w-full text-center">
				{{ $t("maintenance.update.no-pending-updates") }}
			</div>
		</template>
	</UCard>
</template>

<script setup lang="ts">
import { ref } from "vue";
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
