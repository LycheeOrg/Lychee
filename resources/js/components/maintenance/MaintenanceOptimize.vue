<template>
	<Card
		v-if="data"
		class="min-h-40 dark:bg-surface-800 shadow shadow-surface-950/30 rounded-lg relative"
		pt:body:class="min-h-40 h-full"
		pt:content:class="h-full flex justify-between flex-col"
	>
		<template #title>
			<div class="text-center">
				{{ $t("maintenance.optimize.title") }}
			</div>
		</template>
		<template #content>
			<ScrollPanel class="w-full h-40 text-muted-color text-sm">
				<div v-if="data.length === 0 && !loading">{{ $t("maintenance.optimize.description") }}</div>
				<ProgressSpinner v-if="loading && data.length === 0" class="w-full"></ProgressSpinner>
				<pre class="text-2xs m-4" v-if="data.length > 0">{{ data.join("\n") }}</pre>
			</ScrollPanel>
			<div class="flex gap-4 mt-1">
				<Button v-if="data.length === 0 && !loading" severity="primary" class="w-full border-none" @click="exec">{{
					$t("maintenance.optimize.button")
				}}</Button>
			</div>
		</template>
	</Card>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Button from "primevue/button";
import Card from "primevue/card";
import ProgressSpinner from "primevue/progressspinner";
import ScrollPanel from "primevue/scrollpanel";
import MaintenanceService from "@/services/maintenance-service";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";

const data = ref<string[]>([]);
const loading = ref(false);
const toast = useToast();

function exec() {
	loading.value = true;
	MaintenanceService.optimizeDo()
		.then((response) => {
			data.value = response.data;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			loading.value = false;
		});
}
</script>

<style lang="css" scoped>
.lychee-dark .p-card {
	--p-card-background: var(--p-surface-800);
}
</style>
