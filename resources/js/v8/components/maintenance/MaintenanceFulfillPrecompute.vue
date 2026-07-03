<template>
	<UCard v-if="data !== undefined && data !== 0" class="min-h-40 relative" :ui="{ body: 'h-full flex flex-col justify-between gap-4' }">
		<template #header>
			<div class="text-center">
				{{ $t("maintenance.fulfill-precompute.title") }}
			</div>
		</template>
		<div class="w-full h-40 overflow-y-auto text-sm text-muted">
			<div v-if="!loading" class="w-full ltr:text-left rtl:text-right" v-html="description"></div>
			<Spinner v-if="loading" class="w-full" />
		</div>
		<div class="flex gap-4 mt-1">
			<UButton v-if="data !== 0 && !loading" color="primary" class="w-full justify-center" @click="exec">
				{{ $t("maintenance.fulfill-precompute.button") }}
			</UButton>
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

const data = ref<number | undefined>(undefined);
const loading = ref(false);
const toast = useAppToast();

const description = computed(() => {
	if (data.value === -1) {
		return "";
	}
	return sprintf(trans("maintenance.fulfill-precompute.description"), data.value);
});

function load() {
	loading.value = true;
	MaintenanceService.fulfillPrecomputeCheck()
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
	MaintenanceService.fulfillPrecomputeDo()
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
