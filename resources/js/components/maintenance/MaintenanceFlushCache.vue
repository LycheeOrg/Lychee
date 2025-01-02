<template>
	<Card v-if="data" class="min-h-40 dark:bg-surface-800 shadow shadow-surface-950/30 rounded-lg relative">
		<template #title>
			<div class="text-center">
				{{ "Flush Cache" }}
			</div>
		</template>
		<template #content>
			<ScrollPanel class="w-full h-40 text-muted-color text-sm">
				<div v-if="data.length === 0 && !loading">{{ "Flush the cache of every user to solve invalidation problems." }}</div>
				<ProgressSpinner v-if="loading && data.length === 0" class="w-full"></ProgressSpinner>
				<pre class="text-2xs m-4" v-if="data.length > 0">{{ data.join("\n") }}</pre>
			</ScrollPanel>
			<div class="flex gap-4 mt-1">
				<Button v-if="data.length === 0 && !loading" severity="primary" class="w-full border-none" @click="exec">{{ "Flush" }}</Button>
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

const data = ref<string[]>([]);
const loading = ref(false);
const toast = useToast();

function exec() {
	loading.value = true;
	MaintenanceService.flushDo()
		.then((response) => {
			toast.add({ severity: "success", summary: "Success", life: 3000 });
			loading.value = false;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: "Error", detail: e.response.data.message, life: 3000 });
			loading.value = false;
		});
}
</script>

<style lang="css" scoped>
.lychee-dark .p-card {
	--p-card-background: var(--p-surface-800);
}
</style>
