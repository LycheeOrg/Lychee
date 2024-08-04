<template>
	<Panel header="Errors" v-if="errors" class="border-none max-w-7xl mx-auto">
		<div v-for="error in errors.errors" class="flex">
			<div class="w-24" :class="getCss(error.type)">{{ error.type }}</div>
			<div class="text-muted-color">{{ error.line }}</div>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import DiagnosticsService from "@/services/diagnostics-service";
import Panel from "primevue/panel";
import { ref } from "vue";

const errors = ref(undefined as App.Http.Resources.Diagnostics.ErrorsResource | undefined);

function load() {
	DiagnosticsService.errors().then((response) => {
		errors.value = response.data;
	});
}

function getCss(type: string): string {
	if (type === "error") {
		return "text-danger-700 font-bold";
	}

	if (type === "warning") {
		return "text-orange-500";
	}

	if (type === "info") {
		return "text-primary-400";
	}

	return "";
}

load();
</script>
