<template>
	<Card
		v-if="data !== undefined && data > 0"
		class="min-h-40 dark:bg-surface-800 shadow shadow-surface-950/30 rounded-lg relative"
		pt:body:class="min-h-40 h-full"
		pt:content:class="h-full flex justify-between flex-col"
	>
		<template #title>
			<div class="text-center">
				{{ $t("maintenance.fill-filesize-sizevariants.title") }}
			</div>
		</template>
		<template #content>
			<ScrollPanel class="w-full h-40 text-sm text-muted-color">
				<div class="w-full text-center" v-if="!loading" v-html="description"></div>
				<ProgressSpinner v-if="loading" class="w-full"></ProgressSpinner>
			</ScrollPanel>
			<div class="flex gap-4 mt-1">
				<Button v-if="data > 0 && !loading" severity="primary" class="w-full border-none" @click="exec">{{
					$t("maintenance.fill-filesize-sizevariants.button")
				}}</Button>
			</div>
		</template>
	</Card>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import Button from "primevue/button";
import Card from "primevue/card";
import { useToast } from "primevue/usetoast";
import ProgressSpinner from "primevue/progressspinner";
import ScrollPanel from "primevue/scrollpanel";
import MaintenanceService from "@/services/maintenance-service";
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";

const data = ref<number | undefined>(undefined);
const loading = ref(false);
const toast = useToast();

const description = computed(() => {
	return sprintf(trans("maintenance.fill-filesize-sizevariants.description"), data.value);
});

function load() {
	loading.value = true;
	MaintenanceService.missingFileSizesCheck()
		.then((response) => {
			data.value = response.data;
			loading.value = false;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			loading.value = false;
		});
}

function exec() {
	loading.value = true;
	MaintenanceService.missingFileSizesDo()
		.then((response) => {
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
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
