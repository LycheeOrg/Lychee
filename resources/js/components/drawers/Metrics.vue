<template>
	<Drawer :closeOnEsc="false" v-model:visible="isMetricsOpen" position="right"> Metrics </Drawer>
</template>
<script setup lang="ts">
import MetricsService from "@/services/metrics-service";
import Drawer from "primevue/drawer";
import { ref } from "vue";
import { watch } from "vue";
import { Ref } from "vue";

const isMetricsOpen = defineModel("isMetricsOpen", { default: false }) as Ref<boolean>;

type LiveMetrics = {
	ago: string,
	action: string,
	title: string,
	count: number,
}
const data = ref<App.Http.Resources.Models.LiveMetricsResource[] | undefined>(undefined);
const prettifiedData = ref<LiveMetrics[] | undefined>(undefined);

function load() {
	MetricsService.get()
		.then((response) => {
			data.value = response.data;
			console.log(response.data);
		})
		.catch((error) => {
			console.error(error);
		});
}

function prettifyData() {
	prettifiedData.value = [];
	// Group by action.
	// Group by chunk of time,
	data.value
}

watch(
	() => isMetricsOpen.value,
	(newValue) => {
		if (newValue) {
			load();
		}
	},
);
</script>
