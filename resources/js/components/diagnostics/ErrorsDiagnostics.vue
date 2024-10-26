<template>
	<Panel header="Errors" class="border-none max-w-7xl mx-auto">
		<div v-if="!errors" class="text-sky-400 font-bold">Loading...</div>
		<div v-else v-for="error in errors" class="flex">
			<div class="w-24 capitalize" :class="getCss(error.type)">{{ error.type }}</div>
			<div class="text-muted-color">{{ error.line }}</div>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Panel from "primevue/panel";
import DiagnosticsService from "@/services/diagnostics-service";

const errors = ref(undefined as App.Http.Resources.Diagnostics.ErrorLine[] | undefined);

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
