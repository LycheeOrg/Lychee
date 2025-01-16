<template>
	<Panel :header="$t('diagnostics.self-diagnosis')" class="border-none max-w-7xl mx-auto">
		<div v-if="!errors" class="text-sky-400 font-bold">{{ $t("diagnostics.loading") }}</div>
		<div v-else v-for="error in errors" class="flex flex-col">
			<div class="w-full flex">
				<div class="w-24 flex-none capitalize" :class="getCss(error.type)">{{ error.type }}</div>
				<div class="text-muted-color">{{ error.message }}</div>
			</div>
			<div v-for="details in error.details" class="flex">
				<div class="w-24 flex-none"></div>
				<div class="text-muted-color italic text-xs">{{ details }}</div>
			</div>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Panel from "primevue/panel";
import DiagnosticsService from "@/services/diagnostics-service";

const errors = ref<App.Http.Resources.Diagnostics.ErrorLine[] | undefined>(undefined);

const emits = defineEmits<{
	loaded: [data: string[]];
}>();

function load() {
	DiagnosticsService.errors().then((response) => {
		errors.value = response.data;
		emits("loaded", toArray(response.data));
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

function toArray(data: App.Http.Resources.Diagnostics.ErrorLine[]): string[] {
	const result: string[] = [];

	data.forEach((error) => {
		let prefix = "";
		if (error.type === "error") {
			prefix = "Error:   ";
		} else if (error.type === "warning") {
			prefix = "Warning: ";
		} else if (error.type === "info") {
			prefix = "Info:    ";
		}
		result.push(prefix + error.message);
		error.details.forEach((detail) => {
			result.push("         " + detail);
		});
	});

	return result;
}

load();
</script>
