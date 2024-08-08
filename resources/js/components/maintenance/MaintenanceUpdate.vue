<template>
	<Card v-if="data" class="min-h-40 shadow shadow-surface-950 rounded-lg relative bg-surface-800">
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
			<Button v-if="canCheck" severity="primary" class="w-full" @click="check">{{ $t("lychee.CHECK_FOR_UPDATE") }}</Button>
			<Button v-if="canUpdate" severity="primary" href="/Update" target="_blank" rel="noopener" class="w-full">{{
				$t("lychee.UPDATE")
			}}</Button>
			<div v-if="!canCheck && !canUpdate && !loading" class="w-full text-center">
				{{ $t("maintenance.update.no-pending-updates") }}
			</div>
		</template>
	</Card>
</template>

<script setup lang="ts">
import MaintenanceService from "@/services/maintenance-service";
import Button from "primevue/button";
import Card from "primevue/card";
import ScrollPanel from "primevue/scrollpanel";
import { ref } from "vue";

const data = ref(undefined as App.Http.Resources.Diagnostics.UpdateInfo | undefined);
const canCheck = ref(true);
const canUpdate = ref(false);
const loading = ref(false);

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

	MaintenanceService.updateCheck().then((response) => {
		(data.value as App.Http.Resources.Diagnostics.UpdateInfo).extra = response.data.extra;
		canUpdate.value = response.data.can_update;
		loading.value = false;
	});
}

load();
</script>
