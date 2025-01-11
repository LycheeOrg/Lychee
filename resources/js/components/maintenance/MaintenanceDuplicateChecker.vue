<template>
	<Card
		class="min-h-40 dark:bg-surface-800 shadow shadow-surface-950/30 rounded-lg relative"
		pt:body:class="min-h-40 h-full"
		pt:content:class="h-full flex justify-between flex-col"
	>
		<template #title>
			<div class="text-center">
				{{ $t("maintenance.duplicate-finder.title") }}
			</div>
		</template>
		<template #content>
			<div class="w-full h-40 text-sm text-muted-color">
				<div class="w-full text-left">
					<h2 class="mb-4">{{ $t("maintenance.duplicate-finder.description") }}</h2>
					<p v-if="data !== undefined && data.pure_duplicates + data.duplicates_within_album + data.title_duplicates > 0">
						{{ $t("maintenance.duplicate-finder.duplicates-all") }}: {{ data.pure_duplicates }}<br />
						{{ $t("maintenance.duplicate-finder.duplicates-title") }}: {{ data.title_duplicates }}<br />
						{{ $t("maintenance.duplicate-finder.duplicates-per-album") }}: {{ data.duplicates_within_album }}<br />
					</p>
					<ProgressSpinner v-if="data === undefined" class="w-full"></ProgressSpinner>
				</div>
			</div>
			<div class="flex gap-4 mt-1">
				<Button
					as="router-link"
					to="/duplicatesFinder"
					v-if="data !== undefined && data.pure_duplicates"
					severity="primary"
					class="w-full border-none self-end"
					>{{ $t("maintenance.duplicate-finder.show") }}</Button
				>
			</div>
		</template>
	</Card>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import Button from "primevue/button";
import Card from "primevue/card";
import ProgressSpinner from "primevue/progressspinner";
import MaintenanceService from "@/services/maintenance-service";

const data = ref<App.Http.Resources.Models.Duplicates.DuplicateCount | undefined>(undefined);

function load() {
	MaintenanceService.getDuplicatesCount().then((response) => {
		data.value = response.data;
	});
}

onMounted(load);
</script>

<style lang="css" scoped>
.lychee-dark .p-card {
	--p-card-background: var(--p-surface-800);
}
</style>
