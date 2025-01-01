<template>
	<Card
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
				<div class="w-full text-left" v-if="!loading">
					<h2 class="mb-4">{{ $t("This module counts potential duplicates betwen pictures.") }}</h2>
					<p v-if="data !== undefined && data.pure_duplicates + data.duplicates_within_album + data.title_duplicates > 0">
						{{ $t("Duplicates over all albums") }}: {{ data.pure_duplicates }}<br />
						{{ $t("Title duplicates per albums") }}: {{ data.title_duplicates }}<br />
						{{ $t("Duplicates per albums") }}: {{ data.duplicates_within_album }}<br />
					</p>
				</div>
				<ProgressSpinner v-if="loading" class="w-full"></ProgressSpinner>
			</ScrollPanel>
			<div class="flex gap-4 mt-1">
				<Button
					as="router-link"
					to="/duplicatesFinder"
					v-if="data !== undefined && data.pure_duplicates"
					severity="primary"
					class="w-full border-none self-end"
					>{{ $t("Display duplicates") }}</Button
				>
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
// import { sprintf } from "sprintf-js";
// import { trans } from "laravel-vue-i18n";

const data = ref<App.Http.Resources.Models.Duplicates.DuplicateCount | undefined>(undefined);
const loading = ref(false);

const title = ref("Duplicates");

function load() {
	MaintenanceService.getDuplicatesCount().then((response) => {
		console.log(response.data);
		data.value = response.data;
	});
}

load();
</script>

<style lang="css" scoped>
.lychee-dark .p-card {
	--p-card-background: var(--p-surface-800);
}
</style>
