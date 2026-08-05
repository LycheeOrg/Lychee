<template>
	<MaintenanceRow v-if="data !== undefined && data.is_not_empty">
		<template #title>{{ title }}</template>
		<span v-html="description"></span>
		<LycheeLoadingIcon fast v-if="loading" class="inline-block text-2xl" />
		<template #actions>
			<UButton v-if="data.is_not_empty && !loading" variant="soft" color="error" @click="exec">{{ $t("maintenance.cleaning.button") }}</UButton>
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

const props = defineProps<{ path: string }>();

const data = ref<App.Http.Resources.Diagnostics.CleaningState | undefined>(undefined);
const loading = ref(false);
const toast = useAppToast();

const title = computed(() => {
	return sprintf(trans("maintenance.cleaning.title"), data.value?.path);
});
const description = computed(() => {
	return sprintf(trans("maintenance.cleaning.description"), data.value?.base);
});

function load() {
	loading.value = true;
	MaintenanceService.cleaningGet(props.path)
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
	MaintenanceService.cleaningDo(props.path)
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
