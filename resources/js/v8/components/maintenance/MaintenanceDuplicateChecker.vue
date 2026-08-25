<template>
	<MaintenanceRow>
		<template #title>{{ $t("maintenance.duplicate-finder.title") }}</template>
		<span v-if="data !== undefined">
			{{ $t("maintenance.duplicate-finder.duplicates-all") }}: {{ data.pure_duplicates }} ·
			{{ $t("maintenance.duplicate-finder.duplicates-title") }}: {{ data.title_duplicates }} ·
			{{ $t("maintenance.duplicate-finder.duplicates-per-album") }}: {{ data.duplicates_within_album }}
		</span>
		<span v-else-if="!isLoaded">{{ $t("maintenance.duplicate-finder.description") }}</span>
		<LycheeLoadingIcon fast v-if="data === undefined && isLoaded" class="inline-block text-2xl" />
		<template #actions>
			<UButton variant="soft" v-if="data !== undefined && data.pure_duplicates" to="/duplicatesFinder" color="primary">
				{{ $t("maintenance.duplicate-finder.show") }}
			</UButton>
			<UButton v-if="!isLoaded" color="primary" variant="soft" @click="load">
				{{ $t("maintenance.duplicate-finder.load") }}
			</UButton>
		</template>
	</MaintenanceRow>
</template>

<script setup lang="ts">
import { ref } from "vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import MaintenanceRow from "@/v8/components/maintenance/MaintenanceRow.vue";
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
