<template>
	<Card
		v-if="data !== undefined && data > 0"
		class="min-h-40 dark:bg-surface-800 shadow shadow-surface-950/30 rounded-lg relative"
		pt:body:class="min-h-40 h-full"
		pt:content:class="h-full flex justify-between flex-col"
	>
		<template #title>
			<div class="text-center">
				{{ title }}
			</div>
		</template>
		<template #content>
			<ScrollPanel class="w-full h-40 text-sm text-muted-color">
				<div class="w-full text-center" v-html="description"></div>
				<ProgressSpinner v-if="loading" class="w-full"></ProgressSpinner>
			</ScrollPanel>
			<div class="flex gap-4 mt-1">
				<Button v-if="data > 0 && !loading" severity="primary" class="w-full border-none" @click="exec">
					{{ $t("maintenance.gen-sizevariants.button") }}
				</Button>
			</div>
		</template>
	</Card>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import Button from "primevue/button";
import Card from "primevue/card";
import { useToast } from "primevue/usetoast";
import ScrollPanel from "primevue/scrollpanel";
import ProgressSpinner from "primevue/progressspinner";
import MaintenanceService from "@/services/maintenance-service";
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	sv: App.Enum.SizeVariantType;
}>();

const data = ref<number | undefined>(undefined);
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
		case 8:
			return "placeholder";
		case 7:
			return "thumb";
		case 6:
			return "thumb2x";
		case 5:
			return "small";
		case 4:
			return "small2x";
		case 3:
			return "medium";
		case 2:
			return "medium2x";
		case 1:
			return "original";
		case 0:
			return "raw";
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
	MaintenanceService.genSizeVariantsDo(props.sv)
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
			load();
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
