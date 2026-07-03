<template>
	<UCard v-if="data !== undefined && data.is_not_empty" class="min-h-40 relative" :ui="{ body: 'h-full flex flex-col justify-between gap-4' }">
		<template #header>
			<div class="text-center">
				{{ title }}
			</div>
		</template>
		<div class="w-full h-40 overflow-y-auto text-sm text-muted">
			<div class="w-full text-center" v-html="description"></div>
			<Spinner v-if="loading" class="w-full" />
		</div>
		<div class="flex gap-4 mt-1">
			<UButton v-if="data.is_not_empty && !loading" color="error" class="w-full font-bold justify-center" @click="exec">{{
				$t("maintenance.cleaning.button")
			}}</UButton>
		</div>
	</UCard>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import Spinner from "@/v8/components/Spinner.vue";
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
		});
}

load();
</script>
