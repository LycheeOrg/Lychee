<template>
	<Card
		v-if="data && data.isDocker !== true"
		class="min-h-40 dark:bg-surface-800 shadow shadow-surface-950/30 rounded-lg relative"
		pt:body:class="min-h-40 h-full"
		pt:content:class="h-full flex justify-between flex-col"
	>
		<template #title>
			<div class="text-center">
				{{ $t("maintenance.update.title") }}
			</div>
		</template>
		<template #content>
			<ScrollPanel class="w-full h-40 text-center text-muted-color text-sm">
				{{ data.channelName }}<br />
				{{ data.info }}<br />
				{{ data.extra }}
			</ScrollPanel>
		</template>
		<template #footer>
			<Button v-if="canCheck" severity="primary" class="w-full border-none" @click="check">{{ $t("maintenance.update.check-button") }}</Button>
			<Button v-if="canUpdate" severity="primary" class="w-full border-none" href="/Update" target="_blank" rel="noopener">{{
				$t("maintenance.update.update-button")
			}}</Button>
			<div v-if="!canCheck && !canUpdate && !loading" class="w-full text-center">
				{{ $t("maintenance.update.no-pending-updates") }}
			</div>
		</template>
	</Card>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Button from "primevue/button";
import Card from "primevue/card";
import ScrollPanel from "primevue/scrollpanel";
import MaintenanceService from "@/services/maintenance-service";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";

const data = ref<App.Http.Resources.Diagnostics.UpdateInfo | undefined>(undefined);
const canCheck = ref(true);
const canUpdate = ref(false);
const loading = ref(false);
const toast = useToast();

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

<style lang="css" scoped>
.lychee-dark .p-card {
	--p-card-background: var(--p-surface-800);
}
</style>
