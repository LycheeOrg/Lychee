<template>
	<Card v-if="data !== undefined && data.is_not_empty" class="min-h-40 shadow shadow-surface-950 rounded-lg relative bg-surface-800">
		<template #title>
			<div class="text-center">
				{{ title }}
			</div>
		</template>
		<template #content>
			<ScrollPanel class="w-full h-40 text-sm text-muted-color">
				<div v-html="description" class="w-full text-center"></div>
				<ProgressSpinner v-if="loading" class="w-full"></ProgressSpinner>
			</ScrollPanel>
			<div class="flex gap-4 mt-1">
				<Button v-if="data.is_not_empty && !loading" class="w-full bg-danger-800 hover:bg-danger-700 border-none text-white" @click="exec">{{
					$t("maintenance.cleaning.button")
				}}</Button>
			</div>
		</template>
	</Card>
</template>

<script setup lang="ts">
import ProgressSpinner from "primevue/progressspinner";
import MaintenanceService from "@/services/maintenance-service";
import Button from "primevue/button";
import Card from "primevue/card";
import { computed, ref } from "vue";
import ScrollPanel from "primevue/scrollpanel";
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";
import { useToast } from "primevue/usetoast";

const props = defineProps<{ path: string }>();

const data = ref(undefined as App.Http.Resources.Diagnostics.CleaningState | undefined);
const loading = ref(false);
const toast = useToast();

const title = computed(() => {
	return sprintf(trans("maintenance.cleaning.title"), data.value?.path);
});
const description = computed(() => {
	return sprintf(trans("maintenance.cleaning.description"), data.value?.base);
});

function load() {
	MaintenanceService.cleaningGet(props.path).then((response) => {
		data.value = response.data;
	});
}

function exec() {
	loading.value = true;
	MaintenanceService.cleaningDo(props.path).then((response) => {
		toast.add({ severity: "success", summary: "Success" });
		loading.value = false;
	});
}

load();
</script>
