<template>
	<MaintenanceRow v-if="data !== undefined && data > 0">
		<template #title>{{ $t("maintenance.missing-palettes.title") }}</template>
		<span v-if="!loading" v-html="description"></span>
		<LycheeLoadingIcon fast v-if="loading" class="inline-block text-2xl" />
		<template #actions>
			<UButton variant="soft" v-if="data > 0 && !loading" color="primary" @click="exec">
				{{ $t("maintenance.missing-palettes.button") }}
			</UButton>
		</template>
	</MaintenanceRow>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import MaintenanceRow from "@/v8/components/maintenance/MaintenanceRow.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import MaintenanceService from "@/services/maintenance-service";
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";

const data = ref<number | undefined>(undefined);
const loading = ref(false);
const toast = useAppToast();

const description = computed(() => {
	return sprintf(trans("maintenance.missing-palettes.description"), data.value);
});

function load() {
	loading.value = true;
	MaintenanceService.missingPalettesCheck()
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
	MaintenanceService.missingPalettesDo()
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
			loading.value = false;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			loading.value = false;
		})
		.finally(load);
}

load();
</script>
