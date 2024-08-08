<template>
	<Card v-if="data !== undefined && data > 0" class="min-h-40 shadow shadow-surface-950 rounded-lg relative bg-surface-800">
		<template #title>
			<div class="text-center">
				{{ title }}
			</div>
		</template>
		<template #content>
			<ScrollPanel class="w-full h-56 text-sm text-muted-color">
				<div v-html="description" class="w-full text-center"></div>
				<ProgressSpinner v-if="loading" class="w-full"></ProgressSpinner>
			</ScrollPanel>
			<div class="flex gap-4 mt-1">
				<Button v-if="data > 0 && !loading" severity="primary" class="w-full" @click="exec">{{
					$t("maintenance.gen-sizevariants.button")
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

const props = defineProps<{
	sv: App.Enum.SizeVariantType;
}>();

const data = ref(undefined as number | undefined);
const loading = ref(false);
const toast = useToast();

const title = computed(() => {
	return sprintf(trans("maintenance.gen-sizevariants.title"), getName(props.sv));
});
const description = computed(() => {
	return sprintf(trans("maintenance.gen-sizevariants.description"), data.value, getName(props.sv));
});

function getName(sv: App.Enum.SizeVariantType): string {
	switch (sv) {
		case 6:
			return "thumb";
		case 5:
			return "thumb2x";
		case 4:
			return "small";
		case 3:
			return "small2x";
		case 2:
			return "medium";
		case 1:
			return "medium2x";
		case 0:
			return "original";
	}
}

function load() {
	MaintenanceService.genSizeVariantsCheck(props.sv).then((response) => {
		data.value = response.data;
		loading.value = false;
	});
}

function exec() {
	loading.value = true;
	MaintenanceService.genSizeVariantsDo(props.sv).then((response) => {
		toast.add({ severity: "success", summary: "Success" });
		load();
	});
}

load();
</script>
