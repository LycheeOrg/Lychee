<template>
	<UCard v-if="data !== undefined && data !== 0" class="min-h-40 relative bg-muted/50">
		<template #header>
			<div class="text-center font-bold">
				{{ $t("maintenance.sync-face-embeddings.title") }}
			</div>
		</template>
		<div class="w-full h-40 overflow-y-auto text-sm text-muted">
			<div v-if="!loading" class="w-full ltr:text-left rtl:text-right" v-html="description"></div>
			<Spinner v-if="loading" class="w-full" />
		</div>
		<template #footer>
			<UButton v-if="data !== 0 && !loading" color="warning" class="w-full justify-center" @click="exec">
				{{ $t("maintenance.sync-face-embeddings.action") }}
			</UButton>
		</template>
	</UCard>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import Spinner from "@/v8/components/Spinner.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import MaintenanceService from "@/services/maintenance-service";

const data = ref<number | undefined>(undefined);
const loading = ref(false);
const toast = useAppToast();

const description = computed(() => {
	if (data.value === 0) {
		return "";
	}
	return sprintf(trans("maintenance.sync-face-embeddings.description"), data.value);
});

function load() {
	loading.value = true;
	MaintenanceService.syncFaceEmbeddingsCheck().then((response) => {
		data.value = response.data;
		loading.value = false;
	});
}

function exec() {
	loading.value = true;
	MaintenanceService.syncFaceEmbeddingsDo()
		.then(() => {
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				detail: trans("maintenance.sync-face-embeddings.success"),
				life: 3000,
			});
			loading.value = false;
		})
		.catch((e) => {
			if (e.response.status !== 501 && e.response.data.message !== "Feature 'v8' is disabled") {
				toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
			}
			loading.value = false;
		})
		.finally(load);
}

load();
</script>
