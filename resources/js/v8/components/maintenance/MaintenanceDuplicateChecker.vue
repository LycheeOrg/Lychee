<template>
	<UCard class="min-h-40 relative bg-muted/50">
		<template #header>
			<div class="text-center font-bold">
				{{ $t("maintenance.duplicate-finder.title") }}
			</div>
		</template>
		<div class="w-full h-40 text-sm text-muted">
			<div class="w-full ltr:text-left rtl:text-right">
				<h2 class="mb-4">{{ $t("maintenance.duplicate-finder.description") }}</h2>
				<p v-if="data !== undefined && data.pure_duplicates + data.duplicates_within_album + data.title_duplicates > 0">
					{{ $t("maintenance.duplicate-finder.duplicates-all") }}: {{ data.pure_duplicates }}<br />
					{{ $t("maintenance.duplicate-finder.duplicates-title") }}: {{ data.title_duplicates }}<br />
					{{ $t("maintenance.duplicate-finder.duplicates-per-album") }}: {{ data.duplicates_within_album }}<br />
				</p>
				<Spinner v-if="data === undefined && isLoaded" class="w-full" />
			</div>
		</div>
		<template #footer>
			<UButton v-if="data !== undefined && data.pure_duplicates" to="/duplicatesFinder" color="primary" class="w-full justify-center self-end">
				{{ $t("maintenance.duplicate-finder.show") }}
			</UButton>
			<UButton v-if="!isLoaded" color="primary" class="w-full justify-center self-end" @click="load">
				{{ $t("maintenance.duplicate-finder.load") }}
			</UButton>
		</template>
	</UCard>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Spinner from "@/v8/components/Spinner.vue";
import MaintenanceService from "@/services/maintenance-service";

const data = ref<App.Http.Resources.Models.Duplicates.DuplicateCount | undefined>(undefined);
const isLoaded = ref(false);

function load() {
	isLoaded.value = true;
	MaintenanceService.getDuplicatesCount().then((response) => {
		data.value = response.data;
	});
}
</script>
