<template>
	<UCard :ui="{ root: 'max-w-7xl mx-auto' }" dir="ltr">
		<template #header>
			<span class="font-bold">{{ $t("diagnostics.self-diagnosis") }}</span>
		</template>
		<div v-if="!errors" class="text-sky-400 font-bold">{{ $t("diagnostics.loading") }}</div>
		<div v-for="(error, idx) in errors" v-else class="flex flex-col" :key="`error-${idx}`">
			<div class="w-full flex">
				<div class="w-24 flex-none capitalize" :class="getCss(error.type)">{{ error.type }}</div>
				<div class="text-muted">{{ error.message }}</div>
			</div>
			<div v-for="(details, idxx) in error.details" class="flex" :key="`error-${idx}-${idxx}`">
				<div class="w-24 flex-none"></div>
				<div class="text-muted italic text-xs">{{ details }}</div>
			</div>
		</div>
	</UCard>
</template>
<script setup lang="ts">
import { ref } from "vue";
import DiagnosticsService from "@/services/diagnostics-service";

const errors = ref<App.Http.Resources.Diagnostics.ErrorLine[] | undefined>(undefined);

const emits = defineEmits<{
	loaded: [data: string[]];
}>();

function load() {
	DiagnosticsService.errors().then((response) => {
		errors.value = response.data.errors;
		emits("loaded", toArray(response.data.errors));
	});
}

function getCss(type: string): string {
	if (type === "error") {
		return "text-error font-bold";
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
